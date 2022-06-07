<?php

use App\Models\User;
use App\Models\Vote;
use App\Models\Work;
use App\Models\Round;
use App\Models\Submission;
use App\Exceptions\InvalidVoteException;
use App\Exceptions\InvalidSubmissionException;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    Work::fromTemplate('The Poem', 'poem', $this->user);
    $this->poem = Work::first();
    Work::fromTemplate('The Story', 'short_story', $this->user);
    $this->story = Work::where('type', 'short_story')->first();
    Round::fromTemplate();
});

it('allows a user to make a new submission', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');

    $this->assertDatabaseHas('submissions', [
        'user_id' => $this->user->id,
        'text' => 'Hello, world!',
    ]);
});

it('allows a user to upvote a submission', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->upvote($this->user, $submission);

    $this->assertEquals(1, $submission->score);
});

it('allows a user to downvote a submission', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->downvote($this->user, $submission);

    $this->assertEquals(-1, $submission->score);
});

it('allows a user to change their vote', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->upvote($this->user, $submission);
    $submission->downvote($this->user, $submission);

    $this->assertEquals(-1, $submission->score);
    $this->assertEquals(1, $submission->votes()->where('type', 'downvote')->count());
    $this->assertEquals(0, $submission->votes()->where('type', 'upvote')->count());

    $submission->upvote($this->user, $submission);

    $this->assertEquals(1, $submission->score);
    $this->assertEquals(0, $submission->votes()->where('type', 'downvote')->count());
    $this->assertEquals(1, $submission->votes()->where('type', 'upvote')->count());
});

it('throws an error if user upvotes or downvotes a submission twice in a row', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->upvote($this->user, $submission);

    expect(fn() => $submission->upvote($this->user, $submission))
        ->toThrow(InvalidVoteException::class, "You have already upvoted this submission.");

    $submission->downvote($this->user, $submission);

    expect(fn() => $submission->downvote($this->user, $submission))
        ->toThrow(InvalidVoteException::class, "You have already downvoted this submission.");
});

it('throws an error if user submits to a complete work', function () {
    $this->poem->update(['status' => 'complete']);

    expect(fn() => Submission::fromTemplate($this->poem->fresh(), $this->user, 'Hello, world!'))
        ->toThrow(InvalidSubmissionException::class, "This work is already complete.");
});

it('throws an error if user submits to the same work on the same round', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');

    expect(fn() => Submission::fromTemplate($this->poem, $this->user, 'Hello, world!'))
        ->toThrow(InvalidSubmissionException::class, "You can only submit to each work once per day.");
});

it('censors bad language by deleting the submission', function () {
    $submission = Submission::fromTemplate($this->poem, $this->user, 'can you believe this fucking idiot???');

    $this->assertEquals('censored', $submission->fresh()->status);
})->skip();

it('punctuates short story submissions, but not poetry submissions', function () {
    $submission = Submission::fromTemplate($this->poem, $this->user, 'a man walked into a bar');
    $this->assertEquals('a man walked into a bar', $submission->fresh()->text);

    $submission2 = Submission::fromTemplate($this->story, $this->user, 'a man walked into a bar');
    $this->assertEquals('a man walked into a bar. ', $submission2->fresh()->text);
});

it('removes white space from submissions', function () {
    $submission = Submission::fromTemplate($this->poem, $this->user, '    a man   walked      into a bar     ');
    $this->assertEquals('a man walked into a bar', $submission->fresh()->text);
});

it('deletes all votes for a submission after the submission is deleted', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->upvote($this->user, $submission);

    $this->assertEquals(1, $submission->votes()->count());

    $submission->userDelete();

    $this->assertEquals(0, $submission->votes()->count());
});

it('updates a users aggregate_score when their submission is voted for or deleted', function () {
    Submission::fromTemplate($this->poem, $this->user, 'Hello, world!');
    $submission = Submission::first();

    $submission->upvote($this->user, $submission);

    $this->assertEquals(1, $this->user->fresh()->aggregate_score);

    $submission->userDelete();

    $this->assertEquals(0, $submission->votes()->count());
    $this->assertEquals(0, $this->user->fresh()->aggregate_score);
});