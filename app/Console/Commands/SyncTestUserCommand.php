<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncTestUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-test-user 
                            {--email=qalbi@lab.test : Email akun penguji}
                            {--password= : Kata sandi akun (default: password atau nilai TEST_USER_PASSWORD)}
                            {--name=Farhanu Qalbi : Nama lengkap}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat atau memperbarui akun penguji (qalbi@lab.test) secara aman tanpa mereset data lain.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->option('email');
        $name = $this->option('name');
        $password = $this->option('password') ?: env('TEST_USER_PASSWORD', 'password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'customer',
                'phone' => '081234567899',
                'company_name' => 'PT Hijau Lestari Indonesia',
                'address' => 'Kawasan Industri EcoPark Blok C-12, Bekasi',
            ]
        );

        $this->info("Akun penguji [{$user->email}] berhasil disinkronkan.");
        $this->line("Nama: {$user->name}");
        $this->line("Role: {$user->role}");
        $this->line('Password telah di-hash dengan aman menggunakan Laravel Bcrypt.');

        return self::SUCCESS;
    }
}
