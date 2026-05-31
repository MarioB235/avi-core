<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('empresa_id')->nullable()->after('id')->constrained('empresas')->nullOnDelete();
            $table->string('documento')->after('name');
            $table->string('rol')->after('password');
            $table->boolean('activo')->default(true)->after('rol');
            $table->boolean('must_change_password')->default(false)->after('activo');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');

            $table->string('email')->nullable()->change();
            $table->dropUnique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique(['empresa_id', 'documento']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['empresa_id', 'documento']);
            $table->dropConstrainedForeignId('empresa_id');
            $table->dropColumn([
                'documento',
                'rol',
                'activo',
                'must_change_password',
                'last_login_at',
            ]);
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
