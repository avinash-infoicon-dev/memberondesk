<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->restrictOnDelete();
            $table->foreignId('member_id')->constrained()->restrictOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('check_in_at')->index();
            $table->timestamp('check_out_at')->nullable();
            $table->string('source', 16)->default('qr');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'check_in_at']);
            $table->index(['member_id', 'check_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
