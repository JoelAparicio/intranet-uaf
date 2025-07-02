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
        Schema::create('eventos', function (Blueprint $table) {
            $table->bigIncrements('id_evento');
            $table->string('titulo', 100);
            $table->string('descripcion', 500)->nullable();
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();
            $table->bigInteger('creado_por');
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
            $table->foreign('creado_por')->references('id')->on('usuarios')->onDelete('no action');
            $table->timestamp('fecha_creacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
