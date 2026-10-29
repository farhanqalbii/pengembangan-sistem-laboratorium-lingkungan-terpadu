<?php

namespace Tests\Feature;

use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StaffBorrowingsUiConsistencyTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staff;

    protected User $customer;

    protected Peralatan $equipment;

    protected Peminjaman $borrowing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'name' => 'Staff Analis UI',
            'email' => 'staff.ui.'.rand(1000, 9999).'@test.com',
            'role' => 'staff',
        ]);

        $this->customer = User::factory()->create([
            'name' => 'Customer UI Test',
            'email' => 'customer.ui.'.rand(1000, 9999).'@test.com',
            'role' => 'customer',
            'company_name' => 'PT Uji Coba Visual',
        ]);

        $this->equipment = Peralatan::create([
            'kode' => 'EQ-UI-'.rand(1000, 9999),
            'nama' => 'Digital pH Meter Waterproof UI',
            'kategori' => 'Elektrometri',
            'merek' => 'Hanna',
            'spesifikasi' => 'HI-98107',
            'tahun_pengadaan' => 2024,
            'jumlah' => 3,
            'lokasi_penyimpanan' => 'Lab Fisika',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 50000,
            'biaya_deposit' => 100000,
        ]);

        $this->borrowing = Peminjaman::create([
            'kode_peminjaman' => 'BRW-UI-'.rand(100000, 999999),
            'customer_id' => $this->customer->id,
            'status' => 'Dipinjam',
            'tanggal_mulai' => now(),
            'tanggal_selesai' => now()->addDays(5),
            'biaya_deposit' => 100000,
            'tujuan' => 'Pengujian air sungai visual',
        ]);

        DetailPeminjaman::create([
            'id_peminjaman' => $this->borrowing->id,
            'id_peralatan' => $this->equipment->id,
            'jumlah' => 1,
            'kondisi_sebelum' => 'Baik',
        ]);
    }

    public function test_staff_borrowings_index_does_not_have_rogue_background_on_code_cell(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.borrowings.index'));
        $response->assertStatus(200);

        $content = $response->getContent();

        // 1. Pastikan tidak ada class bg-blue-50/40 atau latar belakang abu-abu terang pada elemen <td>
        $this->assertStringNotContainsString('bg-blue-50/40', $content, 'Class bg-blue-50/40 must be removed from table cells.');

        // 2. Pastikan kode pinjam berupa tautan yang dapat diklik ke route staff.borrowings.show
        $expectedUrl = route('staff.borrowings.show', $this->borrowing);
        $this->assertStringContainsString($expectedUrl, $content, 'Borrowing code must be an anchor link to detail page.');
        $this->assertStringContainsString($this->borrowing->borrow_code, $content);

        // 3. Pastikan selector sel td tidak memiliki hardcoded bg yang menimpa row hover
        $this->assertMatchesRegularExpression(
            '/<td[^>]*whitespace-nowrap[^>]*>\s*<a[^>]*href=\"'.preg_quote($expectedUrl, '/').'\"/s',
            $content,
            'Table cell must contain the clickable link directly within clean td'
        );

        // 4. Pastikan fitur pencarian & filter tetap tersedia
        $response->assertSee('name="search"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('Inspeksi & Pengembalian');

        // 5. Pastikan tampilan mobile card view juga memiliki link kode pinjam
        $this->assertStringContainsString($this->borrowing->borrow_code, $content);
    }
}
