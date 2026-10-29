<?php

namespace Tests\Feature;

use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationAuditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_succeeds_with_valid_credentials_and_redirects_based_on_role(): void
    {
        // 1. Customer qalbi@lab.test
        $response = $this->post(route('login'), [
            'email' => 'qalbi@lab.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('customer', auth()->user()->role);
        $this->assertEquals('qalbi@lab.test', auth()->user()->email);

        $this->post(route('logout'));

        // 2. Admin admin@lab.test
        $adminRes = $this->post(route('login'), [
            'email' => 'admin@lab.test',
            'password' => 'password',
        ]);
        $adminRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', auth()->user()->role);

        $this->post(route('logout'));

        // 3. Manager manager@lab.test
        $mgrRes = $this->post(route('login'), [
            'email' => 'manager@lab.test',
            'password' => 'password',
        ]);
        $mgrRes->assertRedirect(route('manager.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('manager', auth()->user()->role);

        $this->post(route('logout'));

        // 4. Staff staff@lab.test
        $staffRes = $this->post(route('login'), [
            'email' => 'staff@lab.test',
            'password' => 'password',
        ]);
        $staffRes->assertRedirect(route('staff.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('staff', auth()->user()->role);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'qalbi@lab.test',
            'password' => 'WrongSecretPassword123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email atau password tidak cocok.',
        ]);
        $this->assertGuest();
    }

    public function test_login_fails_with_unregistered_email(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'akun.tidak.terdaftar.99@lab.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'email' => 'Email atau password tidak cocok.',
        ]);
        $this->assertGuest();
    }

    public function test_login_requires_mandatory_fields(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_remember_me_functionality(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'qalbi@lab.test',
            'password' => 'password',
            'remember' => 'on',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertAuthenticated();
        $this->assertNotNull(auth()->user()->fresh()->remember_token);
    }

    public function test_guest_cannot_access_protected_pages(): void
    {
        $protectedRoutes = [
            'customer.dashboard',
            'staff.dashboard',
            'manager.dashboard',
            'admin.dashboard',
            'profile.edit',
            'notifications.index',
        ];

        foreach ($protectedRoutes as $routeName) {
            $response = $this->get(route($routeName));
            $response->assertRedirect(route('login'), "Route [{$routeName}] did not redirect unauthenticated guest to login.");
        }
    }

    public function test_user_cannot_access_other_role_pages_without_permission(): void
    {
        $customer = User::where('email', 'qalbi@lab.test')->first();

        // Customer attempts to access staff, manager, admin dashboards
        $unauthorizedRoutes = [
            'staff.dashboard',
            'manager.dashboard',
            'admin.dashboard',
            'admin.users.index',
            'admin.finance.index',
        ];

        foreach ($unauthorizedRoutes as $routeName) {
            $response = $this->actingAs($customer)->get(route($routeName));
            $response->assertStatus(403, "Customer was unexpectedly allowed to access [{$routeName}].");
        }
    }

    public function test_login_view_has_loading_spinner_and_prevents_double_submit(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('x-data="{ submitting: false }"', false);
        $response->assertSee('@submit="if ($el.checkValidity()) submitting = true"', false);
        $response->assertSee("window.addEventListener('pageshow', () => submitting = false)", false);
        $response->assertSee(':disabled="submitting"', false);
        $response->assertSee('fa-spinner fa-spin', false);
        $response->assertSee("submitting ? 'Memproses Masuk...' : 'Masuk'", false);
    }

    public function test_logout_terminates_session_completely(): void
    {
        $user = User::where('email', 'qalbi@lab.test')->first();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Anda telah berhasil keluar dari sistem.');
        $this->assertGuest();
    }

    public function test_authenticated_private_pages_send_no_cache_headers_to_prevent_back_history(): void
    {
        $customer = User::where('role', 'customer')->first();
        $response = $this->actingAs($customer)->get(route('customer.dashboard'));

        $response->assertStatus(200);
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
        $this->assertEquals('0', $response->headers->get('Expires'));
    }

    public function test_after_logout_session_is_invalidated_and_private_access_is_rejected(): void
    {
        $customer = User::where('role', 'customer')->first();
        $this->actingAs($customer);
        $this->assertAuthenticated();

        $logoutRes = $this->post(route('logout'));
        $logoutRes->assertRedirect(route('home'));
        $this->assertGuest();

        // Attempting to reopen private dashboard must redirect to login
        $reopenRes = $this->get(route('customer.dashboard'));
        $reopenRes->assertRedirect(route('login'));

        // Attempting to access other private dashboards also redirects
        $this->get(route('staff.dashboard'))->assertRedirect(route('login'));
        $this->get(route('manager.dashboard'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_is_strictly_forbidden_from_manager_technical_verifications(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();
        $customer = User::where('role', 'customer')->first();

        $sample = Sample::create([
            'sample_code' => 'SMP-RBAC-'.uniqid(),
            'customer_id' => $customer->id,
            'sample_type' => 'Air Limbah',
            'sampling_location' => 'Outlet Pabrik',
            'sampling_date' => now()->subDay(),
            'request_date' => now(),
            'status' => 'Menunggu Verifikasi',
        ]);

        // 1. Admin GET manager dashboard -> 403
        $this->actingAs($admin)->get(route('manager.dashboard'))->assertStatus(403);

        // 2. Admin GET verifications list -> 403
        $this->actingAs($admin)->get(route('manager.verifications.index'))->assertStatus(403);

        // 3. Admin GET verification detail -> 403
        $this->actingAs($admin)->get(route('manager.verifications.show', $sample))->assertStatus(403);

        // 4. Admin POST approve -> 403
        $this->actingAs($admin)->post(route('manager.verifications.approve', $sample))->assertStatus(403);
        $this->assertEquals('Menunggu Verifikasi', $sample->fresh()->status);

        // 5. Admin POST reject -> 403
        $this->actingAs($admin)->post(route('manager.verifications.reject', $sample), [
            'alasan_penolakan' => 'Uji coba penolakan oleh admin',
        ])->assertStatus(403);
        $this->assertEquals('Menunggu Verifikasi', $sample->fresh()->status);

        // 6. Manager CAN access all these endpoints
        $this->actingAs($manager)->get(route('manager.dashboard'))->assertStatus(200);
        $this->actingAs($manager)->get(route('manager.verifications.index'))->assertStatus(200);
        $this->actingAs($manager)->get(route('manager.verifications.show', $sample))->assertStatus(200);
    }

    public function test_admin_and_manager_can_both_access_satisfaction_reports_per_srs(): void
    {
        $admin = User::where('role', 'admin')->first();
        $manager = User::where('role', 'manager')->first();

        // Admin must be able to view satisfaction report on admin module
        $adminRes = $this->actingAs($admin)->get(route('admin.reports.satisfaction'));
        $adminRes->assertStatus(200);

        // Manager must be able to view satisfaction report on manager module
        $mgrRes = $this->actingAs($manager)->get(route('manager.reports.satisfaction'));
        $mgrRes->assertStatus(200);
    }
}
