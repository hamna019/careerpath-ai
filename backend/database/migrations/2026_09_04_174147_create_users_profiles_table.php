<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('city')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('residential_address')->nullable();
            $table->string('gender')->nullable();
            $table->string('current_education')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('university')->nullable();
            $table->string('cgpa')->nullable();
            $table->string('graduation_year')->nullable();
            $table->json('career_interests')->nullable();
            $table->json('target_job_roles')->nullable();
            $table->json('core_skills')->nullable();
            $table->json('preferred_work_mode')->nullable();
            $table->text('bio')->nullable();  
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('portfolio')->nullable();
            $table->string('twitter')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('profiles');
    }
};