<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livraisons', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('camion_id')->constrained()->cascadeOnDelete();
            $table->decimal('tonnage', 5, 2);
            $table->integer('prix_unitaire')->default(0);
            $table->integer('quantite')->default(1);
            $table->integer('montant')->default(0);
            $table->string('chiffonage', 255)->nullable();
            $table->string('numero_bl', 50)->nullable();
            $table->string('statut', 20)->default('enregistré');
            $table->string('verification', 20)->default('non_verifié');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livraisons');
    }
};
