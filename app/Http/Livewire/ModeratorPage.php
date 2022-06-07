<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;

class ModeratorPage extends Component
{
    public $password;
    public $email;

    public function submit()
    {
        if($this->password !== 'letsgobirds')
        {
            session()->flash('message', 'Incorrect password.');
            return;
        }

        if(User::where('email', $this->email)->exists())
        {
            User::where('email', $this->email)->first()->update(['is_moderator' => 1]);
            session()->flash('message', 'Success!');
        } else {
            session()->flash('message', 'No user found with that email.');
        }

    }
    
    public function render()
    {
        return view('livewire.moderator-page');
    }
}
