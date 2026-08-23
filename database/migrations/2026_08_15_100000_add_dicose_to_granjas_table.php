<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('granjas', function (Blueprint $table) {
            $table->string('dicose', 20)->nullable()->after('codigo');
            $table->unique(['empresa_id', 'dicose']);
        });
    }

    public function down(): void
    {
        Schema::table('granjas', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'dicose']);
            $table->dropColumn('dicose');
        });
    }
};
