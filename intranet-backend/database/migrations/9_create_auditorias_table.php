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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->bigIncrements('id_auditoria');
            $table->bigInteger('id_usuario')->unsigned()->nullable(); // Usuario que hizo el cambio
            $table->string('tabla_afectada'); // Nombre de la tabla afectada
            $table->bigInteger('id_registro_afectado')->unsigned(); // ID del registro afectado
            $table->string('tipo_cambio'); // Tipo de cambio: insert, update, delete
            $table->string('datos_anteriores')->nullable(); // Datos anteriores al cambio
            $table->string('datos_nuevos')->nullable(); // Datos nuevos después del cambio
            $table->timestamp('fecha_cambio')->useCurrent(); // Fecha y hora del cambio
            $table->string('direccion_ip')->nullable(); // Dirección IP del usuario que hizo el cambio
            $table->string('navegador')->nullable(); // Información del navegador del usuario
            $table->timestamps();

            // Clave foránea
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
