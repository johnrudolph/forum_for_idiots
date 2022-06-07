<?php

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
use App\Models\Submission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Submission::class, 'submission_id')->nullable();
            $table->foreignIdFor(Work::class, 'work_id')->nullable();
            $table->foreignIdFor(User::class, 'user_id');
            $table->integer('user_rewarded');
            $table->string('type')->enum(['upvote', 'downvote']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('votes');
    }
};
