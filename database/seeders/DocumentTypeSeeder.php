<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // User Identity Documents
            [
                'code' => 'ktp',
                'name' => 'Kartu Tanda Penduduk (KTP)',
                'context' => 'user',
            ],
            [
                'code' => 'selfie_ktp',
                'name' => 'Foto Selfie dengan KTP',
                'context' => 'user',
            ],

            // Space Legal Documents
            [
                'code' => 'surat_tanah',
                'name' => 'Surat Bukti Kepemilikan Tanah / Bangunan',
                'context' => 'space',
            ],
            [
                'code' => 'surat_izin',
                'name' => 'Surat Izin Usaha / Keramaian',
                'context' => 'space',
            ],
            [
                'code' => 'perjanjian_sewa',
                'name' => 'Surat Perjanjian Sewa (Jika pihak ketiga)',
                'context' => 'space',
            ],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['code' => $type['code']], // Prevent duplicates if run multiple times
                [
                    'name' => $type['name'],
                    'context' => $type['context'],
                ]
            );
        }

        $this->command->info('Document types seeded successfully!');
    }
}