<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidVoteException;

class SubmitAdvice extends Component
{
    public $new_advice;
    protected $rules = [
        'new_advice' => 'required|string|min:3|max:250',
    ];
    
    public function mount()
    {
        $this->user = Auth::user();

        $this->question = Work::where('status', 'in_progress')
            ->where('type', 'advice')
            ->get()
            ->last();

        if($this->question)
        {
            $this->advice_submissions = Submission::where('work_id', $this->question->id)
                ->where('status', 'pending')
                ->orderByDesc('score')
                ->get();

            if(Submission::where('work_id', $this->question->id)
                ->where('status', 'pending')
                ->where('user_id', $this->user->id)
                ->count() > 0)
            {
                $this->at_max_submissions = true;
            }
            else
            {
                $this->at_max_submissions = false;
            }
        } else {
            $this->advice_submissions = null;
        }  

        $this->sort_by = 'score';
    }
    
    public function submitNewAdvice()
    {
        $this->validate();

        if(Submission::where('work_id', $this->question->id)
            ->where('text', $this->new_advice)
            ->exists())
        {
            session()->flash('message', 'This advice has already been submitted.');
            return;
        }

        Submission::fromTemplate($this->question, $this->user, $this->new_advice);

        $this->at_max_submissions = true;

        $this->sort();
    }

    public function upvoteAdvice(Int $advice_id)
    {
        $advice = Submission::find($advice_id);
        
        try
        {
            $advice->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function downvoteAdvice(Int $advice_id)
    {  
        $advice = Submission::find($advice_id);
        
        try
        {
            $advice->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function sortByScore()
    {
        $this->advice_submissions = Submission::where('work_id', $this->question->id)
            ->where('status', 'pending')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        $this->advice_submissions = Submission::where('work_id', $this->question->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $this->sort_by = 'recent';
    }

    public function sortByMine()
    {     
        $this->advice_submissions = Submission::where('work_id', $this->question->id)
            ->where('status', 'pending')
            ->where('user_id', $this->user->id)
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

    public function delete(Int $advice_id)
    {
        $advice = Submission::find($advice_id);

        $advice->userDelete();

        $this->sort();

        $this->at_max_submissions = false;
    }
    
    public function render()
    {
        return view('livewire.submit-advice');
    }
}
