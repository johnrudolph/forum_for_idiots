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

    public function currentWordOfTheDay()
    {
        return Work::where('type', 'word_of_the_day')
            ->where('status', 'in_progress')
            ->first();
    }

    public function currentAdvice()
    {
        return Work::where('type', 'advice')
            ->where('status', 'in_progress')
            ->first();
    }

    public static function endRound()
    {        
        foreach(Submission::where('status', 'pending')->get() as $submission) {
            $submission->grade();
        }

        if(Round::currentRound() !== null)
        {
            Round::currentRound()->update(['ends_at' => now()]);
        }
        
        Round::fromTemplate();

        Round::setUpNextWord();
        Round::setUpNextQuestion();
    }

    public static function setUpNextWord()
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

        // dumb thing I'm doing so that when I deploy the app it works
        if(Submission::count() === 0 || $previous_word_of_the_day === null)
        {
            $next_word_of_the_day->update(['status' => 'in_progress']);
        }
        elseif(Submission::where('work_id', $previous_word_of_the_day->id)->where('status', 'accepted')->exists())
        {
            $previous_word_of_the_day->update(['status' => 'complete']);
            
            if($next_word_of_the_day === null) {
                return;
            }

            $next_word_of_the_day->update(['status' => 'in_progress']);
        }
    }

    public static function setUpNextQuestion()
    {
        $previous_advice = Work::where('type', 'advice')
            ->where('status', 'in_progress')
            ->get()
            ->last();

        $top_queued_score = Work::where('type', 'advice')
            ->where('status', 'queued')
            ->max('score');

        $next_advice = Work::where('type', 'advice')
            ->where('status', 'queued')
            ->where('score', $top_queued_score)
            ->first();

        // dumb thing I'm doing so that when I deploy the app it works
        if(Submission::count() === 0 || $previous_advice === null)
        {
            $next_advice->update(['status' => 'in_progress']);
        }
        // if there is at least one accepted submission for previous advice
        elseif(Submission::where('work_id', $previous_advice->id)->where('status', 'accepted')->exists())
        {            
            $previous_advice->update(['status' => 'complete']);

            if($next_advice === null) {
                return;
            }
            
            $next_advice->update(['status' => 'in_progress']);
        }
    }
}
