<?php

namespace App\Models;

use App\Models\Vote;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Round extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public static function currentRound()
    {
        return Round::where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();
    }

    public static function yesterday()
    {
        return Round::where('id', Round::currentRound()->id - 1)
            ->first();
    }

    public static function fromTemplate()
    {
        Round::create([
            'starts_at' => now(),
            'ends_at' => now()->addHours(24),
        ]);
    }

    public function endRound()
    {
        foreach($this->submissions as $submission) {
            $submission->grade();
        }

        if(Submission::where('round_id', $this->id)->where('status', 'tied')->exists())
        {
            Submission::where('round_id', $this->id)
                ->where('status', 'tied')
                ->get()
                ->random()
                ->update(['status' => 'accepted']);
        }

        $this->update(['ends_at' => now()]);
        
        Round::fromTemplate();

        $this->setUpNextWord();
    }

    public function setUpNextWord()
    {
        $previous_word_of_the_day = Work::where('type', 'word_of_the_day')
            ->where('status', 'in_progress')
            ->get()
            ->last();

        $top_queued_score = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->max('score');

        $next_word_of_the_day = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->where('score', $top_queued_score)
            ->first();

        if(Submission::where('work_id', $previous_word_of_the_day->id)->where('status', 'accepted')->exists())
        {
            $previous_word_of_the_day->update(['status' => 'complete']);

            if($next_word_of_the_day === null) {
                return;
            }
            
            $next_word_of_the_day->update(['status' => 'in_progress']);
        }
    }
}
