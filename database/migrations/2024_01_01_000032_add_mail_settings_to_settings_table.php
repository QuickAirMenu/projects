<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['key' => 'mail_host', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_port', 'value' => '465', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_encryption', 'value' => 'ssl', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_username', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_password', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_from_address', 'value' => '', 'type' => 'string', 'group' => 'mail'],
            ['key' => 'mail_from_name', 'value' => '', 'type' => 'string', 'group' => 'mail'],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->updateOrInsert(
                ['key' => $row['key']],
                $row
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'mail_host', 'mail_port', 'mail_encryption', 'mail_username',
            'mail_password', 'mail_from_address', 'mail_from_name',
        ])->delete();
    }
};
