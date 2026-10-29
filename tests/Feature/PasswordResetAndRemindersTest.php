<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetAndRemindersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_forgot_password_page_loads_successfully(): void
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('Pemulihan Kata Sandi');
    }

    public function test_customer_can_request_password_reset_link(): void
    {
        $user = User::where('role', 'customer')->first();

        $response = $this->post(route('password.email'), [
            'email_or_phone' => $user->email,
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_customer_can_reset_password_with_valid_token(): void
    {
        $user = User::where('role', 'customer')->first();
        $token = 'test-verification-token-123456';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));
        $response->assertStatus(200);
        $response->assertSee('Buat Kata Sandi Baru');

        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('NewPassword123!', $user->password));

        // Token should be removed after successful reset
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);

        // Restore original password for ongoing test suite consistency
        $user->password = Hash::make('password');
        $user->save();
    }

    public function test_reset_password_page_rejects_expired_token(): void
    {
        $user = User::where('role', 'customer')->first();
        $token = 'expired-token-'.uniqid();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()->subMinutes(61), // Expired past 60 minutes
            ]
        );

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error');
    }

    public function test_reset_password_page_rejects_invalid_token(): void
    {
        $user = User::where('role', 'customer')->first();
        $token = 'correct-token-'.uniqid();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $response = $this->get(route('password.reset', ['token' => 'completely-wrong-token', 'email' => $user->email]));

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error');
    }

    public function test_reset_password_page_rejects_used_or_missing_token(): void
    {
        $user = User::where('role', 'customer')->first();

        DB::table('password_reset_tokens')->where('email', $user->email)->delete();

        $response = $this->get(route('password.reset', ['token' => 'some-token', 'email' => $user->email]));

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error');
    }

    public function test_post_reset_with_expired_token_does_not_change_password(): void
    {
        $user = User::where('role', 'customer')->first();
        $oldPasswordHash = $user->password;
        $token = 'expired-token-'.uniqid();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now()->subMinutes(75),
            ]
        );

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals($oldPasswordHash, $user->fresh()->password);
    }

    public function test_reset_token_cannot_be_reused_after_successful_reset(): void
    {
        $user = User::where('role', 'customer')->first();
        $token = 'single-use-token-'.uniqid();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // First attempt succeeds
        $firstResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPasswordOnce123!',
            'password_confirmation' => 'NewPasswordOnce123!',
        ]);
        $firstResponse->assertRedirect(route('login'));

        // Second attempt with same token must fail
        $secondResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'AnotherPassword123!',
            'password_confirmation' => 'AnotherPassword123!',
        ]);
        $secondResponse->assertSessionHasErrors('email');

        // Reset user password back
        $user->password = Hash::make('password');
        $user->save();
    }

    public function test_opening_expired_link_does_not_delete_newer_valid_token(): void
    {
        $user = User::where('role', 'customer')->first();
        $newerToken = 'newer-valid-token-'.uniqid();

        // Suppose user requested a new token just now
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($newerToken),
                'created_at' => now(),
            ]
        );

        // User mistakenly opens an old expired link with old token
        $oldToken = 'old-expired-token-123';
        $response = $this->get(route('password.reset', ['token' => $oldToken, 'email' => $user->email]));
        $response->assertRedirect(route('password.request'));

        // The newer valid token in database must NOT be wiped out!
        $currentTokenRecord = DB::table('password_reset_tokens')->where('email', $user->email)->first();
        $this->assertNotNull($currentTokenRecord);
        $this->assertTrue(Hash::check($newerToken, $currentTokenRecord->token));
    }

    public function test_laboratory_reminder_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('lab:send-reminders');
        $this->assertEquals(0, $exitCode);
    }
}
