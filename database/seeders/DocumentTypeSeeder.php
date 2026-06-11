<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void{
    
        $types = [
            ['context' => 'user_verification', 'code' => 'ktp', 'name' => 'Kartu Tanda Penduduk (KTP)'],
            ['context' => 'user_verification', 'code' => 'selfie_ktp', 'name' => 'Foto Selfie dengan KTP'],
            
            ['context' => 'user_verification', 'code' => 'ktm', 'name' => 'Kartu Tanda Mahasiswa (KTM)'],
            ['context' => 'user_verification', 'code' => 'siup', 'name' => 'Surat Izin Usaha Perdagangan (SIUP)'],
            
            ['context' => 'space_registration', 'code' => 'surat_tanah', 'name' => 'Sertifikat Hak Milik / Surat Izin Lahan'],
            ['context' => 'space_registration', 'code' => 'perjanjian_sewa', 'name' => 'Surat Perjanjian Sewa Induk'],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['code' => $type['code']], 
                [
                    'name' => $type['name'],
                    'context' => $type['context'],
                ]
            );
        }

        $this->command->info('Document types seeded successfully!');
    }
}