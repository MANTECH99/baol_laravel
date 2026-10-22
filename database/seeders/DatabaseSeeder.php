<?php

namespace Database\Seeders;

use App\Models\Camion;
use App\Models\Livraison;
use App\Models\StatutCamion;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'is_admin' => true,
        ]);

        $camionsData = [
            ['numero' => 'DK-001', 'telephone' => '+221 77 000 01'],
            ['numero' => 'DK-002', 'telephone' => '+221 77 000 02'],
            ['numero' => 'DK-003', 'telephone' => '+221 77 000 03'],
            ['numero' => 'DK-004', 'telephone' => '+221 77 000 04'],
        ];

        $camions = [];
        foreach ($camionsData as $data) {
            $camions[] = Camion::create($data);
        }

        $today = Carbon::today();
        $statuses = ['En attente', 'En panne', 'Travaille pas'];

        foreach ($camions as $i => $camion) {
            foreach (range(0, 5) as $dayOffset) {
                $date = $today->copy()->subDays($dayOffset);

                StatutCamion::updateOrCreate(
                    ['camion_id' => $camion->id, 'date' => $date->format('Y-m-d')],
                    ['statut' => $statuses[$i % 3]]
                );

                if (rand(0, 2) > 0) {
                    Livraison::create([
                        'date' => $date->format('Y-m-d'),
                        'camion_id' => $camion->id,
                        'tonnage' => rand(300, 1450) / 100,
                        'quantite' => rand(1, 3),
                        'chiffonage' => rand(0, 1) ? 'Oui' : 'Non',
                        'numero_bl' => 'BL-' . str_pad(rand(1, 999), 4, '0', STR_PAD_LEFT),
                        'statut' => 'enregistré',
                        'verification' => rand(0, 1) ? 'verifié' : 'non_verifié',
                    ]);
                }
            }
        }
    }
}
