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
            ->where('round_id', Round::currentRound()->id)
            ->exists()) 
        {
            throw new InvalidSubmissionException('You can only submit to each work once per day.');
        }
        
        $newSubmission = Submission::create([
            'user_id' => $user->id,
            'work_id' => $work->id,
            'round_id' => Round::currentRound()->id,
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

            $this->decrement('downvotes');
            $this->increment('score');
            $this->user->increment('aggregate_score');
        }
        
        Vote::create([
            'user_id' => $user->id,
            'submission_id' => $this->id,
            'round_id' => Round::currentRound()->id,
            'type' => 'upvote',
        ]);
        
        $this->increment('upvotes');
        $this->increment('score');
        $this->user->increment('aggregate_score');
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

            $this->decrement('upvotes');
            $this->decrement('score');
            $this->user->decrement('aggregate_score');
        }
        
        Vote::create([
            'user_id' => $user->id,
            'submission_id' => $this->id,
            'round_id' => Round::currentRound()->id,
            'type' => 'downvote',
        ]);
        
        $this->increment('downvotes');
        $this->decrement('score');
        $this->user->decrement('aggregate_score');
    }

    public function grade()
    {
        $top_score = Submission::where('work_id', $this->work_id)->max('score');
        
        $best_submissions = DB::table('users')
            ->join('submissions', 'users.id', '=', 'submissions.user_id')
            ->select(
                'submissions.id as submission_id',
                'submissions.work_id as work_id',
                'submissions.round_id as round_id',
                'users.id as user_id',
                'users.aggregate_score as aggregate_score',
            )
            ->where('submissions.work_id', $this->work_id)
            ->where('submissions.round_id', $this->round_id)
            ->get();

        $best_aggregate_score = $best_submissions->max('aggregate_score');
        
        if($this->score != $top_score) 
        {
            $this->update(['status' => 'rejected']);
        }
        elseif($best_submissions->where('aggregate_score', $best_aggregate_score)->count() > 1)
        {
            // this has the top score AND another user is tied for top aggregate score
            // logic for handling ties is in round->endround(), which is dumb
            $this->update(['status' => 'tied']);
        }
        else
        {
            if($this->user->aggregate_score === $best_submissions->max('aggregate_score'))
            {
                // this has the top score AND the user has the top aggregate score
                $this->update(['status' => 'accepted']);
            }
            else
            {
                // this has the top score AND the user does not have the top aggregate score
                $this->update(['status' => 'rejected']);
            }
        }
    }

    public function punctuate()
    {
        // dd($this->text);   
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
}
