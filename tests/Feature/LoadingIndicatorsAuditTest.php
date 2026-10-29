<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoadingIndicatorsAuditTest extends TestCase
{
    public function test_auth_views_contain_submit_spinners(): void
    {
        // Login View
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('fa-spinner fa-spin');
        $response->assertSee('submitting');
        $response->assertSee('Memproses Masuk...');

        // Register View
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('fa-spinner fa-spin');
        $response->assertSee('submitting');
        $response->assertSee('Mendaftarkan Akun...');

        // Forgot Password View
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('fa-spinner fa-spin');
        $response->assertSee('submitting');
        $response->assertSee('Mengirim Permintaan...');

        // Reset Password View
        $user = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);
        $resetToken = 'valid-test-token-'.uniqid();
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($resetToken),
                'created_at' => now(),
            ]
        );

        $response = $this->get(route('password.reset', ['token' => $resetToken, 'email' => $user->email]));
        $response->assertStatus(200);
        $response->assertSee('fa-spinner fa-spin');
        $response->assertSee('submitting');
        $response->assertSee('Menyimpan Kata Sandi...');
    }

    public function test_main_layout_contains_top_navigation_loader_and_notification_spinner(): void
    {
        $user = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('customer.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('id="top-nav-loader"', false);
        $response->assertSee('id="top-nav-loader-bar"', false);
        $response->assertSee('fa-spinner fa-spin text-[10px]');
        $response->assertSee('Menandai...');
    }

    public function test_customer_and_staff_workflow_forms_contain_submit_spinners(): void
    {
        $customer = User::where('role', 'customer')->first() ?? User::factory()->create(['role' => 'customer']);

        // Customer Samples Create
        $response = $this->actingAs($customer)->get(route('customer.samples.create'));
        $response->assertStatus(200);
        $response->assertSee('submitting');
        $response->assertSee('Mengirim Pengajuan...');

        // Customer Borrowing Create
        $response = $this->actingAs($customer)->get(route('customer.borrowings.create'));
        $response->assertStatus(200);
        $response->assertSee('submitting');
        $response->assertSee('Mengirim Pengajuan...');

        // Profile Edit
        $response = $this->actingAs($customer)->get(route('profile.edit'));
        $response->assertStatus(200);
        $response->assertSee('Menyimpan Profil...');
        $response->assertSee('Memperbarui Kata Sandi...');

        // Preserved spinners: Landing page
        $landingResponse = $this->get(route('home'));
        $landingResponse->assertStatus(200);
        $landingResponse->assertSee('trackingLoading');
        $landingResponse->assertSee('fa-solid fa-spinner fa-spin');
    }
}
