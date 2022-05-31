<?php

namespace App\Http\Livewire;

use App\Exceptions\InvalidVoteException;
use App\Models\Work;
use App\Models\Round;
use Livewire\Component;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class HomePage extends Component
{
    public function mount()
    {
        $this->user = Auth::user();
        
        $this->word_of_the_day_yesterday = Work::where('type', 'word_of_the_day')
            ->where('status', 'complete')
            ->get()
            ->last();

        $this->word_of_the_day_yesterday_definition = Submission::where('work_id', $this->word_of_the_day_yesterday->id)
            ->where('status', 'accepted')
            ->first();

        $this->advice_yesterday = Work::where('type', 'advice')
            ->where('status', 'complete')
            ->get()
            ->last();

        $this->advice_yesterday_answer = Submission::where('work_id', $this->advice_yesterday->id)
            ->where('status', 'accepted')
            ->first();

        $this->word_of_the_day = Work::where('type', 'word_of_the_day')
            ->where('status', 'in_progress')
            ->get()
            ->last();

        $this->advice = Work::where('type', 'advice')
            ->where('status', 'in_progress')
            ->get()
            ->last();
    }

    public function upvoteWordOfTheDay()
    {
        try
        {
            $this->word_of_the_day_yesterday->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }
    }

    public function downvoteWordOfTheDay()
    {
        try
        {
            $this->word_of_the_day_yesterday->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }
    }

    public function upvoteAdvice()
    {
        try
        {
            $this->advice_yesterday->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }
    }

    public function downvoteAdvice()
    {
        try
        {
            $this->advice_yesterday->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }
    }

    public function submitDefinition()
    {
        redirect('/word-of-the-day/' . $this->word_of_the_day->id);
    }

    public function submitAdvice()
    {
        redirect('/advice/' . $this->advice->id);
    }

    public function askForAdvice()
    {
        redirect('/aks-for-advice');
    }

    public function submitNewWord()
    {
        redirect('/submit-word');
    }
    
    public function render()
    {
        return view('livewire.home-page');
    }
}
