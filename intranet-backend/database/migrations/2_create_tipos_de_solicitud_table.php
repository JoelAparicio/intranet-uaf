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
        Schema::create('tipos_de_solicitud', function (Blueprint $table) {
            $table->bigIncrements('id_tipo_solicitud');
            $table->string('tipo_solicitud', 100);
            $table->string('descripcion', 400)->nullable();
            $table->boolean('deleted')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_de_solicitud');
    }
};
