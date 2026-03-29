<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlacklistedDomainsTable extends Migration
{
    public function up(): void
    {
        Schema::create('blacklisted_domains', function (Blueprint $table) {
            $table->string('domain', 255)->primary();
            $table->string('reason')->nullable();
            $table->timestamp('added_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blacklisted_domains');
    }
}
