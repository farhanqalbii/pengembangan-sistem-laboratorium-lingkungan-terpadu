<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use Tests\TestCase;

class ThemeSettingsTest extends TestCase
{
    public function test_landing_page_does_not_render_theme_switcher_controls(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('themePicker()');
        $response->assertDontSee('Default Sistem');
        $response->assertDontSee('js/theme-switcher.js');
        $response->assertDontSee('css/theme-dark.css');
        $this->assertDoesNotMatchRegularExpression('/<html[^>]*class="[^"]*\bdark\b[^"]*"/', $response->getContent());
    }

    public function test_landing_page_always_stays_in_original_colors_even_with_dark_cookie(): void
    {
        $response = $this->withCookie('theme_preference', 'dark')->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('js/theme-switcher.js');
        $response->assertDontSee('css/theme-dark.css');
        $this->assertDoesNotMatchRegularExpression('/<html[^>]*class="[^"]*\bdark\b[^"]*"/', $response->getContent());
    }

    public function test_guest_auth_pages_render_theme_switcher(): void
    {
        // Login page
        $loginRes = $this->get(route('login'));
        $loginRes->assertStatus(200);
        $loginRes->assertSee('js/theme-switcher.js');
        $loginRes->assertSee('css/theme-dark.css');
        $loginRes->assertSee('fa-desktop');
        $loginRes->assertSee('fa-sun');
        $loginRes->assertSee('fa-moon');

        // Register page
        $regRes = $this->get(route('register'));
        $regRes->assertStatus(200);
        $regRes->assertSee('js/theme-switcher.js');
        $regRes->assertSee('css/theme-dark.css');
    }

