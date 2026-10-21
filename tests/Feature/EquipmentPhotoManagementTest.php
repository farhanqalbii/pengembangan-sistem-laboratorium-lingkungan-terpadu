<?php

namespace Tests\Feature;

use App\Models\Peralatan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EquipmentPhotoManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->customer = User::factory()->create(['role' => 'customer']);

        Storage::fake('public');
    }

    /**
     * 1. Tambah alat tanpa foto — penyimpanan harus berhasil.
     */
    public function test_store_equipment_without_photo_succeeds(): void
    {
        $code = 'EQP-TST-'.rand(100, 999);
        $payload = [
            'code' => $code,
            'name' => 'Neraca Analitik Digital',
            'category' => 'Gravimetri',
            'brand' => 'Ohaus',
            'specification' => 'Presisi 0.0001g',
            'purchase_year' => 2024,
            'quantity' => 5,
            'location' => 'Lab Kimia Dasar',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 75000,
            'deposit_amount' => 300000,
        ];

        $response = $this->actingAs($this->staff)->post(route('staff.equipment.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('peralatan', [
            'kode' => $code,
            'nama' => 'Neraca Analitik Digital',
            'foto' => null,
        ]);

        $equipment = Peralatan::where('kode', $code)->first();
        $this->assertNotNull($equipment);
        $this->assertNull($equipment->foto);
        $this->assertNull($equipment->photo_url);
        $this->assertFalse($equipment->hasPhoto());
    }

    /**
     * 2. Tambah alat dengan foto valid — foto harus tersimpan di storage dan path tercatat di database.
     */
    public function test_store_equipment_with_valid_photo_succeeds(): void
    {
        $code = 'EQP-PHO-'.rand(100, 999);
        $fakeImage = UploadedFile::fake()->image('neraca.jpg', 600, 600);

        $payload = [
            'code' => $code,
            'name' => 'Centrifuge High Speed',
            'category' => 'Separasi',
            'brand' => 'Eppendorf',
            'specification' => 'Max 14000 RPM',
            'purchase_year' => 2023,
            'quantity' => 2,
            'location' => 'Lab Biokimia',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 120000,
            'deposit_amount' => 500000,
            'photo' => $fakeImage,
        ];

        $response = $this->actingAs($this->staff)->post(route('staff.equipment.store'), $payload);

        $response->assertRedirect();

        $equipment = Peralatan::where('kode', $code)->first();
        $this->assertNotNull($equipment);
        $this->assertNotNull($equipment->foto);
        $this->assertTrue($equipment->hasPhoto());

        // Verifikasi file tersimpan di storage public
        Storage::disk('public')->assertExists($equipment->foto);
        $this->assertStringContainsString('equipment/', $equipment->foto);
        $this->assertStringContainsString('/storage/'.$equipment->foto, $equipment->photo_url);
    }

    /**
     * 3. Upload file non-gambar — harus ditolak oleh validasi server.
     */
    public function test_store_equipment_rejects_non_image_file(): void
    {
        $code = 'EQP-PDF-'.rand(100, 999);
        $pdfFile = UploadedFile::fake()->create('manual_book.pdf', 500, 'application/pdf');

        $payload = [
            'code' => $code,
            'name' => 'Mikroskop Binokuler',
            'category' => 'Optik',
            'quantity' => 4,
            'location' => 'Lab Biologi',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 60000,
            'deposit_amount' => 250000,
            'photo' => $pdfFile,
        ];

        $response = $this->actingAs($this->staff)->post(route('staff.equipment.store'), $payload);

        $response->assertSessionHasErrors('photo');
        $this->assertDatabaseMissing('peralatan', ['kode' => $code]);
    }

    /**
     * 4. Upload file melebihi batas ukuran (misal > 2 MB) — harus ditolak.
     */
    public function test_store_equipment_rejects_file_exceeding_max_size(): void
    {
        $code = 'EQP-BIG-'.rand(100, 999);
        // 3000 KB > 2048 KB limit
        $hugeImage = UploadedFile::fake()->create('huge_photo.jpg', 3000, 'image/jpeg');

        $payload = [
            'code' => $code,
            'name' => 'Autoclave Vertical',
            'category' => 'Sterilisasi',
            'quantity' => 1,
            'location' => 'Lab Mikrobiologi',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 150000,
            'deposit_amount' => 600000,
            'photo' => $hugeImage,
        ];

        $response = $this->actingAs($this->staff)->post(route('staff.equipment.store'), $payload);

        $response->assertSessionHasErrors('photo');
        $this->assertDatabaseMissing('peralatan', ['kode' => $code]);
    }

    /**
     * 5. Edit alat tanpa memilih foto baru — foto lama harus tetap ada.
     */
    public function test_update_equipment_without_new_photo_retains_old_photo(): void
    {
        $oldPath = 'equipment/existing_photo_'.rand(1000, 9999).'.png';
        Storage::disk('public')->put($oldPath, 'dummy image content');

        $equipment = Peralatan::create([
            'kode' => 'EQP-UP1-'.rand(100, 999),
            'nama' => 'Vortex Mixer Lab',
            'kategori' => 'Preparasi',
            'jumlah' => 3,
            'lokasi' => 'Lab Kimia',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 40000,
            'biaya_deposit' => 150000,
            'foto' => $oldPath,
        ]);

        $updatePayload = [
            'name' => 'Vortex Mixer Lab Generasi 2',
            'category' => 'Preparasi',
            'quantity' => 4,
            'location' => 'Lab Kimia Barat',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 45000,
            'deposit_amount' => 150000,
            // Tidak mengirim input 'photo'
        ];

        $response = $this->actingAs($this->staff)->put(route('staff.equipment.update', $equipment->id), $updatePayload);

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertEquals('Vortex Mixer Lab Generasi 2', $equipment->nama);
        $this->assertEquals($oldPath, $equipment->foto, 'Foto lama harus tetap dipertahankan');
        Storage::disk('public')->assertExists($oldPath);
    }

    /**
     * 6. Edit alat dengan foto baru — foto harus terganti dan foto lama dibersihkan.
     */
    public function test_update_equipment_with_new_photo_replaces_old_photo(): void
    {
        $oldPath = 'equipment/old_image_'.rand(1000, 9999).'.jpg';
        Storage::disk('public')->put($oldPath, 'old photo binary');

        $equipment = Peralatan::create([
            'kode' => 'EQP-UP2-'.rand(100, 999),
            'nama' => 'Hotplate Magnetic Stirrer',
            'kategori' => 'Preparasi',
            'jumlah' => 2,
            'lokasi' => 'Lab Fisika',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 50000,
            'biaya_deposit' => 200000,
            'foto' => $oldPath,
        ]);

        $newImage = UploadedFile::fake()->image('new_hotplate.png', 500, 500);

        $updatePayload = [
            'name' => 'Hotplate Magnetic Stirrer Digital',
            'category' => 'Preparasi',
            'quantity' => 2,
            'location' => 'Lab Fisika',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 55000,
            'deposit_amount' => 200000,
            'photo' => $newImage,
        ];

        $response = $this->actingAs($this->staff)->put(route('staff.equipment.update', $equipment->id), $updatePayload);

        $response->assertRedirect();
        $equipment->refresh();

        $this->assertNotEquals($oldPath, $equipment->foto, 'Foto harus berubah ke path baru');
        Storage::disk('public')->assertExists($equipment->foto);
        Storage::disk('public')->assertMissing($oldPath);
    }

    /**
     * 7. Lima alat yang dipetakan menampilkan gambar bawaan yang benar.
     */
    public function test_five_mapped_equipment_display_correct_default_photos(): void
    {
        $mappings = [
            'EQP-AAS-003' => 'images/equipment/aas-shimadzu-AA-7000.png',
            'EQP-PHM-002' => 'images/equipment/digital-portable-pH-meter-waterproof-hanna.png',
            'EQP-DO-002' => 'images/equipment/dissolved-oxygen-meter-hach.png',
            'EQP-INC-004' => 'images/equipment/incubator-BOD-refrigerated-memmert-ICP-260.png',
            'EQP-SPE-001' => 'images/equipment/spektrofotometer-UV-Vis-high-precision.png',
        ];

        foreach ($mappings as $code => $expectedPath) {
            $equipment = Peralatan::where('kode', $code)->first();
            $this->assertNotNull($equipment, "Equipment with code {$code} must exist");

            // Pastikan photo_url meresolve ke path yang dipetakan
            $this->assertStringContainsString($expectedPath, $equipment->photo_url);
            $this->assertTrue($equipment->hasPhoto());
        }
    }

    /**
     * 8. Alat tanpa foto upload dan bukan salah satu dari 5 kode bawaan menampilkan placeholder.
     */
    public function test_equipment_without_photo_displays_placeholder_in_index(): void
    {
        $equipment = Peralatan::create([
            'kode' => 'EQP-NOPHO-'.rand(100, 999),
            'nama' => 'Labware Statif & Klem',
            'kategori' => 'Glassware',
            'jumlah' => 10,
            'lokasi' => 'Gudang Lab',
            'stok_minimum' => 2,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 15000,
            'biaya_deposit' => 50000,
            'foto' => null,
        ]);

        $this->assertNull($equipment->photo_url);
        $this->assertFalse($equipment->hasPhoto());

        $response = $this->actingAs($this->staff)->get(route('staff.equipment.index', ['search' => $equipment->kode]));

        $response->assertStatus(200);
        $response->assertSee('Instrumen Lab');
        $response->assertSee('fa-microscope');
    }

    /**
     * 9. Filter, pencarian, dan tautan aksi pada kartu inventori tetap berfungsi.
     */
    public function test_inventory_index_filter_search_and_actions(): void
    {
        // 1. Pencarian
        $searchResponse = $this->actingAs($this->staff)->get(route('staff.equipment.index', ['search' => 'pH Meter']));
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('EQP-PHM-002');

        // 2. Filter Kategori
        $filterResponse = $this->actingAs($this->staff)->get(route('staff.equipment.index', ['category' => 'Spektrofotometri']));
        $filterResponse->assertStatus(200);

        // 3. Tombol Edit & Detail tertera di kartu
        $indexResponse = $this->actingAs($this->staff)->get(route('staff.equipment.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Edit');
        $indexResponse->assertSee('Detail');
        $indexResponse->assertSee('QR / Barcode');
    }

    /**
     * 10. Pengguna tidak berwenang (customer / guest) tidak boleh mengunggah atau mengubah foto.
     */
    public function test_unauthorized_users_cannot_upload_or_modify_equipment(): void
    {
        $equipment = Peralatan::first();

        // Guest redirect ke login
        $guestResponse = $this->post(route('staff.equipment.store'), [
            'name' => 'Ilegal Device',
        ]);
        $guestResponse->assertRedirect('/login');

        // Customer mendapat 403 Forbidden
        $customerStore = $this->actingAs($this->customer)->post(route('staff.equipment.store'), [
            'name' => 'Ilegal Device',
        ]);
        $customerStore->assertStatus(403);

        $customerUpdate = $this->actingAs($this->customer)->put(route('staff.equipment.update', $equipment->id), [
            'name' => 'Hacked Device',
        ]);
        $customerUpdate->assertStatus(403);
    }

    /**
     * 11. Kegagalan penyimpanan — foto lama dan data alat harus tetap konsisten.
     */
    public function test_update_failure_preserves_old_photo_and_cleans_up_new_file(): void
    {
        $oldPath = 'equipment/preserved_old_photo_'.rand(1000, 9999).'.png';
        Storage::disk('public')->put($oldPath, 'original intact photo content');

        $equipment = Peralatan::create([
            'kode' => 'EQP-FAIL-'.rand(100, 999),
            'nama' => 'Pipet Otomatis Multi-Channel',
            'kategori' => 'Liquid Handling',
            'jumlah' => 6,
            'lokasi' => 'Lab Kimia',
            'stok_minimum' => 2,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 30000,
            'biaya_deposit' => 100000,
            'foto' => $oldPath,
        ]);

        $newImage = UploadedFile::fake()->image('attempt_new.png', 400, 400);

        // Kirim request update dengan data invalid (quantity negatif melanggar validasi min:0)
        $invalidPayload = [
            'name' => 'Pipet Rusak',
            'category' => 'Liquid Handling',
            'quantity' => -10, // Invalid!
            'location' => 'Lab Kimia',
            'min_stock' => 1,
            'status' => 'Tersedia',
            'rental_price_per_day' => 30000,
            'deposit_amount' => 100000,
            'photo' => $newImage,
        ];

        $response = $this->actingAs($this->staff)->put(route('staff.equipment.update', $equipment->id), $invalidPayload);

        $response->assertSessionHasErrors('quantity');

        // Pastikan foto lama di DB dan di storage tetap utuh
        $equipment->refresh();
        $this->assertEquals($oldPath, $equipment->foto);
        $this->assertEquals('Pipet Otomatis Multi-Channel', $equipment->nama);
        Storage::disk('public')->assertExists($oldPath);
    }

    /**
     * 12. Halaman detail verifikasi publik (QR/barcode) menampilkan foto instrumen.
     */
    public function test_equipment_public_detail_verification_page_displays_photo(): void
    {
        // 1. Uji untuk alat dengan foto terpetakan (EQP-PHM-002)
        $responsePhm = $this->get(route('equipment.detail', 'EQP-PHM-002'));
        $responsePhm->assertStatus(200);
        $responsePhm->assertSee('images/equipment/digital-portable-pH-meter-waterproof-hanna.png');
        $responsePhm->assertSee('Digital Portable pH Meter Waterproof');
        $responsePhm->assertSee('Kembali');

        // 2. Uji untuk alat tanpa foto (menampilkan placeholder)
        $eqNoPhoto = Peralatan::create([
            'kode' => 'EQP-DET-'.rand(100, 999),
            'nama' => 'Buret Asam Bening',
            'kategori' => 'Volumetri',
            'jumlah' => 8,
            'lokasi' => 'Lab Analisis',
            'stok_minimum' => 2,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 20000,
            'biaya_deposit' => 50000,
            'foto' => null,
        ]);

        $responseNoPhoto = $this->get(route('equipment.detail', $eqNoPhoto->kode));
        $responseNoPhoto->assertStatus(200);
        $responseNoPhoto->assertSee('Instrumen Laboratorium Terverifikasi');
        $responseNoPhoto->assertSee('fa-microscope');
    }

    /**
     * 13. Halaman detail inventori staf menampilkan tombol 'Pratinjau Halaman QR' ke route publik di tab baru.
     */
    public function test_staff_equipment_show_displays_qr_preview_button(): void
    {
        $equipment = Peralatan::first();

        $response = $this->actingAs($this->staff)->get(route('staff.equipment.show', $equipment->id));

        $response->assertStatus(200);
        $response->assertSee('Pratinjau Halaman QR');
        $response->assertDontSee('Verifikasi Publik');
        $response->assertSee(route('equipment.detail', $equipment->code));
        $response->assertSee('target="_blank"', false);
    }

    /**
     * 14. Landing page menampilkan kartu peralatan lengkap dengan area foto dan fallback placeholder.
     */
    public function test_landing_page_equipment_cards_display_photos_and_fallback_placeholder(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Alat Laboratorium Siap Dipinjam');
        $response->assertSee('object-contain');
        $response->assertSee('Detail & QR', false);
        $response->assertSee('Pinjam Alat');

        // Memastikan foto peralatan default tampil di landing page
        $response->assertSee('images/equipment/spektrofotometer-UV-Vis-high-precision.png');
        $response->assertSee('images/equipment/digital-portable-pH-meter-waterproof-hanna.png');

        // Uji alat tanpa foto pada landing page
        $eqNoPhoto = Peralatan::create([
            'kode' => 'EQP-LND-'.rand(100, 999),
            'nama' => 'Piknometer Kaca Presisi',
            'kategori' => 'Fisika',
            'jumlah' => 4,
            'lokasi' => 'Lab Fisika Lingkungan',
            'stok_minimum' => 1,
            'status' => 'Tersedia',
            'harga_sewa_per_hari' => 15000,
            'biaya_deposit' => 30000,
            'foto' => null,
        ]);

        $responseWithNew = $this->get(route('home'));
        $responseWithNew->assertStatus(200);
        $responseWithNew->assertSee('Piknometer Kaca Presisi');
        $responseWithNew->assertSee('Instrumen Lab Terverifikasi');
        $responseWithNew->assertSee(route('equipment.detail', $eqNoPhoto->kode));
    }
}
