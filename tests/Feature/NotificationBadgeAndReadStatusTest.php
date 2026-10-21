<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotificationBadgeAndReadStatusTest extends TestCase
{
    use DatabaseTransactions;

    protected User $customer;

    protected User $staff;

    protected User $manager;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::where('role', 'customer')->firstOrFail();
        $this->staff = User::where('role', 'staff')->firstOrFail();
        $this->manager = User::where('role', 'manager')->firstOrFail();
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    public function test_clicking_single_notification_marks_only_that_notification_as_read_and_decrements_count(): void
    {
        // Setup 3 unread notifications for customer
        $notifPayment = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Pembayaran Sampel Terkonfirmasi',
            'message' => 'Pembayaran tagihan sampel SMP-001 berhasil diverifikasi.',
            'link' => '/customer/samples/1',
            'read_at' => null,
        ]);

        $notifSample = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Sampel Sedang Diuji',
            'message' => 'Sampel Anda sedang dianalisis di laboratorium.',
            'link' => '/customer/samples/1',
            'read_at' => null,
        ]);

        $notifBorrow = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Peminjaman Alat Disetujui',
            'message' => 'Pengajuan pinjam alat Anda telah disetujui.',
            'link' => '/customer/borrowings/1',
            'read_at' => null,
        ]);

        $initialCounts = Notification::getUnreadCountsFor($this->customer->id);
        $this->assertGreaterThanOrEqual(3, $initialCounts['total']);
        $this->assertGreaterThanOrEqual(1, $initialCounts['payment']);

        // Mark only the payment notification as read via JSON PATCH
        $response = $this->actingAs($this->customer)->patchJson(route('notifications.read', $notifPayment->id));
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'link' => '/customer/samples/1',
            ]);

        // Verify in DB that ONLY notifPayment was marked read
        $this->assertNotNull($notifPayment->fresh()->read_at);
        $this->assertNull($notifSample->fresh()->read_at);
        $this->assertNull($notifBorrow->fresh()->read_at);

        // Verify updated counts returned in response
        $counts = $response->json('counts');
        $this->assertEquals($initialCounts['total'] - 1, $counts['total']);
        $this->assertEquals($initialCounts['payment'] - 1, $counts['payment']);
    }

    public function test_mark_all_read_marks_all_notifications_as_read_without_deleting_them(): void
    {
        // Create 2 unread notifications for staff
        $n1 = Notification::create([
            'user_id' => $this->staff->id,
            'title' => 'Sampel Baru Perlu Diuji',
            'message' => 'Sampel SMP-999 telah diserahkan ke meja analis.',
            'link' => '/staff/samples/999',
            'read_at' => null,
        ]);

        $n2 = Notification::create([
            'user_id' => $this->staff->id,
            'title' => 'Peringatan Stok Alat',
            'message' => 'Stok alat pH meter mendekati batas minimum.',
            'link' => '/staff/equipment/1',
            'read_at' => null,
        ]);

        // Request mark all read
        $response = $this->actingAs($this->staff)->postJson(route('notifications.read-all'));
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'counts' => [
                    'total' => 0,
                    'sample' => 0,
                    'borrowing' => 0,
                    'coa' => 0,
                    'payment' => 0,
                    'equipment' => 0,
                    'verification' => 0,
                    'finance' => 0,
                ],
            ]);

        // Verify DB: read_at is updated, NOT deleted!
        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
        $this->assertDatabaseHas('notifications', ['id' => $n1->id]);
        $this->assertDatabaseHas('notifications', ['id' => $n2->id]);
    }

    public function test_user_cannot_mark_other_users_notification_as_read(): void
    {
        // Notification belongs to Manager
        $notifManager = Notification::create([
            'user_id' => $this->manager->id,
            'title' => 'Verifikasi Hasil Uji',
            'message' => 'Hasil uji sampel menunggu persetujuan teknis manager.',
            'link' => '/manager/verifications/1',
            'read_at' => null,
        ]);

        // Customer attempts to mark it as read
        $response = $this->actingAs($this->customer)->patch(route('notifications.read', $notifManager->id));
        $response->assertForbidden();

        // Customer attempts via JSON
        $jsonResponse = $this->actingAs($this->customer)->patchJson(route('notifications.read', $notifManager->id));
        $jsonResponse->assertForbidden();

        // Database value must still be NULL
        $this->assertNull($notifManager->fresh()->read_at);
    }

    public function test_notifications_isolation_across_all_roles(): void
    {
        // Clean baseline or create specific notifications per role
        $cNotif = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Notif Customer',
            'message' => 'Pesanan sampel diproses.',
            'link' => '/customer/samples/1',
            'read_at' => null,
        ]);

        $sNotif = Notification::create([
            'user_id' => $this->staff->id,
            'title' => 'Notif Staff',
            'message' => 'Alat perlu kalibrasi.',
            'link' => '/staff/equipment/1',
            'read_at' => null,
        ]);

        $mNotif = Notification::create([
            'user_id' => $this->manager->id,
            'title' => 'Notif Manager',
            'message' => 'Verifikasi sertifikat COA.',
            'link' => '/manager/verifications/1',
            'read_at' => null,
        ]);

        $aNotif = Notification::create([
            'user_id' => $this->admin->id,
            'title' => 'Notif Admin',
            'message' => 'Pengajuan refund pembayaran.',
            'link' => '/admin/finance',
            'read_at' => null,
        ]);

        // Customer marks all as read
        $this->actingAs($this->customer)->postJson(route('notifications.read-all'))->assertOk();

        // Customer notification is read
        $this->assertNotNull($cNotif->fresh()->read_at);

        // Staff, Manager, Admin notifications MUST NOT be affected!
        $this->assertNull($sNotif->fresh()->read_at);
        $this->assertNull($mNotif->fresh()->read_at);
        $this->assertNull($aNotif->fresh()->read_at);
    }

    public function test_sidebar_and_bell_badge_render_consistency_after_reading(): void
    {
        // 1. Customer has unread payment notification
        $notif = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Pembayaran Sampel Terkonfirmasi',
            'message' => 'Pembayaran tagihan sampel berhasil.',
            'link' => '/customer/samples/100',
            'read_at' => null,
        ]);

        // Visit customer dashboard
        $res = $this->actingAs($this->customer)->get(route('customer.dashboard'));
        $res->assertOk();
        // Bell and Payment badge are rendered
        $res->assertSee('Invoice & Pembayaran', false);

        // Mark it as read
        $this->actingAs($this->customer)->patch(route('notifications.read', $notif->id));

        // Re-visit dashboard (simulate refresh or navigation)
        $resAfter = $this->actingAs($this->customer)->get(route('customer.dashboard'));
        $resAfter->assertOk();

        // Notification is read, counts should reflect 0 for this notification
        $this->assertNotNull($notif->fresh()->read_at);
    }

    public function test_read_notifications_remain_accessible_in_notification_center(): void
    {
        $notif = Notification::create([
            'user_id' => $this->customer->id,
            'title' => 'Arsip Notifikasi Pengujian',
            'message' => 'Notifikasi lama yang telah dibaca.',
            'link' => '/customer/samples/5',
            'read_at' => now()->subDay(),
        ]);

        $res = $this->actingAs($this->customer)->get(route('notifications.index'));
        $res->assertOk();
        $res->assertSee('Arsip Notifikasi Pengujian');
        $res->assertSee('Sudah dibaca');
    }
}
