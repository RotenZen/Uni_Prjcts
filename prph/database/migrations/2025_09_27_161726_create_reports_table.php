<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('user_id');
            //$table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            //$table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->enum('status', ['pending','reviewed','action_taken'])->default('pending');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
}
