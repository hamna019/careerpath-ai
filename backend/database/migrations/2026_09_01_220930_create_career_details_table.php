<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('career_details', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('match')->nullable();
            $table->string('demand');
            $table->text('description');
            $table->text('role_overview')->nullable();
            $table->json('skills'); 
            $table->string('salary');
            $table->string('growth');
            $table->string('timeline');
            $table->string('openings');
            $table->string('experience');
            $table->string('flexibility');
            $table->string('icon')->nullable();
            $table->json('roadmap'); 
            $table->json('courses'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('career_details');
    }
};