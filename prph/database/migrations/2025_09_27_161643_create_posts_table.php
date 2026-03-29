<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id('post_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('skill_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->timestamps();

            // Foreign key constraints with correct column references
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('skill_id')->references('skill_id')->on('skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
}
