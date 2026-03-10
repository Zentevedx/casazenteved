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
        Schema::table('prestamos', function (Blueprint $table) {
            $table->string('codigo_comprobante')->nullable()->unique()->after('id');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->string('codigo_comprobante')->nullable()->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestamos_and_pagos_tables', function (Blueprint $table) {
            //
        });
    }
};
