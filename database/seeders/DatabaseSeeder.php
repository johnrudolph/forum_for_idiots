<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
use App\Models\Submission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'johnrudolph',
            'email' => 'john@email.gov',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'coulbourne',
            'email' => 'd@coulb.com',
            'password' => bcrypt('password'),
        ]);

        Round::create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(1),
        ]);
    }
}
