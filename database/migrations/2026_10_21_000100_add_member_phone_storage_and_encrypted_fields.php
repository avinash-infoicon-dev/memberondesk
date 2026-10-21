<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('member_phone_storage', 32)
                ->default('device_local')
                ->after('whatsapp_enabled');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->text('phone_ciphertext')->nullable()->after('phone');
            $table->text('phone_wrapped_key')->nullable()->after('phone_ciphertext');
        });

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `members` MODIFY `phone` VARCHAR(20) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['phone_ciphertext', 'phone_wrapped_key']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `members` MODIFY `phone` VARCHAR(20) NOT NULL');
        }

        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('member_phone_storage');
        });
    }
};
