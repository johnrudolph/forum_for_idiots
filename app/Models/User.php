<?php

namespace App\Models;

use App\Models\Work;
use App\Models\Submission;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'aggregate_score',
        'is_moderator',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function calculateAggregateScore()
    {
        $upvotes = Vote::where('user_rewarded' , $this->id)
            ->where('type', 'upvote')
            ->count();

        $downvotes = Vote::where('user_rewarded' , $this->id)
            ->where('type', 'downvote')
            ->count();

        $this->update(['aggregate_score' => $upvotes - $downvotes]);
    }
}
