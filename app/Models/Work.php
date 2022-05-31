<?php

namespace App\Models;

use App\Models\User;
use App\Models\Vote;
use App\Models\Submission;
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
            Work::create([
                'title' => $title,
                'type' => $type,
                'status' => 'in_progress',
                'user_id' => $user->id,
            ]);
        }
        else
        {
            Work::create([
                'title' => $title,
                'type' => $type,
                'status' => 'queued',
                'user_id' => $user->id,
            ]);
        }
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

            $this->increment('score');
        }
        
        Vote::create([
            'user_id' => $user->id,
            'work_id' => $this->id,
            'round_id' => Round::currentRound()->id,
            'type' => 'upvote',
        ]);

        $this->increment('score');
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

            $this->decrement('score');
        }
        
        Vote::create([
            'user_id' => $user->id,
            'work_id' => $this->id,
            'round_id' => Round::currentRound()->id,
            'type' => 'downvote',
        ]);

        $this->decrement('score');
    }
}
