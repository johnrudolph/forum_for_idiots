<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidVoteException;

class SubmitDefinition extends Component
{
    public $new_definition;
    protected $rules = [
        'new_definition' => 'required|string|min:3|max:250',
    ];
    
    public function mount()
    {
        $this->user = Auth::user();

        $this->word = Work::where('status', 'in_progress')
            ->where('type', 'word_of_the_day')
            ->get()
            ->last();

        if($this->word)
        {
            $this->definitions = Submission::where('work_id', $this->word->id)
                ->where('status', 'pending')
                ->orderByDesc('score')
                ->get();

            if(Submission::where('work_id', $this->word->id)
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
            $this->definitions = null;
        }
        
        $this->sort_by = 'score';
    }
    
    public function submitNewDefinition()
    {
        $this->validate();

        if(Submission::where('work_id', $this->word->id)
            ->where('text', $this->new_definition)
            ->exists())
        {
            session()->flash('message', 'This definition has already been submitted.');
            return;
        }
        
        Submission::fromTemplate($this->word, $this->user, $this->new_definition);

        $this->at_max_submissions = true;

        $this->sort();
    }

    public function upvoteDefinition(Int $definition_id)
    {
        $definition = Submission::find($definition_id);
        
        try
        {
            $definition->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function downvoteDefinition(Int $definition_id)
    {  
        $definition = Submission::find($definition_id);
        
        try
        {
            $definition->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function sortByScore()
    {
        $this->definitions = Submission::where('work_id', $this->word->id)
            ->where('status', 'pending')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        $this->definitions = Submission::where('work_id', $this->word->id)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->get();

        $this->sort_by = 'recent';
    }

    public function sortByMine()
    {     
        $this->definitions = Submission::where('work_id', $this->word->id)
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

    public function delete(Int $definition_id)
    {
        $definition = Submission::find($definition_id);

        $definition->userDelete();

        $this->sort();

        $this->at_max_submissions = false;
    }
    
    public function render()
    {
        return view('livewire.submit-definition');
    }
}
