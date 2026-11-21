<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->string('type')->default('Multiple Choice');
            $table->string('category')->default('Technical Skills');
            $table->string('weight')->default('Medium');
            $table->string('status')->default('Active');
            $table->json('options');
            $table->string('correct_answer')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('quiz_questions');
    }
};