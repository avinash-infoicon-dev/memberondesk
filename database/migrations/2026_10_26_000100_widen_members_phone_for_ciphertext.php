<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'phone']);
        });

        DB::statement('ALTER TABLE `members` MODIFY `phone` TEXT NULL');

        // Prefix keeps the unique index inside InnoDB's key-length limit.
        DB::statement('ALTER TABLE `members` ADD UNIQUE `members_business_id_phone_unique` (`business_id`, `phone`(191))');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'phone']);
        });

        DB::statement('ALTER TABLE `members` MODIFY `phone` VARCHAR(20) NULL');

        DB::statement('ALTER TABLE `members` ADD UNIQUE `members_business_id_phone_unique` (`business_id`, `phone`)');
    }
};
