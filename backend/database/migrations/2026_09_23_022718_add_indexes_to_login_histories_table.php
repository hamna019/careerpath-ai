<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->index(['role', 'login_at'], 'login_histories_role_login_at_index');

            $table->index(['role', 'status', 'logout_at'], 'login_histories_role_status_logout_index');
        });
    }

    public function down(): void
    {
        Schema::table('login_histories', function (Blueprint $table) {
            $table->dropIndex('login_histories_role_login_at_index');
            $table->dropIndex('login_histories_role_status_logout_index');
        });
    }
};
