<?php

namespace App\Http\Livewire;

use App\Models\Work;
use Livewire\Component;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class WorkPage extends Component
{
    public $vote_target_id;
    public $submission;
    
    public function mount($work)
    {
        $this->user = Auth::user();
        
        $this->work = Work::find($work);

        $this->title = $this->work->title;

        $this->full_text = $this->work->completeText();

        $this->prompt = $this->work->prompt();

        $this->submissions = Submission::where('work_id', $this->work->id)
            ->where('status', 'pending')
            ->orderBy('score', 'desc')
            ->get();
    }

    public function submit()
    {
        dd('submit');

        Submission::fromTemplate($this->poem, $this->user, $this->submission);
    }

    public function upvote()
    {
        $this->submission->upvote($this->user);
    }

    public function downvote()
    {
        $this->submission->downvote($this->user);
    }
    
    public function render()
    {
        return view('livewire.work-page');
    }
}
