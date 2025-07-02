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
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->bigIncrements('id_notificacion');
            $table->bigInteger('id_usuario');
            $table->bigInteger('id_solicitud')->nullable();
            $table->string('titulo', 255);
            $table->string('mensaje', 400);
            $table->string('correo_destinatario', 255); // Dominio de correo o lista de destinatarios
            $table->boolean('enviado')->default(false); // Indica si la notificación ha sido enviada
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
            $table->timestamp('fecha_creacion');
            $table->foreign('id_usuario')->references('id')->on('usuarios')->onDelete('no action');
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes')->onDelete('no action');
        });
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
