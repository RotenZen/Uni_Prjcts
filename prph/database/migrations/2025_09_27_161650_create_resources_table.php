<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResourcesTable extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id('resource_id');
            $table->enum('type', ['video','article','book','playlist','website','other']);
            $table->string('title', 255);
            $table->string('url', 500)->unique();
            $table->string('domain', 255)->nullable(); // extracted domain for blacklist checks
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
}
