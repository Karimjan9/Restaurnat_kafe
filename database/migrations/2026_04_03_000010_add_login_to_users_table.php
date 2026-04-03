<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('login')->nullable()->after('name');
        });

        $users = DB::table('users')
            ->select(['id', 'name', 'email', 'login'])
            ->orderBy('id')
            ->get();

        foreach ($users as $user) {
            $login = $user->login;

            if (! filled($login)) {
                $baseLogin = Str::of($user->email ?: $user->name)
                    ->before('@')
                    ->lower()
                    ->replaceMatches('/[^a-z0-9]+/', '');

                if ($baseLogin->isEmpty()) {
                    $baseLogin = Str::of('user'.$user->id);
                }

                $login = (string) $baseLogin;
                $suffix = 1;

                while (
                    DB::table('users')
                        ->where('login', $login)
                        ->where('id', '!=', $user->id)
                        ->exists()
                ) {
                    $login = $baseLogin.$suffix;
                    $suffix++;
                }
            }

            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'login' => $login,
                    'password' => Hash::make($login.'456'),
                ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('login');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['login']);
            $table->dropColumn('login');
        });
    }
};
