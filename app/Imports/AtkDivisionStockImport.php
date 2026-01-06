<?php

namespace App\Imports;

use App\Models\AtkCategory;
use App\Models\AtkDivisionStock;
use App\Models\AtkFloatingStock;
use App\Models\AtkItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AtkDivisionStockImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $divisionId;
    public int $processedCount = 0;
    public int $skippedCount = 0;

    public function __construct($divisionId = null)
    {
        $this->divisionId = $divisionId;
    }

    /**
     * @param  Collection  $collection
     */
    public function collection(Collection $rows)
    {
        $defaultCategory = AtkCategory::where('name', 'Lain-Lain')->first() ?? AtkCategory::first();

        foreach ($rows as $row) {
            $itemName = isset($row['name']) ? trim($row['name']) : null;
            $unit = isset($row['satuan']) ? trim($row['satuan']) : 'Pcs';
            $notes = isset($row['deskripsi']) ? trim($row['deskripsi']) : null;

            if (!$itemName) {
                $this->skippedCount++;
                continue;
            }

            // Find or create the item
            $item = AtkItem::where('name', $itemName)
                ->orderBy('created_at', 'asc')
                ->first();

            if (! $item) {
                $item = AtkItem::create([
                    'name' => $itemName,
                    'slug' => Str::slug($itemName),
                    'unit_of_measure' => $unit,
                    'notes' => $notes,
                    'category_id' => $defaultCategory->id,
                ]);
            } else {
                // Update unit and notes for existing items if provided in excel
                $item->update([
                    'unit_of_measure' => $unit,
                    'notes' => $notes,
                ]);
            }

            $updated = false;

            // Find or create the corresponding AtkDivisionStock record for the selected division
            $atkDivisionStock = AtkDivisionStock::firstOrCreate(
                [
                    'item_id' => $item->id,
                    'division_id' => $this->divisionId,
                ],
                [
                    'category_id' => $item->category_id,
                    'current_stock' => 0,
                ]
            );

            if ($atkDivisionStock && isset($row['quantity']) && $row['quantity'] !== '') {
                // Update the current_stock with the "Quantity" from Excel
                $atkDivisionStock->update([
                    'current_stock' => $row['quantity'],
                ]);
                $updated = true;
            }

            // Find or create the corresponding AtkFloatingStock record
            $atkFloatingStock = AtkFloatingStock::firstOrCreate(
                [
                    'item_id' => $item->id,
                ],
                [
                    'category_id' => $item->category_id,
                    'current_stock' => 0,
                ]
            );

            if ($atkFloatingStock && isset($row['stok_umum']) && $row['stok_umum'] !== '') {
                // Update the current_stock with the "Stok Umum" from Excel
                $atkFloatingStock->update([
                    'current_stock' => $row['stok_umum'],
                ]);
                $updated = true;
            }

            if ($updated) {
                $this->processedCount++;
            } else {
                $this->skippedCount++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'stok_umum' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'quantity.integer' => 'The Quantity field must be an integer.',
            'quantity.min' => 'The Quantity field must be at least 0.',
            'stok_umum.integer' => 'The Stok Umum field must be an integer.',
            'stok_umum.min' => 'The Stok Umum field must be at least 0.',
            'satuan.string' => 'The Satuan field must be a string.',
            'deskripsi.string' => 'The Deskripsi field must be a string.',
        ];
    }
}
