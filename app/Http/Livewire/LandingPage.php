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

        if($this->word_of_the_day_yesterday)
        {
            $this->word_of_the_day_yesterday_definition = Submission::where('work_id', $this->word_of_the_day_yesterday->id)
            ->where('status', 'accepted')
            ->first();
        } else {
            $this->word_of_the_day_yesterday_definition = null;
        }

        $this->advice_yesterday = Work::where('type', 'advice')
            ->where('status', 'complete')
            ->get()
            ->last();

        if($this->advice_yesterday)
        {
            $this->advice_yesterday_answer = Submission::where('work_id', $this->advice_yesterday->id)
                ->where('status', 'accepted')
                ->first();
        } else {
            $this->advice_yesterday_answer = null;
        }
        
    }
    
    public function render()
    {
        return view('livewire.landing-page');
    }
}
