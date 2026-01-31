<?php

namespace App\Imports;

use App\Models\AtkCategory;
use App\Models\AtkItem;
use App\Models\AtkItemPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AtkItemPriceImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    protected $effectiveDate;

    protected $currentCategoryId;

    public int $processedCount = 0;

    public int $skippedCount = 0;

    public function __construct(string $effectiveDate)
    {
        $this->effectiveDate = $effectiveDate;
    }

    public function headingRow(): int
    {
        return 7;
    }

    public function collection(Collection $rows)
    {
        $defaultCategory = AtkCategory::where('name', 'Lain-Lain')->first() ?? AtkCategory::first();
        $this->currentCategoryId = $defaultCategory?->id;

        foreach ($rows as $row) {
            $no = isset($row['no']) ? trim((string) $row['no']) : null;
            $itemDescription = isset($row['item_description']) ? trim($row['item_description']) : null;
            $uom = isset($row['uom']) ? trim($row['uom']) : null;
            $price = isset($row['harga']) ? $row['harga'] : null;

            // Clean price: treat non-numeric values (like '-') as null
            if ($price !== null && ! is_numeric($price)) {
                $price = null;
            }

            // Skip header rows or placeholder rows
            if ($itemDescription && str_contains(strtolower($itemDescription), 'item description')) {
                continue;
            }

            // Check if it's a category row
            // Category rows usually have no price
            if ($itemDescription && $price === null) {
                $category = AtkCategory::where('name', 'like', '%'.$itemDescription.'%')->first();
                if ($category) {
                    $this->currentCategoryId = $category->id;
                }

                continue;
            }

            // If no item name or no price, skip
            if (! $itemDescription || $price === null) {
                $this->skippedCount++;

                continue;
            }

            // Find or create the item
            $item = AtkItem::where('name', $itemDescription)
                ->orderBy('created_at', 'asc')
                ->first();

            if (! $item) {
                $item = AtkItem::create([
                    'name' => $itemDescription,
                    'slug' => Str::slug($itemDescription),
                    'unit_of_measure' => $uom ?: 'Pcs',
                    'category_id' => $this->currentCategoryId ?: $defaultCategory?->id,
                ]);
            } else {
                // Update UOM and category if provided
                $item->update([
                    'unit_of_measure' => $uom ?: $item->unit_of_measure,
                    'category_id' => $this->currentCategoryId ?: $item->category_id,
                ]);
            }

            // Create the new price
            AtkItemPrice::create([
                'item_id' => $item->id,
                'category_id' => $item->category_id,
                'unit_price' => $price,
                'effective_date' => $this->effectiveDate,
                'is_active' => true, // Creating it as active will trigger deactivation of others in boot()
            ]);

            $this->processedCount++;
        }
    }

    public function rules(): array
    {
        return [
            'no' => 'nullable',
            'item_description' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'harga' => 'nullable',
        ];
    }
}
