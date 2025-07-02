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
        Schema::create('usuarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->string('correo_electronico')->unique();
            $table->string('cargo', 100);
            $table->string('posicion',40);
            $table->string('cedula', 25)->unique();
            $table->string('extension', 10)->unique();
            $table->string('estado', 25)->default('activo');
            $table->bigInteger('departamento')->nullable();
            $table->bigInteger('tiempo_extra')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
            $table->foreign('departamento')->references('id_departamento')->on('departamentos')->onDelete('set null');
            $table->string('firma_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

