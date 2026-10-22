<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statut_camions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camion_id')->constrained()->cascadeOnDelete();
            $table->date('date')->nullable();
            $table->string('statut', 20)->default('attente');
            $table->unique(['camion_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statut_camions');
    }
};
