<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Disease;
use App\Models\Disability;
use App\Models\HospitalAdmin;
use App\Models\Medication;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\User;
use App\Models\HealthProfile;
use Illuminate\Support\Facades\Hash;
 
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
 
        // 2. 100 DATA MASTER PENYAKIT (50 Kritis, 50 Non-Kritis)
        $criticalBase = [
            ['code' => 'I21', 'name' => 'Acute Myocardial Infarction (Serangan Jantung Koroner)'],
            ['code' => 'B20', 'name' => 'HIV/AIDS Disease'],
            ['code' => 'C34', 'name' => 'Malignant Neoplasm of Bronchus and Lung (Kanker Paru)'],
            ['code' => 'N18', 'name' => 'Chronic Kidney Disease (Gagal Ginjal Kronis)'],
            ['code' => 'I63', 'name' => 'Cerebral Infarction (Stroke Akut)'],
            ['code' => 'J44', 'name' => 'Chronic Obstructive Pulmonary Disease (PPOK)'],
            ['code' => 'B18', 'name' => 'Chronic Viral Hepatitis B/C'],
            ['code' => 'A15', 'name' => 'Respiratory Tuberculosis (TBC Paru Kritis)'],
            ['code' => 'C50', 'name' => 'Malignant Neoplasm of Breast (Kanker Payudara)'],
            ['code' => 'I05', 'name' => 'Mitral Valve Disease (Kelainan Katup Jantung Mitral)'],
        ];
 
        $nonCriticalBase = [
            ['code' => 'J00', 'name' => 'Acute Nasopharyngitis (Common Cold / Flu Batuk)'],
            ['code' => 'K29', 'name' => 'Gastritis and Duodenitis (Maag Lambung)'],
            ['code' => 'I10', 'name' => 'Essential Primary Hypertension (Darah Tinggi Ringan)'],
            ['code' => 'E11', 'name' => 'Type 2 Diabetes Mellitus (Kencing Manis Mandiri)'],
            ['code' => 'L20', 'name' => 'Atopic Dermatitis (Gatal Eksim Kulit)'],
            ['code' => 'M79', 'name' => 'Myalgia / Pegal Linu Nyeri Otot'],
            ['code' => 'H10', 'name' => 'Acute Conjunctivitis (Mata Merah Radang)'],
            ['code' => 'J02', 'name' => 'Acute Pharyngitis (Radang Tenggorokan)'],
            ['code' => 'K30', 'name' => 'Functional Dyspepsia (Kembung Mual)'],
            ['code' => 'M10', 'name' => 'Gouty Arthritis (Penyakit Asam Urat)'],
        ];
 
        // Generate exactly 50 critical diseases (ICD codes I21.1 to I05.5)
        for ($i = 0; $i < 50; $i++) {
            $base = $criticalBase[$i % 10];
            $index = intval($i / 10) + 1;
            $code = $base['code'] . '.' . $index;
            $name = $base['name'] . ' Subtype ' . chr(65 + $i % 10) . $index;
            Disease::updateOrCreate(
                ['icd_code' => $code],
                [
                    'name' => $name,
                    'is_critical' => 1,
                    'description' => 'Kategori kritis untuk ' . $name
                ]
            );
        }
 
        // Generate exactly 50 non-critical diseases (ICD codes J00.1 to M10.5)
        for ($i = 0; $i < 50; $i++) {
            $base = $nonCriticalBase[$i % 10];
            $index = intval($i / 10) + 1;
            $code = $base['code'] . '.' . $index;
            $name = $base['name'] . ' Subtype ' . chr(65 + $i % 10) . $index;
            Disease::updateOrCreate(
                ['icd_code' => $code],
                [
                    'name' => $name,
                    'is_critical' => 0,
                    'description' => 'Kategori non-kritis untuk ' . $name
                ]
            );
        }
 
        // 3. SEED 1 HOSPITAL ADMIN (RS-UMMI)
        $hospital = HospitalAdmin::updateOrCreate(
            ['kode_rs' => 'RS-UMMI'],
            [
                'nama_rs' => 'RS UMMI BOGOR',
                'name' => 'Petugas RS UMMI',
                'email' => 'admin@ummi.com',
                'password' => Hash::make('password'),
            ]
        );
 
        // 4. 100 DATA MASTER OBAT (RS-UMMI)
        $medicationBase = [
            ['name' => 'Paracetamol', 'type' => 'Tablet'],
            ['name' => 'Amoxicillin', 'type' => 'Kapsul'],
            ['name' => 'Ibuprofen', 'type' => 'Tablet'],
            ['name' => 'Metformin', 'type' => 'Tablet'],
            ['name' => 'Amlodipine', 'type' => 'Tablet'],
            ['name' => 'Cetirizine', 'type' => 'Tablet'],
            ['name' => 'Omeprazole', 'type' => 'Kapsul'],
            ['name' => 'Ranitidine', 'type' => 'Tablet'],
            ['name' => 'Atorvastatin', 'type' => 'Tablet'],
            ['name' => 'Salbutamol', 'type' => 'Sirup'],
        ];
 
        for ($i = 0; $i < 100; $i++) {
            $base = $medicationBase[$i % 10];
            $index = intval($i / 10) + 1;
            $dosage = 50 * $index;
            $name = $base['name'] . ' ' . $dosage . ' mg';
            Medication::create([
                'kode_rs' => 'RS-UMMI',
                'name' => $name,
                'type' => $base['type'],
                'stock' => rand(50, 200)
            ]);
        }
 
        // 5. SEED DOCTOR & ROOM FOR RS-UMMI
        Doctor::updateOrCreate(
            ['nip' => 'NIP-001'],
            [
                'kode_rs' => 'RS-UMMI',
                'name' => 'Dr. Budi H. S.',
                'specialist' => 'Spesialis Penyakit Dalam',
                'is_active' => true,
            ]
        );
 
        Room::updateOrCreate(
            ['room_code' => 'R-01'],
            [
                'kode_rs' => 'RS-UMMI',
                'name' => 'Poli Penyakit Dalam',
            ]
        );
 
        // 6. SEED PATIENT USER & HEALTH PROFILE
        $user = User::updateOrCreate(
            ['no_bpjs' => '0001234567890'],
            [
                'username' => 'Ahmad F. Ahla',
                'email' => 'ahmad@gmail.com',
                'password' => Hash::make('password'),
                'born' => '2000-01-01',
                'gender' => 'male',
            ]
        );
 
        HealthProfile::updateOrCreate(
            ['no_bpjs' => '0001234567890'],
            [
                'blood_type' => 'O',
                'height_cm' => 170.0,
                'weight_kg' => 65.0,
                'drug_allergies' => 'Tidak ada',
                'food_allergies' => 'Tidak ada',
                'operation_history' => 'Tidak ada',
                'emergency_contact_name' => 'Ibu Ahmad',
                'emergency_contact_phone' => '081234567890',
                'health_status' => 'sehat',
            ]
        );
    }
}