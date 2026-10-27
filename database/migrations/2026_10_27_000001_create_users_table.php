<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Same tables and columns as controlfinan-rails8-hotwire, so both apps can share one database.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email_address')->unique();
            $table->string('password_digest');
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
        });

        Schema::create('current_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained();
            $table->integer('cash_account_id');
            $table->integer('cost_id');
            $table->date('date_ocurrence');
            $table->integer('type_launche');
            $table->decimal('price', 9, 2);
            $table->string('historic')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->index(['user_id', 'date_ocurrence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('current_accounts');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
