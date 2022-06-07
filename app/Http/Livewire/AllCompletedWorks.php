<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use App\Models\Submission;
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

        $this->submissions = Submission::where('status', 'accepted')->get();

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

    public function filter(String $filter_by)
    {
        $this->filter_by = $filter_by;
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
        if($this->filter_by === 'all')
        {
            $this->works = Work::where('status', 'complete')
                ->orderByDesc('score')
                ->get();
        }
        elseif($this->filter_by === 'advice')
        {
            $this->works = Work::where('status', 'complete')
                ->where('type', 'advice')
                ->orderByDesc('score')
                ->get();
        }
        elseif($this->filter_by === 'word')
        {
            $this->works = Work::where('status', 'complete')
                ->where('type', 'word_of_the_day')
                ->orderByDesc('score')
                ->get();
        }
        
        $this->sort_by = 'score';
    }

    public function sortByRecent()
    {
        if($this->filter_by === 'all')
        {
            $this->works = Work::where('status', 'complete')
                ->orderByDesc('created_at')
                ->get();
        }
        elseif($this->filter_by === 'advice')
        {
            $this->works = Work::where('status', 'complete')
                ->where('type', 'advice')
                ->orderByDesc('created_at')
                ->get();
        }
        elseif($this->filter_by === 'word')
        {
            $this->works = Work::where('status', 'complete')
                ->where('type', 'word_of_the_day')
                ->orderByDesc('created_at')
                ->get();
        }

        $this->sort_by = 'recent';
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

    public function delete(Int $work_id)
    {
        $work = Work::find($work_id);

        $work->userDelete();

        $this->sort();
    }
}