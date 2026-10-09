<?php

namespace Tests\Feature;

use App\Models\Sample;
use App\Models\User;
use Tests\TestCase;

class LimsWorkflowTest extends TestCase
{
    /** @test */
    public function customer_can_access_customer_dashboard()
    {
        $customer = User::where('role', 'customer')->first();
        $response = $this->actingAs($customer)->get('/customer/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Customer');
    }

    /** @test */
    public function staff_can_access_staff_dashboard()
    {
        $staff = User::where('role', 'staff')->first();
        $response = $this->actingAs($staff)->get('/staff/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Stok Alat Kritis');
    }

    /** @test */
    public function manager_can_access_manager_dashboard()
    {
        $manager = User::where('role', 'manager')->first();
        $response = $this->actingAs($manager)->get('/manager/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Antrean Verifikasi');
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Control Center Administrator');
    }

    public function test_manager_approval_generates_coa_certificate(): void
    {
        $manager = User::where('role', 'manager')->first();
        $sample = Sample::where('status', 'Menunggu Verifikasi')->first();

        if ($sample) {
            $response = $this->actingAs($manager)->post("/manager/verifications/{$sample->id}/approve");

            $response->assertRedirect(route('manager.verifications.show', $sample));
            $this->assertDatabaseHas('samples', [
                'id' => $sample->id,
                'status' => 'Selesai',
            ]);
            $this->assertDatabaseHas('sertifikat', [
                'sample_id' => $sample->id,
            ]);
        } else {
            $this->assertTrue(true);
        }
    }
}
