<?php

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
use App\Exceptions\InvalidVoteException;
use App\Exceptions\InvalidWorkException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Work::fromTemplate('The Title', 'poem', $this->user);
    $this->work = Work::first();
    Round::fromTemplate([
        'starts_at' => now(),
        'ends_at' => now()->addDays(1),
    ]);
});

it('creates a new work', function () {
    $this->assertDatabaseHas('works', [
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'in_progress',
    ]);
});

it('throws an error if you create a work of a given type while there is another in-progress work of that type', function () {
    expect(fn () => Work::fromTemplate('New poem', 'poem', $this->user))
        ->toThrow(InvalidWorkException::class, 'There is already an open work of this type.');
});

it('throws an error if you create a work with a name that has already been used', function () {
    expect(fn () => Work::fromTemplate('The Title', 'short_story', $this->user))
        ->toThrow(InvalidWorkException::class, 'There is already a work with this name.');
});

it('allows a user to upvote a completed work', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->upvote($this->user);

    $this->assertEquals(1, $work2->score);
});

it('allows a user to downvote a work', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->downvote($this->user);

    $this->assertEquals(-1, $work2->score);
});

it('allows a user to change their vote', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->downvote($this->user);

    $this->assertDatabaseHas('votes', [
        'user_id' => $this->user->id,
        'work_id' => 2,
        'type' => 'downvote',
    ]);

    $work2->upvote($this->user);

    $this->assertDatabaseHas('votes', [
        'user_id' => $this->user->id,
        'work_id' => 2,
        'type' => 'upvote',
    ]);

    $this->assertEquals(1, $work2->score);
});

it('throws an error if user upvotes or downvotes a submission twice in a row', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->downvote($this->user);

    expect(fn() => $work2->downvote($this->user))
        ->toThrow(InvalidVoteException::class, "You have already downvoted this work.");

    $work2->upvote($this->user);

    expect(fn() => $work2->upvote($this->user))
        ->toThrow(InvalidVoteException::class, "You have already upvoted this work.");
});

it('deletes all votes for a work after the work is deleted', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->upvote($this->user);

    $this->assertEquals(1, $work2->score);
});

it('updates a users aggregate_score when their submission is voted for or deleted', function () {
    Work::create([
        'title' => 'The Title',
        'type' => 'poem',
        'status' => 'complete',
        'user_id' => $this->user->id,
    ]);

    $work2 = Work::find(2);

    $work2->upvote($this->user);

    $this->assertEquals(1, $this->user->fresh()->aggregate_score);

    $work2->userDelete();

    $this->assertEquals(0, $this->user->fresh()->aggregate_score);
});