<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowPathsTable extends Migration
{
    public function up(): void
    {
        Schema::create('follow_paths', function (Blueprint $table) {
            $table->id('follow_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('post_id');
            //$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            //$table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->timestamp('started_at')->useCurrent();
            $table->unique(['user_id', 'post_id']); // user cannot follow same post twice

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_paths');
    }
}
