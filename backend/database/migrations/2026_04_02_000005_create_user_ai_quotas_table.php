<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ai_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('daily_limit')->default(50);
            $table->integer('monthly_limit')->default(1000);
            $table->integer('daily_usage')->default(0);
            $table->integer('monthly_usage')->default(0);
            $table->date('daily_reset_date');
            $table->integer('monthly_reset_day')->default(1);
            $table->enum('plan_tier', ['free', 'basic', 'pro', 'enterprise'])->default('free');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index('plan_tier');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ai_quotas');
    }
};
