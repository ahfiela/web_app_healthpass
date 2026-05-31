<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Disease;
use App\Models\Disability;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ISI DATA MASTER KEKURANGAN / KELAINAN
        $disabilities = [
            ['code' => 'KLN-01', 'name' => 'Buta Warna Parsial / Total'],
            ['code' => 'KLN-02', 'name' => 'Mata Minus / Silinder (> 2)'],
            ['code' => 'KLN-03', 'name' => 'Tuli / Gangguan Pendengaran'],
            ['code' => 'KLN-04', 'name' => 'Cacat Fisik / Amputasi Gerak'],
            ['code' => 'KLN-05', 'name' => 'Asma / Riwayat Sesak Nafas Kronis'],
        ];

        foreach ($disabilities as $item) {
            Disability::updateOrCreate(['code' => $item['code']], $item);
        }

        // 2. ISI DATA MASTER PENYAKIT (CONTOH ICD-10)
        $diseases = [
            ['icd_code' => 'A15', 'name' => 'Respiratory tuberculosis (TBC)'],
            ['icd_code' => 'I21', 'name' => 'Acute myocardial infarction (Jantung Koroner)'],
            ['icd_code' => 'E11', 'name' => 'Type 2 diabetes mellitus (Kencing Manis)'],
            ['icd_code' => 'I10', 'name' => 'Essential (primary) hypertension (Darah Tinggi)'],
            ['icd_code' => 'B20', 'name' => 'Human immunodeficiency virus (HIV) disease'],
        ];

        foreach ($diseases as $item) {
            Disease::updateOrCreate(['icd_code' => $item['icd_code']], $item);
        }
    }
}