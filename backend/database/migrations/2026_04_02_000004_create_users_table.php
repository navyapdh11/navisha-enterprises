<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'manager', 'staff', 'customer'])->default('customer');
            $table->string('zone', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('preferences')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('language', 10)->default('en');
            $table->string('timezone', 50)->default('UTC');
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('role');
            $table->index('zone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
