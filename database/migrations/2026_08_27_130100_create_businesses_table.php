<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 32)->default('gym');
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('upi_id')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 12)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->boolean('whatsapp_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
