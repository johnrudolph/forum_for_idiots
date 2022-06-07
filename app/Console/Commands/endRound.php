<?php

namespace App\Console\Commands;

use App\Models\Round;
use Illuminate\Console\Command;

class endRound extends Command
{
    protected $signature = 'round:end';
    protected $description = 'Ends the current round';

    public function handle()
    {
        return Round::endRound() ? 0 : 1;
    }
}
