<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departamentos', function (Blueprint $table) {
            // Un departamento puede pertenecer a otro (Padre)
            // Es nullable porque las Gerencias Generales no tienen padre (son raíz)
            $table->unsignedBigInteger('DepartamentoPadre_FK')->nullable()->after('NombreDepartamento');

            $table->foreign('DepartamentoPadre_FK')
                  ->references('CodDepartamento')->on('departamentos')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('departamentos', function (Blueprint $table) {
            $table->dropForeign(['DepartamentoPadre_FK']);
            $table->dropColumn('DepartamentoPadre_FK');
        });
    }
};