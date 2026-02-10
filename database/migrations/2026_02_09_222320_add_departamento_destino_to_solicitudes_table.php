<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('solicitudes', function (Blueprint $table) {
        $table->unsignedBigInteger('DepartamentoDestino_FK')->nullable()->after('Funcionario_FK');
        
        $table->foreign('DepartamentoDestino_FK')
              ->references('CodDepartamento')->on('departamentos')
              ->onDelete('set null');
    });
}

public function down()
{
    Schema::table('solicitudes', function (Blueprint $table) {
        $table->dropForeign(['DepartamentoDestino_FK']);
        $table->dropColumn('DepartamentoDestino_FK');
    });
}
};
