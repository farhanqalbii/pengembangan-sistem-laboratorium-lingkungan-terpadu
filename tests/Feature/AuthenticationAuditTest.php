<?php

namespace Tests\Feature;

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
}
