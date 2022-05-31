<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\InvalidVoteException;

class AllCompletedWorks extends Component
{
    public function mount()
    {
        $this->user = Auth::user();

        $this->works = Work::where('status', 'complete')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
        $this->filter_by = 'all';
    }

    public function upvoteWork(Int $work_id)
    {
        $work = Work::find($work_id);
        
        try
        {
            $work->upvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort();
    }

    public function downvoteWork(Int $work_id)
    {  
        $work = Work::find($work_id);
        
        try
        {
            $work->downvote($this->user);
        }
        catch (InvalidVoteException $e)
        {
            return;
        }

        $this->sort($this->sort_by, $this->filter_by);
    }

    public function sortByScore()
    {
        $this->works = Work::where('status', 'complete')
            ->orderByDesc('score')
            ->get();

        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        $this->works = Work::where('status', 'complete')
            ->orderByDesc('created_at')
            ->get();

        $this->sort_by = 'recent';
    }

    public function sortByMine()
    {
        $this->works = Work::where('status', 'complete')
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
    
    public function render()
    {
        return view('livewire.all-completed-works');
    }
}