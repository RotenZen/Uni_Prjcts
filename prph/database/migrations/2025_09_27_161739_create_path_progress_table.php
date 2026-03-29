<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePathProgressTable extends Migration
{
    public function up(): void
    {
        Schema::create('path_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->unsignedBigInteger('follow_id');
            $table->unsignedBigInteger('resource_id');
            //$table->foreignId('follow_id')->constrained('follow_paths')->onDelete('cascade');
            //$table->foreignId('resource_id')->constrained('resources')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_to_completion')->nullable(); // minutes or seconds as you prefer
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['follow_id', 'resource_id']); // one progress row per resource per follow

            $table->foreign('follow_id')->references('follow_id')->on('follow_paths')->onDelete('cascade');
            $table->foreign('resource_id')->references('resource_id')->on('resources')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('path_progress');
    }
}
