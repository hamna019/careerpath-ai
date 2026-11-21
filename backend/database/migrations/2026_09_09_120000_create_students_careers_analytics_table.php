<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('student_assessments');
        Schema::dropIfExists('careers');
        Schema::rename('career_details', 'careers');

        Schema::create('students_careers_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('career_id')->nullable()->constrained('careers')->nullOnDelete();
            $table->string('career_title');
            $table->unsignedTinyInteger('score')->nullable(); 
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->index('career_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students_careers_analytics');

        Schema::rename('careers', 'career_details');

        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('career_id')->nullable()->constrained('career_details')->nullOnDelete();
            $table->unsignedTinyInteger('score')->nullable();
            $table->string('status')->default('Completed');
            $table->timestamps();
        });
    }
};
