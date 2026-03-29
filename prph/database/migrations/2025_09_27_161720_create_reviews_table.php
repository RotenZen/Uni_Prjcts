<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->unsignedBigInteger('user_id');

            $table->enum('target_type', ['post','resource']); // which table target_id refers to
            $table->unsignedBigInteger('target_id'); // validate in backend
            $table->unsignedTinyInteger('rating'); // 1-5 validated in backend
            $table->text('comment')->nullable();
            $table->timestamp('created_at')->useCurrent();
            // note: target_id has no FK because it can point to different tables

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
