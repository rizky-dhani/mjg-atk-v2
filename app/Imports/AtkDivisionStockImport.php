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

    protected $currentCategoryId;

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
        $this->currentCategoryId = $defaultCategory?->id;

        foreach ($rows as $row) {
            $no = isset($row['no']) ? trim((string) $row['no']) : null;
            $itemDescription = isset($row['item_description']) ? trim($row['item_description']) : null;
            $uom = isset($row['uom']) ? trim($row['uom']) : null;

            // Check if it's a category row (A, B, C...)
            if ($no && preg_match('/^[A-Z]$/', $no) && $itemDescription && ! $uom) {
                $category = AtkCategory::where('name', 'like', '%'.$itemDescription.'%')->first();
                if ($category) {
                    $this->currentCategoryId = $category->id;
                }

                continue;
            }

            // Map fields, supporting both old and new formats
            $itemName = $itemDescription ?: (isset($row['name']) ? trim($row['name']) : null);

            // If still no item name, or it's a category row we already handled/skipped
            if (! $itemName || ($no && preg_match('/^[A-Z]$/', $no))) {
                continue;
            }

            $unit = $uom ?: (isset($row['satuan']) ? trim($row['satuan']) : 'Pcs');
            $notes = isset($row['deskripsi']) ? trim($row['deskripsi']) : null;

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
                    'category_id' => $this->currentCategoryId ?: $defaultCategory->id,
                ]);
            } else {
                // Update unit and notes for existing items if provided
                $item->update([
                    'unit_of_measure' => $unit,
                    'notes' => $notes,
                    'category_id' => $this->currentCategoryId ?: $item->category_id,
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
                // Update the current_stock with the "Quantity" from Excel if present
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
                // Update the current_stock with the "Stok Umum" from Excel if present
                $atkFloatingStock->update([
                    'current_stock' => $row['stok_umum'],
                ]);
                $updated = true;
            }

            // Even if no stock was updated, we count it as processed if the item was created/found
            $this->processedCount++;
        }
    }

    public function rules(): array
    {
        return [
            'no' => 'nullable',
            'item_description' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'name' => 'nullable|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'stok_umum' => 'nullable|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'item_description.string' => 'The Item Description field must be a string.',
            'name.string' => 'The name field must be a string.',
            'quantity.integer' => 'The Quantity field must be an integer.',
            'quantity.min' => 'The Quantity field must be at least 0.',
            'stok_umum.integer' => 'The Stok Umum field must be an integer.',
            'stok_umum.min' => 'The Stok Umum field must be at least 0.',
        ];
    }
}
