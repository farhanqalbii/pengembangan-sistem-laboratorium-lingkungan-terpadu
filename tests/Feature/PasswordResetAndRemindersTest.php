<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetAndRemindersTest extends TestCase
{
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

    public function test_laboratory_reminder_command_executes_successfully(): void
    {
        $exitCode = Artisan::call('lab:send-reminders');
        $this->assertEquals(0, $exitCode);
    }
}
