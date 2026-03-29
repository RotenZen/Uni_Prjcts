<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostResourcesTable extends Migration
{
    public function up(): void
    {
        Schema::create('post_resources', function (Blueprint $table) {
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('resource_id');
            $table->integer('order_number')->default(0);
            // composite primary key
            $table->primary(['post_id', 'resource_id']);
            // foreign keys
            $table->foreign('post_id')->references('post_id')->on('posts')->onDelete('cascade');
            $table->foreign('resource_id')->references('resource_id')->on('resources')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_resources');
    }
}
