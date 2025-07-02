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


        Schema::create('solicitudes', function (Blueprint $table) {
            $table->bigIncrements('id_solicitud');
            $table->bigInteger('id_usuario');
            $table->bigInteger('id_tipo_solicitud');
            $table->string('motivo', 500)->nullable();
            $table->string('observacion', 500)->nullable();
            $table->string('trabajo_realizado', 500)->nullable();
            $table->string('justificacion', 500)->nullable();
            $table->string('estado', 50)->default('pendiente');
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->timestamp('fecha_creacion')->nullable();
            $table->string('tiempo_utilizado')->nullable();
            $table->string('tiempo_laborado')->nullable();
            $table->boolean('deleted')->default(0);
            $table->softDeletes();

            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('no action');
            $table->foreign('id_tipo_solicitud')->references('id_tipo_solicitud')->on('tipos_de_solicitud')->onDelete('no action');
        });

        

        Schema::create('aprobaciones', function (Blueprint $table) {
            $table->bigIncrements('id_aprobacion');
            $table->bigInteger('id_solicitud');
            $table->bigInteger('id_usuario_solicitud');
            $table->bigInteger('id_jefe_departamento')->nullable();
            $table->bigInteger('id_rrhh')->nullable();
            $table->boolean('aprobado_jefe')->default(false); // Indica si la solicitud fue aprobada o negada
            $table->boolean('aprobado_rrhh')->default(false); // Indica si la solicitud fue aprobada o negada
            $table->string('comentarios_jefe', 400)->nullable(); // Comentarios sobre la decisión jefe
            $table->string('comentarios_rrhh', 400)->nullable(); // Comentarios sobre la decisión rrhh
            $table->timestamp('fecha_finalizacion')->nullable(); // Fecha de aprobación/negación
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
            
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes')->onDelete('no action');
            $table->foreign('id_usuario_solicitud')->references('id')->on('usuarios')->onDelete('no action');
            $table->foreign('id_jefe_departamento')->references('id')->on('usuarios')->onDelete('no action');
            $table->foreign('id_rrhh')->references('id')->on('usuarios')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprobaciones');
        Schema::dropIfExists('solicitudes');
        
    }
};
