<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesDislikesTable extends Migration
{
    public function up(): void
    {
        Schema::create('likes_dislikes', function (Blueprint $table) {
            $table->id('reaction_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id');
            $table->enum('type', ['like','dislike']);
            $table->timestamps();

            $table->unique(['user_id', 'post_id']); // one reaction per user per post
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes_dislikes');
    }
}
