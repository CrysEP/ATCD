<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Como solicitaste, esta tabla extenderá la funcionalidad
        // para manejar múltiples archivos adjuntos por solicitud.
        Schema::create('archivos_solicitud', function (Blueprint $table) {
            $table->id();
            
            // Usamos constrained para asegurar la integridad referencial
            // Asumiendo que la PK de 'solicitud' es 'CodSolucitud'
            $table->foreignId('solicitud_id')
                  ->constrained('solicitud', 'CodSolucitud')
                  ->onDelete('cascade');
                  
            $table->string('nombre_original');
            $table->string('ruta_archivo'); // se guardará en storage/app/public/solicitudes
            $table->string('tipo_archivo')->nullable();
            $table->unsignedBigInteger('tamano_archivo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('archivos_solicitud');
    }
};
