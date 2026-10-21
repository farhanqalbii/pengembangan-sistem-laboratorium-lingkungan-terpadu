<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Seed akun customer pengembang/penguji (qalbi@lab.test).
     *
     * Berjalan secara idempoten (updateOrCreate) sehingga tidak menghapus
     * data transaksi atau akun pengguna lain.
     */
    public function run(): void
    {
        $password = env('TEST_USER_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => 'qalbi@lab.test'],
            [
                'name' => 'Farhanu Qalbi',
                'password' => Hash::make($password),
                'role' => 'customer',
                'phone' => '081234567899',
                'company_name' => 'PT Hijau Lestari Indonesia',
                'address' => 'Kawasan Industri EcoPark Blok C-12, Bekasi',
            ]
        );
    }
}
