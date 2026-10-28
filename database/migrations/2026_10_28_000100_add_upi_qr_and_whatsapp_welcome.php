<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('account_holder_name')->nullable()->after('upi_id');
            $table->string('upi_qr_path')->nullable()->after('account_holder_name');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->text('whatsapp_welcome_message')->nullable()->after('notes');
            $table->timestamp('whatsapp_welcome_sent_at')->nullable()->after('whatsapp_welcome_message');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['account_holder_name', 'upi_qr_path']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_welcome_message', 'whatsapp_welcome_sent_at']);
        });
    }
};
