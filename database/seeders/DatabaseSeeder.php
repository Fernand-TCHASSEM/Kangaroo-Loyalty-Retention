<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Idempotence guard: if the demo data already exists, do nothing.
        // This lets the entrypoint run --seed on every boot safely, without
        // duplicating data or wiping changes made during a demo.
        if (Customer::query()->exists()) {
            return;
        }

        User::firstOrCreate(
            ['email' => 'demo@kangaroo.test'],
            [
                'name' => 'Demo Merchant',
                'password' => Hash::make('password'),
            ]
        );

        $this->call(LoyaltyDemoSeeder::class);
    }
}
