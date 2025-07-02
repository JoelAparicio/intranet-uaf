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
        Schema::create('documentos', function (Blueprint $table) {
            $table->bigIncrements('id_documento');
            $table->string('nombre_documento');
            $table->string('path');
            $table->bigInteger('id_solicitud');
            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes')->onDelete('no action');
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
            $table->timestamp('fecha_creacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
