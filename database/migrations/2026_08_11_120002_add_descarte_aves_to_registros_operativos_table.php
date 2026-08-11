<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->unsignedInteger('descarte_aves')->nullable()->after('muertes');
        });
    }

    public function down(): void
    {
        Schema::table('registros_operativos', function (Blueprint $table) {
            $table->dropColumn('descarte_aves');
        });
    }
};
