<?php

use App\Models\User;
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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Round::class, 'round_id')->nullable();
            $table->foreignIdFor(User::class, 'user_id')->nullable();
            $table->string('title');
            $table->string('type')->enum(['poem', 'short_story', 'word_of_the_day', 'advice']);
            $table->string('status')->enum(['in_progress', 'complete']);
            $table->integer('score')->default(0);
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
        Schema::dropIfExists('works');
    }
};
