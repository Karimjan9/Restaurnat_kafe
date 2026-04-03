<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        DB::table('users')->update([
            'email' => null,
            'email_verified_at' => null,
        ]);
    }

    public function down(): void
    {
        DB::table('users')
            ->select(['id'])
            ->orderBy('id')
            ->get()
            ->each(function (object $user) {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'email' => 'user'.$user->id.'@example.invalid',
                    ]);
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};
