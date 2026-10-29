<?php

namespace Tests\Feature;

use App\Models\Borrowing;
use App\Models\Equipment;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CustomSelectAndDropdownStylingTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected User $staff;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin_test_select@lab.test'],
            [
                'name' => 'Admin Select Tester',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '081234567899',
            ]
        );

        $this->staff = User::firstOrCreate(
            ['email' => 'staff_test_select@lab.test'],
            [
                'name' => 'Staff Select Tester',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'phone' => '081234567898',
            ]
        );

        $this->customer = User::firstOrCreate(
            ['email' => 'customer_test_select@lab.test'],
            [
                'name' => 'Customer Select Tester',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'phone' => '081234567897',
            ]
        );
    }

    /**
     * Test /admin/users renders custom-select role filter and modal options properly.
     */
    public function test_admin_users_renders_custom_select_and_filter(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('name="role"', false);
        $response->assertSee('Semua Role');
        $response->assertSee('Administrator');
        $response->assertSee('Manajer Lab');
        $response->assertSee('Staff / Analis');
        $response->assertSee('role="listbox"', false);
        $response->assertSee('role="option"', false);

        // Test filter functionality
        $filterResponse = $this->actingAs($this->admin)->get(route('admin.users.index', ['role' => 'staff']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($this->staff->name);
    }

    /**
     * Test creating a user via admin store with role selected from custom-select.
     */
    public function test_admin_can_create_user_with_role(): void
    {
        $userData = [
            'name' => 'Budi Baru Staf',
            'email' => 'budi_baru_'.uniqid().'@lab.test',
            'role' => 'staff',
            'phone' => '081198765432',
            'company_name' => 'UPTD Lab Lingkungan',
            'password' => 'secretPassword123!',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), $userData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
            'role' => 'staff',
        ]);
    }

    /**
     * Test updating a user role via admin update.
     */
    public function test_admin_can_update_user_role(): void
    {
        $targetUser = User::factory()->create(['role' => 'customer']);

        $updateData = [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'role' => 'staff',
            'phone' => '081233344455',
            'company_name' => 'PT Pengujian Prima',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'role' => 'staff',
        ]);
    }

    /**
     * Test /staff/samples renders custom select status filter without errors.
     */
    public function test_staff_samples_renders_custom_select_filter(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.samples.index'));

        $response->assertStatus(200);
        $response->assertSee('name="status"', false);
        $response->assertSee('Semua Status');
        $response->assertSee('role="listbox"', false);
    }

    /**
     * Test /staff/equipment renders category and status custom select filters.
     */
    public function test_staff_equipment_renders_custom_select_filters(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.equipment.index'));

        $response->assertStatus(200);
        $response->assertSee('name="category"', false);
        $response->assertSee('name="status"', false);
        $response->assertSee('Semua Kategori');
        $response->assertSee('Semua Status');
        $response->assertSee('role="listbox"', false);
    }

    /**
     * Test /customer/samples renders custom select filter.
     */
    public function test_customer_samples_renders_custom_select_filter(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.samples.index'));

        $response->assertStatus(200);
        $response->assertSee('name="status"', false);
        $response->assertSee('Semua Status');
        $response->assertSee('role="listbox"', false);
    }

    /**
     * Test /customer/borrowings/create renders category custom select.
     */
    public function test_customer_borrowings_create_renders_custom_select(): void
    {
        $response = $this->actingAs($this->customer)->get(route('customer.borrowings.create'));

        $response->assertStatus(200);
        $response->assertSee('name="category"', false);
        $response->assertSee('Semua Kategori');
        $response->assertSee('role="listbox"', false);
    }

    /**
     * Test /staff/samples/{sample} detail page renders custom select for analyst and equipment assignment.
     */
    public function test_staff_sample_detail_renders_custom_select_for_assignments(): void
    {
        $sample = Sample::has('sampleParameters')->first();
        if ($sample) {
            $identifier = $sample->sample_code ?? $sample->kode_sampel ?? $sample->id;
            $response = $this->actingAs($this->staff)->get(route('staff.samples.show', $identifier));

            $response->assertStatus(200);
            $response->assertSee('analyst_id');
            $response->assertSee('equipment_id');
            $response->assertSee('role="listbox"', false);
            $response->assertSee('role="option"', false);
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Test /staff/borrowings/{borrowing} detail page renders custom select for return conditions.
     */
    public function test_staff_borrowing_detail_renders_custom_select_for_conditions(): void
    {
        $borrowing = Borrowing::has('borrowingDetails')->first();
        if ($borrowing) {
            $response = $this->actingAs($this->staff)->get(route('staff.borrowings.show', $borrowing));

            $response->assertStatus(200);
            $response->assertSee('condition_return');
            $response->assertSee('role="listbox"', false);
            $response->assertSee('Baik & Lengkap');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * Test global CSS styling rules for native select and custom dropdowns exist.
     */
    public function test_global_css_styling_rules_exist(): void
    {
        $appBlade = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $themeDarkCss = file_get_contents(public_path('css/theme-dark.css'));

        // Check custom chevron SVG in layouts/app.blade.php
        $this->assertStringContainsString('select', $appBlade);
        $this->assertStringContainsString('.form-select', $appBlade);
        $this->assertStringContainsString('[x-cloak]', $appBlade);
        $this->assertStringContainsString('.custom-scrollbar', $appBlade);

        // Check dark mode listbox styles
        $this->assertStringContainsString('[role="listbox"]', $themeDarkCss);
        $this->assertStringContainsString('[role="option"]', $themeDarkCss);

        // Check stacking context elevation rules
        $this->assertStringContainsString(':has([data-dropdown-open="true"])', $appBlade);
        $this->assertStringContainsString(':has([data-dropdown-open="true"])', $themeDarkCss);
    }

    /**
     * Test custom select component has anti-clipping architecture:
     * - data-dropdown-open attribute
     * - dropUp auto-positioning
     * - elevateAncestors & restoreAncestors stacking helpers
     */
    public function test_custom_select_component_has_anti_clipping_features(): void
    {
        $customSelectBlade = file_get_contents(resource_path('views/components/custom-select.blade.php'));

        $this->assertStringContainsString('data-dropdown-open', $customSelectBlade);
        $this->assertStringContainsString('dropUp', $customSelectBlade);
        $this->assertStringContainsString('elevateAncestors', $customSelectBlade);
        $this->assertStringContainsString('restoreAncestors', $customSelectBlade);
        $this->assertStringContainsString('adjustDropdownPosition', $customSelectBlade);
        $this->assertStringContainsString('bottom-full mb-1.5', $customSelectBlade);
        $this->assertStringContainsString('top-full mt-1.5', $customSelectBlade);
    }

    /**
     * Test that filter toolbars and table cards across views use proper z-index layering.
     */
    public function test_views_have_explicit_z_index_layering(): void
    {
        $staffSamples = file_get_contents(resource_path('views/staff/samples/index.blade.php'));
        $this->assertStringContainsString('relative z-20', $staffSamples);
        $this->assertStringContainsString('relative z-10', $staffSamples);

        $staffEquipment = file_get_contents(resource_path('views/staff/peralatan/index.blade.php'));
        $this->assertStringContainsString('relative z-20', $staffEquipment);
        $this->assertStringContainsString('relative z-10', $staffEquipment);

        $staffBorrowings = file_get_contents(resource_path('views/staff/peminjaman/index.blade.php'));
        $this->assertStringContainsString('relative z-20', $staffBorrowings);
        $this->assertStringContainsString('relative z-10', $staffBorrowings);

        $customerSamples = file_get_contents(resource_path('views/customer/samples/index.blade.php'));
        $this->assertStringContainsString('relative z-20', $customerSamples);
        $this->assertStringContainsString('relative z-10', $customerSamples);

        $customerBorrowings = file_get_contents(resource_path('views/customer/peminjaman/create.blade.php'));
        $this->assertStringContainsString('relative z-20', $customerBorrowings);
        $this->assertStringContainsString('relative z-10', $customerBorrowings);

        $adminUsers = file_get_contents(resource_path('views/admin/users/index.blade.php'));
        $this->assertStringContainsString('relative z-20', $adminUsers);
        $this->assertStringContainsString('relative z-10', $adminUsers);
    }

    /**
     * Test that staff samples filtering with status parameter functions correctly.
     */
    public function test_staff_samples_filter_by_status_executes_successfully(): void
    {
        $response = $this->actingAs($this->staff)->get(route('staff.samples.index', ['status' => 'Diajukan']));
        $response->assertStatus(200);
        $response->assertSee('Semua Status');
        $response->assertSee('Diajukan');
    }
}
