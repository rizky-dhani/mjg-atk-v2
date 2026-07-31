<?php

namespace App\Exports;

use App\Models\UserDivision;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserDivisionExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function collection(): Collection
    {
        return UserDivision::query()
            ->orderBy('name')
            ->get(['name', 'initial']);
    }

    public function headings(): array
    {
        return [
            'Name',
            'Initial',
        ];
    }
}
