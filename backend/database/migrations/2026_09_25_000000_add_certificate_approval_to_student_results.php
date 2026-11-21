<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->timestamp('certificate_approved_at')->nullable()->after('completed_at');
            $table->foreignId('certificate_approved_by')->nullable()->after('certificate_approved_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_approved_by');
            $table->dropColumn('certificate_approved_at');
        });
    }
};
