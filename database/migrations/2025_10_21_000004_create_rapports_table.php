<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->date('mois');
            $table->string('fichier');
            $table->timestamp('date_ajout')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
