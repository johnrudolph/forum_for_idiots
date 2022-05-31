<?php

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
use App\Models\Submission;
use Spatie\TestTime\TestTime;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->user1 = User::factory()->create();
    $this->user2 = User::factory()->create();
    $this->user3 = User::factory()->create();
    $this->user4 = User::factory()->create();
    Work::fromTemplate('The Title', 'poem', $this->user1);
    $this->work = Work::first();
    TestTime::freeze(Carbon::parse('2021-12-03 10:00'));
    Round::fromTemplate();
    $this->round = Round::first();

    Work::create([
        'title' => 'The first word',
        'type' => 'word_of_the_day',
        'user_id' => $this->user1->id,
        'status' => 'in_progress',
    ]);

    Work::create([
        'title' => 'A new word',
        'type' => 'word_of_the_day',
        'user_id' => $this->user1->id,
        'status' => 'queued',
    ]);

    Work::create([
        'title' => 'The first question',
        'type' => 'advice',
        'user_id' => $this->user1->id,
        'status' => 'in_progress',
    ]);

    Work::create([
        'title' => 'A new question',
        'type' => 'advice',
        'user_id' => $this->user1->id,
        'status' => 'queued',
    ]);
});

it('can create a round lasting 24 hours', function () {
    $this->assertEquals($this->round->starts_at, Carbon::parse('2021-12-03 10:00'));
    $this->assertEquals($this->round->ends_at, Carbon::parse('2021-12-04 10:00'));
}); 

it('starts a new round when a round ends', function () {
    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals(Round::currentRound()->starts_at, Carbon::parse('2021-12-04 10:00'));
    $this->assertEquals(Round::currentRound()->ends_at, Carbon::parse('2021-12-05 10:00'));

    $this->assertEquals(Round::count(), 2);
}); 

it('appends the top scoring submission and marks all other submissions for that work as rejected', function () {
    Submission::fromTemplate($this->work, $this->user1, 'First text.');
    $submission1 = Submission::first();
    Submission::fromTemplate($this->work, $this->user2, 'Second text.');
    $submission2 = Submission::where('id','!=', $submission1->id)->first();

    $submission1->upvote($this->user1);
    $submission1->upvote($this->user2);
    $submission1->upvote($this->user3);
    $submission2->upvote($this->user2);
    $submission2->upvote($this->user3);
    $submission2->upvote($this->user4);
    $submission2->downvote($this->user1);

    $this->assertEquals($submission1->score, 3);
    $this->assertEquals($submission2->score, 2);

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals($this->work->fresh()->completeText(), 'First text.');
}); 

it('uses user aggregate_score as a tiebreaker if two submissions are tied', function () {
    $this->user1->update(['aggregate_score' => 5]);
    
    Submission::fromTemplate($this->work, $this->user2, 'First text.');
    $submission1 = Submission::first();
    Submission::fromTemplate($this->work, $this->user1, 'Second text.');
    $submission2 = Submission::where('id','!=', $submission1->id)->first();

    $submission1->upvote($this->user1);
    $submission1->upvote($this->user2);
    $submission2->upvote($this->user2);
    $submission2->upvote($this->user3);
    $submission2->upvote($this->user4);
    $submission2->downvote($this->user1);

    $this->assertEquals($submission1->score, 2);
    $this->assertEquals($submission2->score, 2);

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals($this->work->fresh()->completeText(), 'Second text.');
}); 

it('selects a random top-scoring submission if tied submissions also have tied aggregate_scores', function () {
    Submission::fromTemplate($this->work, $this->user2, 'First text.');
    $submission1 = Submission::first();
    Submission::fromTemplate($this->work, $this->user1, 'Second text.');
    $submission2 = Submission::where('id','!=', $submission1->id)->first();

    $submission1->upvote($this->user1);
    $submission1->upvote($this->user2);
    $submission2->upvote($this->user2);
    $submission2->upvote($this->user3);
    $submission2->upvote($this->user4);
    $submission2->downvote($this->user1);

    $this->assertEquals($submission1->score, 2);
    $this->assertEquals($submission2->score, 2);

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals($this->round->submissions()->where('status', 'accepted')->count(), 1);
}); 

it('sets up a new word of the day when the round ends', function () {
    $original_word = Work::find(2);
    $next_word = Work::find(3);

    Submission::fromTemplate($original_word, $this->user1, 'A definition');

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals(Work::find(2)->fresh()->status, 'complete');
    $this->assertEquals(Work::find(3)->fresh()->status, 'in_progress');
});

it('sets up a new question/advice of the day when the round ends', function () {
    $original_question = Work::where('type', 'advice')->first();

    Submission::fromTemplate($original_question, $this->user1, 'Advice');

    TestTime::addDay();
    $this->round->endRound();
    TestTime::addMinute();

    $this->assertEquals(Work::find(4)->fresh()->status, 'complete');
    $this->assertEquals(Work::find(5)->fresh()->status, 'in_progress');
});