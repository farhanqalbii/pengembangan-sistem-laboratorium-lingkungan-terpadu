<?php

namespace Tests\Feature;

use App\Models\Peralatan;
use App\Models\User;
use Tests\TestCase;

class AllRoutesHealthTest extends TestCase
{
    protected ?User $customer;

    protected ?User $staff;

    protected ?User $manager;

    protected ?User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::where('role', 'customer')->first();
        $this->staff = User::where('role', 'staff')->first();
        $this->manager = User::where('role', 'manager')->first();
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_guest_routes_render_ok(): void
    {
        $guestRoutes = [
            '/',
            '/login',
            '/register',
            '/forgot-password',
            '/equipment/scan',
            '/equipment/detail/EQP-SPE-001',
        ];

        foreach ($guestRoutes as $url) {
            $response = $this->get($url);
            $response->assertStatus(200, "Failed accessing URL: {$url}");
        }

        // Test public sample tracking API
        $notFoundRes = $this->get('/track-sample?code=PMP-20260901-0001');
        $notFoundRes->assertStatus(404);
        $this->assertStringContainsString('tidak ditemukan di sistem', $notFoundRes->json('message'));
        $this->assertStringNotContainsString('Pastikan format kode sudah sesuai', $notFoundRes->json('message'));

        $invalidFormatRes = $this->get('/track-sample?code=xyz');
        $invalidFormatRes->assertStatus(422);
        $this->assertStringContainsString('Format kode', $invalidFormatRes->json('message'));

        $validRes = $this->get('/track-sample?code=SMP-20260901-0001');
        $this->assertContains($validRes->status(), [200, 404]);
    }

    public function test_customer_routes_render_ok(): void
    {
        $this->actingAs($this->customer);

        $routes = [
            '/customer/dashboard',
            '/customer/samples',
            '/customer/samples/create',
            '/customer/borrowings',
            '/customer/borrowings/create',
            '/customer/certificates',
            '/customer/payments',
            '/customer/satisfaction?type=Pengujian&id=1',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed on Customer URL: {$url}");
        }
    }

    public function test_staff_routes_render_ok(): void
    {
        $this->actingAs($this->staff);

        $eq = Peralatan::first();
        $eqCode = $eq?->kode ?? 'EQP-SPE-001';

        $routes = [
            '/staff/dashboard',
            '/staff/samples',
            '/staff/equipment',
            '/staff/equipment/'.$eqCode,
            '/staff/equipment/create',
            '/staff/borrowings',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed on Staff URL: {$url}");
        }

        // Test staff equipment detail contains QR & Cetak Stiker
        $res = $this->get('/staff/equipment/'.$eqCode);
        $res->assertSee('Cetak Stiker Label');
        $res->assertSee('qrcode-display');
        $res->assertSee('barcode-display');

        // Test numeric ID is rejected with 404
        if ($eq) {
            $this->get('/staff/equipment/'.$eq->id)->assertStatus(404);
        }
    }

    public function test_manager_routes_render_ok(): void
    {
        $this->actingAs($this->manager);

        $routes = [
            '/manager/dashboard',
            '/manager/verifications',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed on Manager URL: {$url}");
        }
    }

    public function test_admin_routes_render_ok(): void
    {
        $this->actingAs($this->admin);

        $routes = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/users/create',
            '/admin/parameters',
            '/admin/parameters/create',
            '/admin/borrowings',
            '/admin/finance',
            '/admin/activity-logs',
            '/admin/reports/samples',
            '/admin/reports/inventory',
            '/admin/reports/borrowings',
            '/admin/reports/financial',
            '/admin/reports/satisfaction',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed on Admin URL: {$url}");
        }
    }

    public function test_shared_routes_render_ok(): void
    {
        $this->actingAs($this->staff);

        $routes = [
            '/notifications',
            '/profile',
        ];

        foreach ($routes as $url) {
            $response = $this->get($url);
            $this->assertEquals(200, $response->status(), "Failed on Shared URL: {$url}");
        }
    }
}
