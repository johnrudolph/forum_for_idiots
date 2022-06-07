<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidVoteException;

class SubmitWord extends Component
{
    public $new_word;
    protected $rules = [
        'new_word' => 'required|string|min:3|max:50',
    ];
    
    public function mount()
    {
        $this->user = Auth::user();

        $this->words = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';

        if(Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->where('user_id', $this->user->id)
            ->count() > 2)
        {
            $this->at_max_submissions = true;
        }
        else
        {
            $this->at_max_submissions = false;
        }
    }
    
    public function submitNewWord()
    {
        $this->validate();

        if(Work::where('type', 'word_of_the_day')
            ->where('title', $this->new_word)
            ->exists())
        {
            session()->flash('message', 'This word has already been submitted.');
            return;
        }
        
        Work::fromTemplate($this->new_word, 'word_of_the_day', $this->user);

        $this->new_word = '';

        if(Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->where('user_id', $this->user->id)
            ->count() > 2)
        {
            $this->at_max_submissions = true;
        }

        $this->sort();
    }

    public function upvoteWord(Int $word_id)
    {
        $word = Work::find($word_id);
        
        try
        {
            $word->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function downvoteWord(Int $word_id)
    {  
        $word = Work::find($word_id);
        
        try
        {
            $word->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function sortByScore()
    {
        $this->words = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        $this->words = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->orderByDesc('created_at')
            ->get();

        $this->sort_by = 'recent';
    }

    public function sortByMine()
    {
        $this->words = Work::where('type', 'word_of_the_day')
            ->where('status', 'queued')
            ->where('user_id', $this->user->id)
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'mine';
    }

    public function sort()
    {
        if($this->sort_by == 'score')
        {
            $this->sortByScore();
        }
        elseif($this->sort_by == 'recent')
        {
            $this->sortByRecent();
        }
        elseif($this->sort_by == 'mine')
        {
            $this->sortByMine();
        }
    }

    public function delete(Int $word_id)
    {
        $word = Work::find($word_id);

        $word->userDelete();

        $this->sort();

        $this->checkForMaxSubmissions();
    }

    public function checkForMaxSubmissions()
    {
        if(Work::where('type', 'word_of_the_day')
        ->where('status', 'queued')
        ->where('user_id', $this->user->id)
        ->count() > 2)
        {
            $this->at_max_submissions = true;
        }
        else
        {
            $this->at_max_submissions = false;
        }
    }

    public function render()
    {
        return view('livewire.submit-word');
    }
}
