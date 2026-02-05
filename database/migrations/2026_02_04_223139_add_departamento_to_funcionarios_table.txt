<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            // FK hacia la tabla departamentos
            // Nullable: porque al crear el sistema, quizás haya funcionarios viejos sin depto asignado
            $table->unsignedBigInteger('Departamento_FK')->nullable()->after('CodFuncionario');

            $table->foreign('Departamento_FK')
                  ->references('CodDepartamento')->on('departamentos')
                  ->onDelete('set null'); // Si se borra un depto, el usuario no se rompe, queda sin depto
        });
    }

    public function down()
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropForeign(['Departamento_FK']);
            $table->dropColumn('Departamento_FK');
        });
    }
};