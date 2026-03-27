<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAracaosNullableAndAddSolicitante extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aracaos', function (Blueprint $table) {
            $table->string('solicitante')->nullable()->after('beneficiario_id');
            $table->unsignedBigInteger('beneficiario_id')->nullable()->change();
            $table->string('cultura')->nullable()->change();
            $table->string('ponto_localizacao')->nullable()->change();
            $table->string('quantidade_ha')->nullable()->change();
            $table->string('quantidade_horas')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aracaos', function (Blueprint $table) {
            $table->string('quantidade_horas')->nullable(false)->change();
            $table->string('quantidade_ha')->nullable(false)->change();
            $table->string('ponto_localizacao')->nullable(false)->change();
            $table->string('cultura')->nullable(false)->change();
            $table->unsignedBigInteger('beneficiario_id')->nullable(false)->change();
            $table->dropColumn('solicitante');
        });
    }
}
