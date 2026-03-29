<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id'); // primary key
            $table->string('u_name', 100);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->string('profile_pic')->nullable();
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}
