<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_otps', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
            $table->boolean('is_used')->default(false)->after('expires_at');
            $table->timestamp('used_at')->nullable()->after('is_used');
        });

        Schema::table('student_otps', function (Blueprint $table) {
            $table->dropUnique(['email']);
            $table->index('email');
            $table->boolean('is_used')->default(false)->after('expires_at');
            $table->timestamp('used_at')->nullable()->after('is_used');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropPrimary(['email']);
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->bigIncrements('id')->first();
            $table->index('email');
            $table->boolean('is_used')->default(false)->after('token');
            $table->timestamp('used_at')->nullable()->after('is_used');
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn(['id', 'is_used', 'used_at', 'updated_at']);
            $table->dropIndex(['email']);
            $table->primary('email');
        });

        Schema::table('student_otps', function (Blueprint $table) {
            $table->dropColumn(['is_used', 'used_at']);
            $table->dropIndex(['email']);
            $table->unique('email');
        });

        Schema::table('admin_otps', function (Blueprint $table) {
            $table->dropColumn(['is_used', 'used_at']);
            $table->dropIndex(['email']);
            $table->unique('email');
        });
    }
};
