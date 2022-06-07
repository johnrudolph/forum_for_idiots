<?php

namespace App\Models;

use App\Models\User;
use App\Models\Vote;
use App\Models\Work;
use App\Models\Round;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidVoteException;
use phpDocumentor\Reflection\Types\Boolean;
use App\Exceptions\InvalidSubmissionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submission extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public static function fromTemplate(Work $work, User $user, String $text)
    {
        if($work->status === 'complete') {
            throw new InvalidSubmissionException('This work is already complete.');
        }

        if(Submission::where('work_id', $work->id)
            ->where('user_id', $user->id)
            ->exists()) 
        {
            throw new InvalidSubmissionException('You can only submit to each work once per day.');
        }
        
        $newSubmission = Submission::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'status' => 'pending',
            'text' => $text,
        ]);

        $newSubmission->cleanUp();

        return $newSubmission;
    }

    public function upvote(User $user)
    {
        if(Vote::where('user_id', $user->id)
            ->where('submission_id', $this->id)
            ->where('type', 'upvote')
            ->exists()) 
        {
            throw new InvalidVoteException('You have already upvoted this submission.');
        }

        if(Vote::where('user_id', $user->id)
            ->where('submission_id', $this->id)
            ->where('type', 'downvote')
            ->exists()) 
        {
            Vote::where('user_id', $user->id)
                ->where('submission_id', $this->id)
                ->where('type', 'downvote')
                ->first()
                ->delete();
        }
        
        Vote::create([
            'user_id' => $user->id,
            'submission_id' => $this->id,
            'type' => 'upvote',
            'user_rewarded' => $this->user_id,
        ]);

        User::find($this->user_id)->calculateAggregateScore();
        
        $this->calculateScore();
    }

    public function downvote(User $user)
    {
        if(Vote::where('user_id', $user->id)
            ->where('submission_id', $this->id)
            ->where('type', 'downvote')
            ->exists()) 
        {
            throw new InvalidVoteException('You have already downvoted this submission.');
        }

        if(Vote::where('user_id', $user->id)
            ->where('submission_id', $this->id)
            ->where('type', 'upvote')
            ->exists()) 
        {
            Vote::where('user_id', $user->id)
                ->where('submission_id', $this->id)
                ->where('type', 'upvote')
                ->first()
                ->delete();
        }
        
        Vote::create([
            'user_id' => $user->id,
            'submission_id' => $this->id,
            'type' => 'downvote',
            'user_rewarded' => $this->user_id,
        ]);

        User::find($this->user_id)->calculateAggregateScore();
        
        $this->calculateScore();
    }

    public function calculateScore()
    {
        $upvotes = $this->votes()
            ->where('type', 'upvote')
            ->count();

        $downvotes = $this->votes()
            ->where('type', 'downvote')
            ->count();

        $this->update(['score' => $upvotes - $downvotes]);
    }

    public function isTopScoringSubmission()
    {
        if($this->score === Submission::where('work_id', $this->work_id)->max('score')) {
            return true;
        } else {
            return false;
        }
    }

    public function topSubmissionsAreTied()
    {
        if(Submission::where('work_id', $this->work_id)->where('score', $this->work->topScore())->count() > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function userHasTopAggregateScore()
    {        
        if($this->work->topAggregateScoreOfSubmitters() === $this->user->aggregate_score) {
            return true;
        } else {
            return false;
        }
    }

    public function grade()
    {
        if($this->isTopScoringSubmission() === false) {
            $this->update(['status' => 'rejected']);
        } 
        elseif($this->topSubmissionsAreTied() === false) {
            $this->update(['status' => 'accepted']);
        }
        elseif(Submission::where('work_id', $this->work_id)->where('status', 'accepted')->exists()) {
            $this->update(['status' => 'rejected']);
        }
        else {
            $this->update(['status' => 'accepted']);
        }
    }

    public function punctuate()
    {
        if(Str::endsWith($this->text, ['...', '.', '!', '?'])) {
            $this->update(['text' => $this->text.' ']);
        }
        elseif(Str::endsWith($this->text, ['... ', '. ', '! ', '? '])) {
        
        }
        else
        {
            $this->update(['text' => $this->text.'. ']);
        }
    }

    public function cleanUp()
    {
        $this->update(['text' => Str::squish($this->text)]);
        
        if($this->work->type === 'short_story')
        {
            $this->punctuate();
        }
        
        $this->censor();
    }

    public function censor()
    {
        $dirty_words = [
            'fuck', 'shit', 'bitch', 'asshole', 'dick', 'titties', 'cock', 'pussy', 'nigger', 'anal', 'anus', 'arse', 'ballsack',
            'biatch', 'blowjob', 'blow job', 'boner', 'boob', 'buttplug', 'clitoris','coon','cunt','dildo','dyke','fag','faggot',
            'feck','fellate','fellatio','f u c k','n i g g e r','fudgepacker','fudge packer','Goddamn','God damn','homo','jizz',
            'labia','muff','nigga','penis','prick','pube','scrotum','slut','tit','twat','vagina','wank','whore','wtf',
        ];

        foreach($dirty_words as $dirty_word)
        { 
            if(stripos($this->text, $dirty_word) > -1)
            {
                $this->update(['status' => 'censored']);
                $this->delete();
            }
        }
    }

    public function userDelete()
    {
        $submitter = User::find($this->user_id);
        
        $this->delete();

        foreach(Vote::where('submission_id', $this->id)->get() as $vote)
        {
            $vote->delete();
        }

        $submitter->calculateAggregateScore();
    }
}
