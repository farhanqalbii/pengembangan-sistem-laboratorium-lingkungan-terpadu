<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleXlsxService
{
    /**
     * Membuat dan mengalirkan (stream download) file Microsoft Excel .xlsx asli.
     *
     * @param  string  $filename  Nama file tanpa ekstensi
     * @param  array<int, string>  $headers  Baris judul kolom
     * @param  iterable<array<int, mixed>>  $rows  Data baris
     */
    public static function download(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename).'_'.date('Ymd_His').'.xlsx';

        $sheetXml = self::buildSheetXml($headers, $rows);
        $workbookXml = self::buildWorkbookXml();
        $workbookRelsXml = self::buildWorkbookRelsXml();
        $contentTypesXml = self::buildContentTypesXml();
        $relsXml = self::buildRelsXml();
        $stylesXml = self::buildStylesXml();

        $files = [
            '[Content_Types].xml' => $contentTypesXml,
            '_rels/.rels' => $relsXml,
            'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
            'xl/workbook.xml' => $workbookXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        $zipData = self::createZipData($files);

        return response()->stream(function () use ($zipData) {
            echo $zipData;
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$safeName}\"",
            'Content-Length' => strlen($zipData),
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Menghasilkan binary file XLSX murni tanpa header HTTP response (berguna untuk pengujian atau penyimpanan ke disk).
     *
     * @param  array<int, string>  $headers
     * @param  iterable<array<int, mixed>>  $rows
     */
    public static function generateRawContent(array $headers, iterable $rows): string
    {
        $sheetXml = self::buildSheetXml($headers, $rows);
        $workbookXml = self::buildWorkbookXml();
        $workbookRelsXml = self::buildWorkbookRelsXml();
        $contentTypesXml = self::buildContentTypesXml();
        $relsXml = self::buildRelsXml();
        $stylesXml = self::buildStylesXml();

        $files = [
            '[Content_Types].xml' => $contentTypesXml,
            '_rels/.rels' => $relsXml,
            'xl/_rels/workbook.xml.rels' => $workbookRelsXml,
            'xl/workbook.xml' => $workbookXml,
            'xl/styles.xml' => $stylesXml,
            'xl/worksheets/sheet1.xml' => $sheetXml,
        ];

        return self::packZipManually($files);
    }

    /**
     * Membuat binary ZIP dari array file name => content menggunakan ZipArchive jika ada, atau pure PHP fallback.
     */
    protected static function createZipData(array $files): string
    {
        if (class_exists(\ZipArchive::class)) {
            $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
            $zip = new \ZipArchive;
            if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($files as $name => $content) {
                    $zip->addFromString($name, $content);
                }
                $zip->close();
                $data = file_get_contents($tempFile);
                @unlink($tempFile);

                return $data ?: '';
            }
        }

        // Pure PHP ZIP builder fallback (RFC 1951 Deflate)
        return self::packZipManually($files);
    }

    protected static function packZipManually(array $files): string
    {
        $datasec = '';
        $dir = '';
        $offset = 0;

        foreach ($files as $name => $data) {
            $hexdtime = pack('V', self::dosTime());

            $unc_len = strlen($data);
            $crc = crc32($data);
            $zdata = gzdeflate($data);
            $c_len = strlen($zdata);

            $fr = "\x50\x4b\x03\x04";
            $fr .= "\x14\x00"; // version needed to extract
            $fr .= "\x00\x00"; // general purpose bit flag
            $fr .= "\x08\x00"; // compression method (deflate)
            $fr .= $hexdtime;
            $fr .= pack('V', $crc);
            $fr .= pack('V', $c_len);
            $fr .= pack('V', $unc_len);
            $fr .= pack('v', strlen($name));
            $fr .= pack('v', 0); // extra field length
            $fr .= $name;
            $fr .= $zdata;

            $datasec .= $fr;

            $cdrec = "\x50\x4b\x01\x02";
            $cdrec .= "\x00\x00"; // version made by
            $cdrec .= "\x14\x00"; // version needed to extract
            $cdrec .= "\x00\x00"; // general purpose bit flag
            $cdrec .= "\x08\x00"; // compression method
            $cdrec .= $hexdtime;
            $cdrec .= pack('V', $crc);
            $cdrec .= pack('V', $c_len);
            $cdrec .= pack('V', $unc_len);
            $cdrec .= pack('v', strlen($name));
            $cdrec .= pack('v', 0); // extra field length
            $cdrec .= pack('v', 0); // file comment length
            $cdrec .= pack('v', 0); // disk number start
            $cdrec .= pack('v', 0); // internal file attributes
            $cdrec .= pack('V', 32); // external file attributes - 'archive' bit set
            $cdrec .= pack('V', $offset); // relative offset of local header
            $cdrec .= $name;

            $dir .= $cdrec;
            $offset = strlen($datasec);
        }

        $dircount = count($files);

        return $datasec.$dir.
            "\x50\x4b\x05\x06\x00\x00\x00\x00".
            pack('v', $dircount).
            pack('v', $dircount).
            pack('V', strlen($dir)).
            pack('V', strlen($datasec)).
            "\x00\x00";
    }

    protected static function dosTime(): int
    {
        $timearray = getdate();

        return (($timearray['year'] - 1980) << 25)
            | ($timearray['mon'] << 21)
            | ($timearray['mday'] << 16)
            | ($timearray['hours'] << 11)
            | ($timearray['minutes'] << 5)
            | ($timearray['seconds'] >> 1);
    }

    protected static function escapeXml(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected static function buildSheetXml(array $headers, iterable $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'."\n";
        $xml .= '<sheetData>'."\n";

        // Row 1: Headers
        $rowIndex = 1;
        $xml .= "<row r=\"{$rowIndex}\">\n";
        $colIndex = 0;
        foreach ($headers as $h) {
            $colLetter = self::colLetter($colIndex++);
            $val = self::escapeXml((string) $h);
            $xml .= "<c r=\"{$colLetter}{$rowIndex}\" t=\"inlineStr\" s=\"1\"><is><t>{$val}</t></is></c>\n";
        }
        $xml .= "</row>\n";

        // Rows 2..N: Data
        foreach ($rows as $row) {
            $rowIndex++;
            $xml .= "<row r=\"{$rowIndex}\">\n";
            $colIndex = 0;
            $rowArr = is_array($row) ? $row : (array) $row;
            foreach ($rowArr as $cell) {
                $colLetter = self::colLetter($colIndex++);
                if (is_numeric($cell) && ! str_starts_with((string) $cell, '0')) {
                    $xml .= "<c r=\"{$colLetter}{$rowIndex}\"><v>{$cell}</v></c>\n";
                } else {
                    $val = self::escapeXml((string) ($cell ?? ''));
                    $xml .= "<c r=\"{$colLetter}{$rowIndex}\" t=\"inlineStr\"><is><t>{$val}</t></is></c>\n";
                }
            }
            $xml .= "</row>\n";
        }

        $xml .= '</sheetData>'."\n";
        $xml .= '</worksheet>';

        return $xml;
    }

    protected static function colLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(($index % 26) + 65).$letter;
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    protected static function buildWorkbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'.
            '<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets>'.
            '</workbook>';
    }

    protected static function buildWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'.
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'.
            '</Relationships>';
    }

    protected static function buildContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
            '<Default Extension="xml" ContentType="application/xml"/>'.
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'.
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'.
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.
            '</Types>';
    }

    protected static function buildRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'.
            '</Relationships>';
    }

    protected static function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'.
            '<fonts count="2">'.
            '<font><name val="Calibri"/><sz val="11"/></font>'.
            '<font><b/><name val="Calibri"/><sz val="11"/></font>'.
            '</fonts>'.
            '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'.
            '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'.
            '<cellXfs count="2">'.
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'.
            '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1"/>'.
            '</cellXfs>'.
            '</styleSheet>';
    }
}
