<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Parameter;
use App\Models\Peralatan;
use App\Models\Sample;
use App\Models\User;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class Week1LogicParityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $staff;

    protected User $manager;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->manager = User::factory()->create(['role' => 'manager']);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    /** 1. Authentication: Login, Role-based redirect, and Logout */
    public function test_auth_login_redirects_correctly_per_role(): void
    {
        // Customer login redirects to customer dashboard
        $res = $this->post(route('login'), [
            'email' => $this->customer->email,
            'password' => 'password',
        ]);
        $res->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($this->customer);

        // Logout redirects to home
        $logoutRes = $this->post(route('logout'));
        $logoutRes->assertRedirect(route('home'));
        $this->assertGuest();
    }

    /** 2. RBAC: RoleMiddleware blocks unauthorized access with 403 */
    public function test_rbac_prevents_cross_role_access(): void
    {
        // Customer cannot access staff dashboard
        $res1 = $this->actingAs($this->customer)->get(route('staff.dashboard'));
        $res1->assertStatus(403);

        // Customer cannot access manager dashboard
        $res2 = $this->actingAs($this->customer)->get(route('manager.dashboard'));
        $res2->assertStatus(403);

        // Staff cannot access admin dashboard
        $res3 = $this->actingAs($this->staff)->get(route('admin.dashboard'));
        $res3->assertStatus(403);

        // Customer cannot access other customer's sample detail (Controller authorization)
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $otherSample = Sample::create([
            'sample_code' => 'SMP-TEST-0001',
            'customer_id' => $otherCustomer->id,
            'sample_type' => 'Air Sungai',
            'sampling_location' => 'Sungai Citarum',
            'sampling_date' => now(),
            'request_date' => now(),
            'status' => 'Diajukan',
        ]);

        $res4 = $this->actingAs($this->customer)->get(route('customer.samples.show', $otherSample));
        $res4->assertStatus(403);
    }

    /** 3. Modul 1: Full Sample and Testing Lifecycle matches Acuan Project */
    public function test_sample_and_testing_lifecycle_matches_acuan(): void
    {
        $parameter = Parameter::first();
        if (! $parameter) {
            $parameter = Parameter::create([
                'name' => 'pH Air',
                'category' => 'Fisika & Kimia',
                'unit' => 'pH',
                'test_method' => 'SNI 06-6989.11-2004',
                'price' => 50000,
            ]);
        }

        $equipment = Peralatan::where('status', 'Tersedia')->first();
        if (! $equipment) {
            $equipment = Peralatan::create([
                'code' => 'EQ-PARITY-1',
                'name' => 'pH Meter Lab',
                'category' => 'Instrumen',
                'quantity' => 5,
                'min_stock' => 1,
                'status' => 'Tersedia',
            ]);
        }

        // A. Customer submits sample
        $submitRes = $this->actingAs($this->customer)->post(route('customer.samples.store'), [
            'sample_type' => 'Air Bersih',
            'sampling_location' => 'Sumber Mata Air',
            'sampling_date' => now()->toDateString(),
            'parameters' => [$parameter->id],
            'notes' => 'Uji kelayakan konsumsi',
        ]);

        $sample = Sample::where('customer_id', $this->customer->id)->latest()->first();
        $this->assertNotNull($sample);
        $submitRes->assertRedirect(route('customer.samples.show', $sample));

        $this->assertEquals('Diajukan', $sample->status);
        $this->assertStringStartsWith('SMP-', $sample->sample_code);

        // Parity Check: Confirmation notification sent to customer
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->customer->id,
            'title' => 'Pengajuan Sampel Berhasil',
        ]);

        // Parity Check: Notification sent to staff
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->staff->id,
            'title' => 'Pengajuan Sampel Baru',
        ]);

        // Settle payment before staff can approve / receive sample
        $payment = $sample->payments()->latest()->first();
        if ($payment) {
            app(PaymentGatewayService::class)->handleSuccessfulPayment($payment, 'TRX-PARITY-'.rand(), 'bank_transfer');
        }

        // B. Staff receives sample
        $receiveRes = $this->actingAs($this->staff)->post(route('staff.samples.receive', $sample), [
            'received_date' => now()->toDateString(),
        ]);
        $receiveRes->assertRedirect();
        $sample->refresh();
        $this->assertEquals('Diterima', $sample->status);

        // C. Staff assigns analyst and equipment
        $sampleParam = $sample->sampleParameters->first();
        $assignRes = $this->actingAs($this->staff)->post(route('staff.samples.assign', $sample), [
            'assignments' => [
                [
                    'sample_parameter_id' => $sampleParam->id,
                    'analyst_id' => $this->staff->id,
                    'equipment_id' => $equipment->id,
                ],
            ],
        ]);
        $assignRes->assertRedirect();
        $sample->refresh();
        $this->assertEquals('Diuji', $sample->status);

        // D. Staff inputs test results
        $inputRes = $this->actingAs($this->staff)->post(route('staff.samples.store-results', $sample), [
            'results' => [
                [
                    'sample_parameter_id' => $sampleParam->id,
                    'test_value' => '7.2',
                    'notes' => 'Kondisi stabil normal',
                ],
            ],
        ]);
        $inputRes->assertRedirect();
        $sample->refresh();
        $this->assertEquals('Menunggu Verifikasi', $sample->status);

        // Parity Check: Manager notified of pending verification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->manager->id,
            'title' => 'Hasil Uji Menunggu Verifikasi',
        ]);

        // E. Public tracking check
        $trackRes = $this->getJson(route('sample.track', ['code' => $sample->sample_code]));
        $trackRes->assertStatus(200);
        $trackRes->assertJson([
            'success' => true,
            'data' => [
                'sample_code' => $sample->sample_code,
                'status' => 'Menunggu Verifikasi',
            ],
        ]);
    }

    /** 4. Notification markAsRead and markAllAsRead parity */
    public function test_notification_mark_as_read_parity(): void
    {
        $notif = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Test Notif',
            'message' => 'Pesan pengujian',
            'link' => '/customer/dashboard',
        ]);

        // markAsRead by ID
        $res = $this->actingAs($this->customer)->get(route('notifications.read', $notif->id));
        $res->assertRedirect('/customer/dashboard');
        $this->assertNotNull($notif->fresh()->read_at);

        // markAllAsRead
        $resAll = $this->actingAs($this->customer)->post(route('notifications.read-all'));
        $resAll->assertRedirect();
        $resAll->assertSessionHas('success', 'Semua notifikasi telah ditandai dibaca.');
    }
}
