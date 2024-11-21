<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            $table->string('salt')->nullable()->after('password');
            $table->string('last_name')->nullable()->after('salt');
            $table->timestamp('last_login')->nullable()->after('last_name');
            $table->timestamp('last_activity')->nullable()->after('last_login');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
            $table->dropColumn('salt');
            $table->dropColumn('last_name');
            $table->dropColumn('last_login');
            $table->dropColumn('last_activity');
        });
    }
};
