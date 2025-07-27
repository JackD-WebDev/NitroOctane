<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }
    
        DB::unprepared('DROP FUNCTION IF EXISTS `f_new_uuid`;');
        DB::unprepared("
            CREATE DEFINER=`root`@`%` FUNCTION `f_new_uuid`() RETURNS char(36)
                NOT DETERMINISTIC
            BEGIN
                DECLARE cNewUUID char(36);
                DECLARE ts BIGINT;
                DECLARE rand_val BIGINT;
                
                SET ts = UNIX_TIMESTAMP(NOW()) * 1000000 + FLOOR(RAND() * 1000000);
                SET rand_val = FLOOR(RAND() * POW(2, 60));
                SET cNewUUID = LPAD(HEX(ts), 12, '0');
                SET cNewUUID = 
                    CONCAT(
                        SUBSTRING(cNewUUID, 1, 8), '-',
                        SUBSTRING(cNewUUID, 9, 4), '-',
                        '7', SUBSTRING(LPAD(HEX(rand_val), 15, '0'), 1, 3), '-',
                        SUBSTR(HEX(64 + FLOOR(RAND() * 16)), -2), 
                        SUBSTRING(LPAD(HEX(rand_val), 15, '0'), 4, 2), '-',
                        SUBSTRING(LPAD(HEX(rand_val), 15, '0'), 6, 12)
                    );
                
                RETURN LOWER(cNewUUID);
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('drop function if exists f_new_uuid;');
    }
};
