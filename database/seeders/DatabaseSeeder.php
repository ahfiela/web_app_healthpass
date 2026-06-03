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

        // 2. SEED 1 HOSPITAL ADMIN (RS-UMMI)
        $hospital = HospitalAdmin::updateOrCreate(
            ['kode_rs' => 'RS-UMMI'],
            [
                'nama_rs' => 'RS UMMI BOGOR',
                'name' => 'Petugas RS UMMI',
                'email' => 'admin@ummi.com',
                'password' => Hash::make('password'),
            ]
        );

        // 3. 100 DATA MASTER PENYAKIT (50 Kritis, 50 Non-Kritis) & MATCHING MEDICATIONS
        $criticalBase = [
            [
                'code' => 'I21', 
                'name' => 'Acute Myocardial Infarction (Serangan Jantung Koroner)', 
                'meds' => [
                    ['name' => 'Nitroglycerin', 'type' => 'Tablet Sublingual', 'base_dose' => 0.5],
                    ['name' => 'Clopidogrel', 'type' => 'Tablet', 'base_dose' => 75]
                ]
            ],
            [
                'code' => 'B20', 
                'name' => 'HIV/AIDS Disease', 
                'meds' => [
                    ['name' => 'Tenofovir', 'type' => 'Tablet', 'base_dose' => 300],
                    ['name' => 'Lamivudine', 'type' => 'Tablet', 'base_dose' => 150]
                ]
            ],
            [
                'code' => 'C34', 
                'name' => 'Malignant Neoplasm of Lung (Kanker Paru)', 
                'meds' => [
                    ['name' => 'Cisplatin Injection', 'type' => 'Vial', 'base_dose' => 50],
                    ['name' => 'Gemcitabine', 'type' => 'Vial', 'base_dose' => 200]
                ]
            ],
            [
                'code' => 'N18', 
                'name' => 'Chronic Kidney Disease (Gagal Ginjal Kronis)', 
                'meds' => [
                    ['name' => 'Furosemide', 'type' => 'Tablet', 'base_dose' => 40],
                    ['name' => 'Calcium Carbonate', 'type' => 'Tablet', 'base_dose' => 500]
                ]
            ],
            [
                'code' => 'I63', 
                'name' => 'Cerebral Infarction (Stroke Akut)', 
                'meds' => [
                    ['name' => 'Citicoline', 'type' => 'Tablet', 'base_dose' => 500],
                    ['name' => 'Aspirin', 'type' => 'Tablet', 'base_dose' => 80]
                ]
            ],
            [
                'code' => 'J44', 
                'name' => 'Chronic Obstructive Pulmonary Disease (PPOK Kritis)', 
                'meds' => [
                    ['name' => 'Salbutamol Inhaler', 'type' => 'Inhaler', 'base_dose' => 100],
                    ['name' => 'Budesonide Inhaler', 'type' => 'Inhaler', 'base_dose' => 200]
                ]
            ],
            [
                'code' => 'B18', 
                'name' => 'Chronic Viral Hepatitis B', 
                'meds' => [
                    ['name' => 'Tenofovir Alafenamide', 'type' => 'Tablet', 'base_dose' => 25],
                    ['name' => 'Entecavir', 'type' => 'Tablet', 'base_dose' => 0.5]
                ]
            ],
            [
                'code' => 'A15', 
                'name' => 'Respiratory Tuberculosis (TBC Paru Kritis)', 
                'meds' => [
                    ['name' => 'Rifampicin', 'type' => 'Kapsul', 'base_dose' => 450],
                    ['name' => 'Isoniazid', 'type' => 'Tablet', 'base_dose' => 300]
                ]
            ],
            [
                'code' => 'C50', 
                'name' => 'Malignant Neoplasm of Breast (Kanker Payudara)', 
                'meds' => [
                    ['name' => 'Tamoxifen', 'type' => 'Tablet', 'base_dose' => 20],
                    ['name' => 'Doxorubicin Injection', 'type' => 'Vial', 'base_dose' => 50]
                ]
            ],
            [
                'code' => 'I05', 
                'name' => 'Mitral Valve Disease (Kelainan Katup Jantung)', 
                'meds' => [
                    ['name' => 'Warfarin', 'type' => 'Tablet', 'base_dose' => 2],
                    ['name' => 'Digoxin', 'type' => 'Tablet', 'base_dose' => 0.25]
                ]
            ]
        ];
 
        $nonCriticalBase = [
            [
                'code' => 'J00', 
                'name' => 'Acute Nasopharyngitis (Common Cold / Flu)', 
                'meds' => [
                    ['name' => 'Paracetamol', 'type' => 'Tablet', 'base_dose' => 500],
                    ['name' => 'Pseudoephedrine', 'type' => 'Tablet', 'base_dose' => 30]
                ]
            ],
            [
                'code' => 'K29', 
                'name' => 'Gastritis and Duodenitis (Maag Lambung)', 
                'meds' => [
                    ['name' => 'Omeprazole', 'type' => 'Kapsul', 'base_dose' => 20],
                    ['name' => 'Antasida Doen', 'type' => 'Tablet Kunyah', 'base_dose' => 200]
                ]
            ],
            [
                'code' => 'I10', 
                'name' => 'Essential Primary Hypertension (Darah Tinggi Ringan)', 
                'meds' => [
                    ['name' => 'Amlodipine', 'type' => 'Tablet', 'base_dose' => 5],
                    ['name' => 'Captopril', 'type' => 'Tablet', 'base_dose' => 25]
                ]
            ],
            [
                'code' => 'E11', 
                'name' => 'Type 2 Diabetes Mellitus (Kencing Manis Mandiri)', 
                'meds' => [
                    ['name' => 'Metformin', 'type' => 'Tablet', 'base_dose' => 500],
                    ['name' => 'Glimepiride', 'type' => 'Tablet', 'base_dose' => 2]
                ]
            ],
            [
                'code' => 'L20', 
                'name' => 'Atopic Dermatitis (Gatal Eksim Kulit)', 
                'meds' => [
                    ['name' => 'Hydrocortisone Cream 2.5%', 'type' => 'Salep', 'base_dose' => 5],
                    ['name' => 'Cetirizine', 'type' => 'Tablet', 'base_dose' => 10]
                ]
            ],
            [
                'code' => 'M79', 
                'name' => 'Myalgia (Pegal Linu Nyeri Otot)', 
                'meds' => [
                    ['name' => 'Ibuprofen', 'type' => 'Tablet', 'base_dose' => 400],
                    ['name' => 'Meloxicam', 'type' => 'Tablet', 'base_dose' => 7.5]
                ]
            ],
            [
                'code' => 'H10', 
                'name' => 'Acute Conjunctivitis (Mata Merah Radang)', 
                'meds' => [
                    ['name' => 'Chloramphenicol Eye Drops 0.5%', 'type' => 'Tetes Mata', 'base_dose' => 5],
                    ['name' => 'Tetrahydrozoline Eye Drops', 'type' => 'Tetes Mata', 'base_dose' => 10]
                ]
            ],
            [
                'code' => 'J02', 
                'name' => 'Acute Pharyngitis (Radang Tenggorokan)', 
                'meds' => [
                    ['name' => 'Amoxicillin', 'type' => 'Tablet', 'base_dose' => 500],
                    ['name' => 'FG Troches', 'type' => 'Tablet Hisap', 'base_dose' => 1]
                ]
            ],
            [
                'code' => 'K30', 
                'name' => 'Functional Dyspepsia (Kembung Mual)', 
                'meds' => [
                    ['name' => 'Simethicone', 'type' => 'Tablet', 'base_dose' => 80],
                    ['name' => 'Domperidone', 'type' => 'Tablet', 'base_dose' => 10]
                ]
            ],
            [
                'code' => 'M10', 
                'name' => 'Gouty Arthritis (Penyakit Asam Urat)', 
                'meds' => [
                    ['name' => 'Allopurinol', 'type' => 'Tablet', 'base_dose' => 100],
                    ['name' => 'Piroxicam', 'type' => 'Tablet', 'base_dose' => 20]
                ]
            ]
        ];
 
        // Clear old medications and diseases first to avoid duplicates or leftovers
        Medication::truncate();
        Disease::truncate();

        // Generate exactly 50 critical diseases (ICD codes I21.1 to I05.5) and their medications
        for ($i = 0; $i < 50; $i++) {
            $base = $criticalBase[$i % 10];
            $index = intval($i / 10) + 1;
            $code = $base['code'] . '.' . $index;
            $name = $base['name'] . ' Subtype ' . chr(65 + $i % 10) . $index;
            
            Disease::create([
                'kode_rs' => 'RS-UMMI',
                'icd_code' => $code,
                'name' => $name,
                'is_critical' => 1,
                'description' => 'Kategori kritis tingkat ' . $index . ' untuk ' . $name
            ]);

            // Seed matching medications for this specific subtype
            foreach ($base['meds'] as $med) {
                $dosage = $med['base_dose'] * $index;
                $unit = ($med['type'] === 'Salep' || $med['type'] === 'Tetes Mata') ? ' gr/ml' : ' mg';
                $medName = $med['name'] . ' ' . $dosage . $unit;
                
                Medication::updateOrCreate(
                    ['kode_rs' => 'RS-UMMI', 'name' => $medName],
                    [
                        'type' => $med['type'],
                        'stock' => rand(50, 250)
                    ]
                );
            }
        }
 
        // Generate exactly 50 non-critical diseases (ICD codes J00.1 to M10.5) and their medications
        for ($i = 0; $i < 50; $i++) {
            $base = $nonCriticalBase[$i % 10];
            $index = intval($i / 10) + 1;
            $code = $base['code'] . '.' . $index;
            $name = $base['name'] . ' Subtype ' . chr(65 + $i % 10) . $index;
            
            Disease::create([
                'kode_rs' => 'RS-UMMI',
                'icd_code' => $code,
                'name' => $name,
                'is_critical' => 0,
                'description' => 'Kategori non-kritis tingkat ' . $index . ' untuk ' . $name
            ]);

            // Seed matching medications for this specific subtype
            foreach ($base['meds'] as $med) {
                $dosage = $med['base_dose'] * $index;
                $unit = ($med['type'] === 'Salep' || $med['type'] === 'Tetes Mata') ? ' gr/ml' : ' mg';
                $medName = $med['name'] . ' ' . $dosage . $unit;
                
                Medication::updateOrCreate(
                    ['kode_rs' => 'RS-UMMI', 'name' => $medName],
                    [
                        'type' => $med['type'],
                        'stock' => rand(50, 250)
                    ]
                );
            }
        }
 
        // 4. SEED DOCTOR & ROOM FOR RS-UMMI
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
 
        // 5. SEED PATIENT USER & HEALTH PROFILE
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