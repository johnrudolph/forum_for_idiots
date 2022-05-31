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
        
        Work::create([
            'title' => 'The Poem',
            'status' => 'in_progress',
            'type' => 'poem',
        ]);

        Work::create([
            'title' => 'A short story',
            'status' => 'in_progress',
            'type' => 'short_story',
        ]);

        Work::create([
            'title' => 'How do I screw in a lightbulb if all I have is a potato?',
            'status' => 'complete',
            'type' => 'advice',
            'round_id' => 1,
            'user_id' => 1,
        ]);

        Work::create([
            'title' => 'Obstibation',
            'status' => 'complete',
            'type' => 'word_of_the_day',
            'round_id' => 1,
            'user_id' => 1,
        ]);

        Work::create([
            'title' => 'What is the meaning of life?',
            'status' => 'in_progress',
            'type' => 'advice',
            'round_id' => 2,
            'user_id' => 1,
        ]);

        Work::create([
            'title' => 'Coulporeur',
            'status' => 'in_progress',
            'type' => 'word_of_the_day',
            'round_id' => 2,
            'user_id' => 1,
        ]);

        Round::create([
            'starts_at' => now()->addDays(-1),
            'ends_at' => now(),
        ]);

        Round::create([
            'starts_at' => now(),
            'ends_at' => now()->addDays(1),
        ]);

        Submission::create([
            'round_id' => 1,
            'work_id' => 3,
            'text' => 'Try harder.',
            'status' => 'accepted',
            'user_id' => 1,
        ]);

        Submission::create([
            'round_id' => 1,
            'work_id' => 4,
            'text' => 'Severe constipation',
            'status' => 'accepted',
            'user_id' => 1,
        ]);
    }
}
