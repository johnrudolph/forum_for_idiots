<?php

namespace App\Models;

use App\Models\User;
use App\Models\Vote;
use App\Models\Submission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidVoteException;
use App\Exceptions\InvalidWorkException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Work extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        $this->hasMany(Vote::class);
    }

    public static function fromTemplate(String $title, String $type, User $user)
    {
        if(Work::where('type', $type)
            ->where('status', 'in_progress')
            ->where(function ($query) {
                $query->where('type', 'poem')
                    ->orWhere('type', 'short_story');
            })
            ->exists())
        {
            throw new InvalidWorkException('There is already an open work of this type.');
        }

        if(Work::where('title', $title)
            ->exists()) 
        {
            throw new InvalidWorkException('There is already a work with this name.');
        }

        if($type === 'poem' || $type === 'short_story')
        {
            $new_work = Work::create([
                'title' => $title,
                'type' => $type,
                'status' => 'in_progress',
                'user_id' => $user->id,
            ]);
        }
        else
        {
            $new_work = Work::create([
                'title' => $title,
                'type' => $type,
                'status' => 'queued',
                'user_id' => $user->id,
            ]);
        }

        $new_work->cleanUp();
    }

    public function complete()
    {
        $this->update(['status' => 'complete']);
    }

    public function completeText()
    {
        $text = '';
        $submissionsToAppend = Submission::where('work_id', $this->id)
            ->where('status', 'accepted')
            ->orderBy('created_at', 'desc')
            ->get();

        foreach($submissionsToAppend as $submission) {
            $text .= $submission->text;
        }

        return $text;
    }

    public function prompt()
    {
        if($this->type === 'short_story')
        {
            $prompts = collect([
                'Pen your opus',
                'Give us that payoff we have been waiting for',
                'Write the twist no one anticipated',
                'Channel your inner Stephen King',
            ]);
        }
        elseif($this->type === 'poem')
        {
            $prompts = collect([
                'Speak your truth',
                'Channel your inner Maya Angelou',
                'Write from the heart'
            ]);
        }
        elseif($this->type === 'word_of_the_day')
        {
            $prompts = collect([
                "Make something up from that time you 'went' to 'grad school'",
                "Facts don't matter. Just make something up",
                "If you say it with confidence, it's true",
            ]);
        }
        elseif($this->type === 'advice')
        {
            $prompts = collect([
                'Help a brother out',
                "You're the expert",
                "Be the change you want to see in the world"
            ]);
        }
        
        return $prompts->random();
    }

    public function upvote(User $user)
    {
        if(Vote::where('user_id', $user->id)
            ->where('work_id', $this->id)
            ->where('type', 'upvote')
            ->exists()) 
        {
            throw new InvalidVoteException('You have already upvoted this work.');
        }

        if(Vote::where('user_id', $user->id)
            ->where('work_id', $this->id)
            ->where('type', 'downvote')
            ->exists()) 
        {
            Vote::where('user_id', $user->id)
                ->where('work_id', $this->id)
                ->where('type', 'downvote')
                ->first()
                ->delete();
        }

        Vote::create([
            'user_id' => $user->id,
            'work_id' => $this->id,
            'type' => 'upvote',
            'user_rewarded' => $this->user_id,
        ]);

        User::find($this->user_id)->calculateAggregatescore();

        $this->calculateScore();
    }

    public function downvote(User $user)
    {
        if(Vote::where('user_id', $user->id)
            ->where('work_id', $this->id)
            ->where('type', 'downvote')
            ->exists()) 
        {
            throw new InvalidVoteException('You have already downvoted this work.');
        }

        if(Vote::where('user_id', $user->id)
            ->where('work_id', $this->id)
            ->where('type', 'upvote')
            ->exists()) 
        {
            Vote::where('user_id', $user->id)
                ->where('work_id', $this->id)
                ->where('type', 'upvote')
                ->first()
                ->delete();
        }
        
        Vote::create([
            'user_id' => $user->id,
            'work_id' => $this->id,
            'type' => 'downvote',
            'user_rewarded' => $this->user_id,
        ]);

        User::find($this->user_id)->calculateAggregatescore();

        $this->calculateScore();
    }

    public function cleanUp()
    {
        $this->update(['title' => Str::squish($this->title)]);
        
        $this->censor();
    }

    public function usersWhoSubmittedToThisWork()
    {
        return User::where('id', $this->submissions()->pluck('user_id'))
            ->get();

        dump(User::where('id', $this->submissions()->pluck('user_id'))
        ->get());
    }

    public function topScore()
    {
        return $this->submissions()
            ->get()
            ->max('score');
    }

    public function topAggregateScoreOfSubmitters()
    {
        return $this->usersWhoSubmittedToThisWork()->max('aggregate_score');
    }

    public function censor()
    {
        $dirty_words = [
            ' fuck ', ' shit ', ' bitch ', ' asshole ', ' dick ', ' titties ', ' cock ', ' pussy ', ' nigger ', ' anal ', ' anus ', 
            ' arse ', ' ballsack ', ' biatch ', ' blowjob ', ' blow job ', ' blowjob ', ' boner ', ' boob ', ' buttplug ', ' clitoris '
            ,' coon ',' cunt ', ' dildo ', ' dyke ', ' fag ', ' faggot ', ' feck ',' fellate ',' fellatio ', ' f u c k ', ' n i g g e r ',
            ' fudgepacker ',' fudge packer ',' Goddamn ',' God damn ',' homo ',' jizz ', ' labia ',' muff ',' nigga ',' penis ',' prick ',
            ' pube ',' scrotum ',' slut ',' tit ',' twat ',' vagina ',' wank ',' whore ',' wtf ',
        ];

        foreach($dirty_words as $dirty_word)
        { 
            if(stripos($this->title, $dirty_word) > -1)
            {
                $this->update(['status' => 'censored']);
                $this->delete();
            }
        }
    }

    public function calculateScore()
    {
        $upvotes = Vote::where('work_id', $this->id)
            ->where('type', 'upvote')
            ->count();

        $downvotes = Vote::where('work_id', $this->id)
            ->where('type', 'downvote')
            ->count();

        $this->update(['score' => $upvotes - $downvotes]);
    }

    public function userDelete()
    {
        $submitter = User::find($this->user_id);
        
        $this->delete();

        foreach(Vote::where('work_id', $this->id)->get() as $vote)
        {
            $vote->delete();
        }

        $submitter->calculateAggregateScore();
    }
}
