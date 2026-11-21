<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->json('traits')->nullable()->after('courses');
        });

        Schema::table('student_results', function (Blueprint $table) {
            $table->json('answers')->nullable()->after('match_percent');
            $table->json('recommendations')->nullable()->after('answers');
        });
    }

    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn('traits');
        });

        Schema::table('student_results', function (Blueprint $table) {
            $table->dropColumn(['answers', 'recommendations']);
        });
    }
};
