<?php

namespace Database\Seeders;

use App\Models\AtkCategory;
use Illuminate\Database\Seeder;

class AtkCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AtkCategory::insert([
            ['name' => 'Kertas'],
            ['name' => 'Odner'],
            ['name' => 'Buku dan Kwitansi'],
            ['name' => 'Pulpen, Pensil, Stabilo'],
            ['name' => 'Binder Clip'],
            ['name' => 'Label T&J'],
            ['name' => 'Lain-Lain'],
            ['name' => 'Kop Surat'],
            ['name' => 'Tambahan'],
        ]);
    }
}
