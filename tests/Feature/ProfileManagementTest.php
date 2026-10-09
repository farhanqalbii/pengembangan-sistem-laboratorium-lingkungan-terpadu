<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $user = User::first();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee('Profil &amp; Pengaturan Akun', false);
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::where('role', 'customer')->first();

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Farhanu Qalbi Updated',
            'email' => $user->email,
            'phone' => '08999888777',
            'company_name' => 'PT Lab Lingkungan Sejahtera',
            'address' => 'Jl. Hijau Lestari No. 12',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Farhanu Qalbi Updated',
            'phone' => '08999888777',
            'company_name' => 'PT Lab Lingkungan Sejahtera',
        ]);

        // Revert name back to Farhanu Qalbi for consistency
        $user->update(['name' => 'Farhanu Qalbi']);
    }

    public function test_user_cannot_take_another_users_email(): void
    {
        $user1 = User::first();
        $user2 = User::skip(1)->first();

        $response = $this->actingAs($user1)->put(route('profile.update'), [
            'name' => $user1->name,
            'email' => $user2->email,
            'phone' => '081234567890',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $user = User::where('role', 'customer')->first();
        $user->update(['password' => Hash::make('CurrentSecret123')]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'CurrentSecret123',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewSecretPassword123!', $user->fresh()->password));

        // Restore password back to farhanjos23
        $user->update(['password' => Hash::make('farhanjos23')]);
    }

    public function test_user_cannot_change_password_with_wrong_current_password(): void
    {
        $user = User::where('role', 'customer')->first();
        $user->update(['password' => Hash::make('farhanjos23')]);

        $response = $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'wrong-current-password',
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_user_can_upload_and_update_avatar_photo(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'customer')->first();
        $file = UploadedFile::fake()->image('profile_pic.jpg', 200, 200);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'avatar' => $file,
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
        $this->assertNotNull($user->avatar_url);

        // Clean up
        $user->update(['avatar' => null]);
    }

    public function test_user_can_remove_avatar_photo(): void
    {
        Storage::fake('public');

        $user = User::where('role', 'customer')->first();
        $path = UploadedFile::fake()->image('old_pic.jpg')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '081234567890',
            'remove_avatar' => '1',
        ]);

        $response->assertSessionHas('success');
        $user->refresh();
        $this->assertNull($user->avatar);
        $this->assertFalse(Storage::disk('public')->exists($path));
        $this->assertNull($user->avatar_url);
    }
}
