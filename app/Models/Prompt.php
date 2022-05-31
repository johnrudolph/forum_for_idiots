<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidWorkException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prompt extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public static function fromTemplate(String $title, String $type)
    {
        if(Work::where('title', $title)
            ->exists()) 
        {
            throw new InvalidWorkException('There is already a work with this name.');
        }
        
        Work::create([
            'title' => $title,
            'type' => $type,
            'status' => 'in_progress',
        ]);
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
}
