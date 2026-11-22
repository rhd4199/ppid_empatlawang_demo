<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('officials', function (Blueprint $table) {
            // pemkab | forkopimda | dprd | kecamatan (Official::GROUPS): sections on /profil-pejabat
            $table->string('group', 20)->default('pemkab')->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('officials', fn (Blueprint $table) => $table->dropColumn('group'));
    }
};
