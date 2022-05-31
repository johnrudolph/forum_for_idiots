<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use App\Models\Submission;
use App\Exceptions\InvalidVoteException;

class LandingPage extends Component
{
    public function mount()
    {        
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
    
    public function render()
    {
        return view('livewire.landing-page');
    }
}