    public function test_all_four_role_dashboards_render_theme_support(): void
    {
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $res = $this->actingAs($admin)->get(route('admin.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('js/theme-switcher.js');
            $res->assertSee('css/theme-dark.css');
            $res->assertSee('Default Sistem');
        }

        $manager = User::where('role', 'manager')->first();
        if ($manager) {
            $res = $this->actingAs($manager)->get(route('manager.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('js/theme-switcher.js');
            $res->assertSee('css/theme-dark.css');
        }

        $staff = User::where('role', 'staff')->first();
        if ($staff) {
            $res = $this->actingAs($staff)->get(route('staff.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('js/theme-switcher.js');
            $res->assertSee('css/theme-dark.css');
        }

        $customer = User::where('role', 'customer')->first();
        if ($customer) {
            $res = $this->actingAs($customer)->get(route('customer.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('js/theme-switcher.js');
            $res->assertSee('css/theme-dark.css');
        }
    }

    public function test_header_theme_dropdown_is_exclusive_and_profile_dropdown_has_no_theme_controls(): void
    {
        $rolesAndRoutes = [
            'admin' => route('admin.dashboard'),
            'manager' => route('manager.dashboard'),
            'staff' => route('staff.dashboard'),
            'customer' => route('customer.dashboard'),
        ];

        foreach ($rolesAndRoutes as $role => $route) {
            $user = User::where('role', $role)->first();
            if (! $user) {
                continue;
            }

            $res = $this->actingAs($user)->get($route);
            $res->assertStatus(200);

            // Header theme switcher controls
            $res->assertSee('Pilih Tema Tampilan');
            $res->assertSee('Tema Tampilan');
            $res->assertSee('fa-desktop');
            $res->assertSee('fa-sun');
            $res->assertSee('fa-moon');
            $res->assertSee('Sistem');
            $res->assertSee('Light');
            $res->assertSee('Dark');

            // Verify user profile dropdown contains Profile and Sign Out
            $res->assertSee(route('profile.edit'));
            $res->assertSee('Sign Out');

            // Verify the profile dropdown card has no theme controls
            $content = $res->getContent();
            $this->assertStringContainsString('userMenuOpen', $content);
            preg_match('/x-show="userMenuOpen"[\s\S]*?<\/div>\s*<\/div>\s*<\/div>/', $content, $matches);
            $userMenuContent = $matches[0] ?? '';
            $this->assertNotEmpty($userMenuContent);
            $this->assertStringNotContainsString('Tema Tampilan', $userMenuContent);
            $this->assertStringNotContainsString('setTheme', $userMenuContent);
            $this->assertStringNotContainsString('themePicker', $userMenuContent);
        }
    }

    public function test_profile_page_does_not_display_redundant_theme_card_and_uses_header_switcher(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        // Header theme switcher exists in topbar
        $response->assertSee('Pilih Tema Tampilan');
        // Redundant theme preference card is removed from profile content
        $response->assertDontSee('Sesuaikan preferensi tema antarmuka');
        $response->assertDontSee('Pilih mode tampilan sesuai kenyamanan Anda saat bekerja');
    }

    public function test_public_equipment_detail_renders_theme_switcher(): void
    {
        $equipment = Equipment::first();
        if (! $equipment) {
            $this->markTestSkipped('No equipment found in database.');
        }

        $response = $this->get(route('equipment.detail', $equipment->code));

        $response->assertStatus(200);
        $response->assertSee('js/theme-switcher.js');
        $response->assertSee('css/theme-dark.css');
        $response->assertSee('fa-desktop');
        $response->assertSee('fa-sun');
        $response->assertSee('fa-moon');
    }

    public function test_public_equipment_scan_renders_theme_switcher(): void
    {
        $response = $this->get(route('equipment.scan'));

        $response->assertStatus(200);
        $response->assertSee('js/theme-switcher.js');
        $response->assertSee('css/theme-dark.css');
        $response->assertSee('fa-desktop');
        $response->assertSee('fa-sun');
        $response->assertSee('fa-moon');
        $response->assertSee('logo_lab_lgn_rgst.png');
        $response->assertSee('logo_lab_navbar_clean.png');
        $response->assertSee('block dark:hidden');
        $response->assertSee('hidden dark:block');
    }

    public function test_server_rendered_dark_mode_class_with_cookie(): void
    {
        // When cookie has theme_preference=dark, server renders <html ... class="... dark ...">
        $response = $this->withCookie('theme_preference', 'dark')->get(route('login'));

        $response->assertStatus(200);
        $this->assertMatchesRegularExpression('/<html[^>]*class="[^"]*\bdark\b[^"]*"/', $response->getContent());
    }

    public function test_server_rendered_light_mode_without_dark_class(): void
    {
        $response = $this->withCookie('theme_preference', 'light')->get(route('login'));

        $response->assertStatus(200);
        $this->assertDoesNotMatchRegularExpression('/<html[^>]*class="[^"]*\bdark\b[^"]*"/', $response->getContent());
    }

    public function test_sidebar_defines_soft_blue_and_midnight_palettes(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (! $admin) {
            $this->markTestSkipped('No admin user found.');
        }

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Light Mode Soft Blue Palette
        $response->assertSee('--sidebar-bg: #EAF2F8', false);
        $response->assertSee('--sidebar-logo-bg: #EAF2F8', false);
        $response->assertSee('--sidebar-hover-bg: #D8E7F3', false);
        $response->assertSee('--sidebar-text: #17324D', false);
        $response->assertSee('--sidebar-icon: #36536D', false);
        $response->assertSee('--sidebar-active-bg: #168F7A', false);
        $response->assertSee('--sidebar-border: #D1DFEB', false);

        // Dark Mode Midnight Palette
        $response->assertSee('--sidebar-bg: #071521', false);
        $response->assertSee('--sidebar-logo-bg: #071521', false);
        $response->assertSee('--sidebar-hover-bg: #142A3D', false);
        $response->assertSee('--sidebar-text: #C4D2DF', false);
        $response->assertSee('--sidebar-icon: #C4D2DF', false);
        $response->assertSee('--sidebar-border: #1C3448', false);

        // Preserved Original Logos
        $response->assertSee('logo_lab_lgn_rgst.png');
        $response->assertSee('logo_lab_navbar_clean.png');
        $response->assertSee('block dark:hidden');
        $response->assertSee('hidden dark:block');

        // Sidebar Classes
        $response->assertSee('app-sidebar');
        $response->assertSee('app-sidebar-logo');
        $response->assertSee('app-sidebar-link');
        $response->assertSee('is-active');
    }

    public function test_sidebar_renders_cleanly_for_all_user_roles(): void
    {
        // Admin
        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            $res = $this->actingAs($admin)->get(route('admin.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('app-sidebar');
            $res->assertSee('Administrator Portal');
            $res->assertSee('Dashboard Admin');
        }

        // Manager
        $manager = User::where('role', 'manager')->first();
        if ($manager) {
            $res = $this->actingAs($manager)->get(route('manager.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('app-sidebar');
            $res->assertSee('Manajer Lab Portal');
            $res->assertSee('Dashboard Manajer');
        }

        // Staff
        $staff = User::where('role', 'staff')->first();
        if ($staff) {
            $res = $this->actingAs($staff)->get(route('staff.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('app-sidebar');
            $res->assertSee('Staff / Analis Portal');
            $res->assertSee('Dashboard Staf');
        }

        // Customer
        $customer = User::where('role', 'customer')->first();
        if ($customer) {
            $res = $this->actingAs($customer)->get(route('customer.dashboard'));
            $res->assertStatus(200);
            $res->assertSee('app-sidebar');
            $res->assertSee('Customer Portal');
            $res->assertSee('Dashboard');
        }
    }
}
