<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\User;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    public function test_admin_can_access_all_admin_pages(): void
    {
        $admin = User::where('role', 'admin')->first();

        $routes = [
            'admin.dashboard',
            'admin.users.index',
            'admin.users.create',
            'admin.borrowings.index',
            'admin.finance.index',
            'admin.parameters.index',
            'admin.parameters.create',
            'admin.activity-logs.index',
            'admin.reports.samples',
            'admin.reports.inventory',
            'admin.reports.borrowings',
            'admin.reports.financial',
            'admin.reports.satisfaction',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($admin)->get(route($routeName));
            $response->assertStatus(200, "Failed accessing route: {$routeName}");
        }
    }

    public function test_manager_can_access_all_manager_pages(): void
    {
        $manager = User::where('role', 'manager')->first();

        $routes = [
            'manager.dashboard',
            'manager.verifications.index',
            'manager.reports.satisfaction',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($manager)->get(route($routeName));
            $response->assertStatus(200, "Failed accessing route: {$routeName}");
        }
    }

    public function test_staff_can_access_all_staff_pages(): void
    {
        $staff = User::where('role', 'staff')->first();

        $routes = [
            'staff.dashboard',
            'staff.samples.index',
            'staff.equipment.index',
            'staff.equipment.create',
            'staff.borrowings.index',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($staff)->get(route($routeName));
            $response->assertStatus(200, "Failed accessing route: {$routeName}");
        }
    }

    public function test_customer_can_access_all_customer_pages(): void
    {
        $customer = User::where('role', 'customer')->first();

        $routes = [
            'customer.dashboard',
            'customer.samples.index',
            'customer.samples.create',
            'customer.borrowings.index',
            'customer.borrowings.create',
            'customer.certificates.index',
            'customer.payments.index',
            'customer.satisfaction.create',
        ];

        foreach ($routes as $routeName) {
            $response = $this->actingAs($customer)->get(route($routeName));
            $response->assertStatus(200, "Failed accessing route: {$routeName}");
        }
    }

    public function test_equipment_scan_and_detail_pages(): void
    {
        $response = $this->get(route('equipment.scan'));
        $response->assertStatus(200);

        $equipment = Equipment::first();
        if ($equipment) {
            $response = $this->get(route('equipment.detail', $equipment->code));
            $response->assertStatus(200);
        }
    }

    public function test_admin_users_index_is_sorted_by_role_hierarchy(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);

        $users = $response->viewData('users');
        $this->assertNotEmpty($users);

        $roleWeights = ['admin' => 1, 'manager' => 2, 'staff' => 3, 'customer' => 4];
        $previousWeight = 0;
        foreach ($users as $u) {
            $weight = $roleWeights[$u->role] ?? 5;
            $this->assertGreaterThanOrEqual($previousWeight, $weight, "User {$u->name} with role {$u->role} is not in expected role hierarchy order.");
            $previousWeight = $weight;
        }
    }
}
