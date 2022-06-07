<?php

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
use Spatie\TestTime\TestTime;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('can set up initial word and advice when there are zero submissions', function() {
    $this->user1 = User::factory()->create();
    Round::fromTemplate();
    $this->round = Round::first();

    Work::fromTemplate('First Word', 'word_of_the_day', $this->user1);
    Work::fromTemplate('Second Word', 'word_of_the_day', $this->user1);
    Work::fromTemplate('First Question', 'advice', $this->user1);
    Work::fromTemplate('Second Question', 'advice', $this->user1);

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals(1, Work::where('status', 'in_progress')->where('type', 'advice')->count());
    $this->assertEquals(1, Work::where('status', 'in_progress')->where('type', 'word_of_the_day')->count());
});