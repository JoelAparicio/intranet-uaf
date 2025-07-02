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
        Schema::create('birthdays', function (Blueprint $table) {
            $table->bigIncrements('id_birthday'); // Primary Key
            $table->string('usuario')->unique(); // temporal, luego cambiar a id_usuario
            $table->bigInteger('id_departamento'); // Foreign Key to departamentos
            $table->date('fecha_birthday'); // Birthday date
            $table->boolean('deleted')->default(0); // Soft delete (logical delete)
            $table->softDeletes();

            // Foreign Key Constraints
            // $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('no action');
            $table->foreign('id_departamento')->references('id_departamento')->on('departamentos')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('birthdays');
    }
};
