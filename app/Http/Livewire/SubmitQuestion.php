<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidVoteException;

class SubmitQuestion extends Component
{
    public $new_question;
    protected $rules = [
        'new_question' => 'required|string|min:3|max:250',
    ];
    
    public function mount()
    {
        $this->user = Auth::user();

        $this->questions = Work::where('type', 'advice')
            ->where('status', 'queued')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';

        if(Work::where('type', 'advice')
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
    
    public function submitNewQuestion()
    {
        $this->validate();

        if(Work::where('type', 'advice')
            ->where('title', $this->new_question)
            ->exists())
        {            
            session()->flash('message', 'This question has already been submitted.');
            return;
        }
    
        Work::fromTemplate($this->new_question, 'advice', $this->user);

        $this->new_question = '';

        if(Work::where('type', 'advice')
            ->where('status', 'queued')
            ->where('user_id', $this->user->id)
            ->count() > 2)
        {
            $this->at_max_submissions = true;
        }

        $this->sort();
    }

    public function upvoteQuestion(Int $question_id)
    {
        $question = Work::find($question_id);
        
        try
        {
            $question->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function downvoteQuestion(Int $question_id)
    {  
        $question = Work::find($question_id);
        
        try
        {
            $question->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function sortByScore()
    {
        $this->questions = Work::where('type', 'advice')
            ->where('status', 'queued')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        $this->questions = Work::where('type', 'advice')
            ->where('status', 'queued')
            ->orderByDesc('created_at')
            ->get();

        $this->sort_by = 'recent';
    }

    public function sortByMine()
    {
        $this->questions = Work::where('type', 'advice')
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

    public function delete(Int $question_id)
    {
        $question = Work::find($question_id);

        $question->userDelete();

        $this->sort();

        $this->checkForMaxSubmissions();
    }

    public function checkForMaxSubmissions()
    {
        if(Work::where('type', 'advice')
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
        return view('livewire.submit-question');
    }
}
