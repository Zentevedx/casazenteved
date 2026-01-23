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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_movimiento', ['Ingreso', 'Egreso']);
            $table->string('origen')->nullable();
            $table->string('descripcion');
            $table->decimal('monto', 10, 2);
            $table->decimal('saldo_caja', 15, 2)->nullable();
            $table->date('fecha');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_tabla')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
