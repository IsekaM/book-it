<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()
            ->member()
            ->createOne(["email" => "member@example.com"]);

        User::factory()
            ->admin()
            ->createOne(["email" => "admin@example.com"]);
    }
}
