<?php

use App\Models\User;
use App\Models\Work;
use App\Models\Round;
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
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->integer('upvotes')->default(0);
            $table->integer('downvotes')->default(0);
            $table->integer('score')->default(0);
            $table->string('status')->enum(['pending', 'accepted', 'rejected', 'tied', 'censored']);
            $table->foreignIdFor(User::class, 'user_id');
            $table->foreignIdFor(Work::class, 'work_id');
            $table->foreignIdFor(Round::class, 'round_id');
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
        Schema::dropIfExists('submissions');
    }
};
