<?php

namespace App\Imports;

use App\Models\AtkDivisionStock;
use App\Models\AtkItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AtkDivisionStockImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $divisionId;

    public function __construct($divisionId = null)
    {
        $this->divisionId = $divisionId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Find the item by name, ordered by created_at ascending
            $item = AtkItem::where('name', $row['name'])
                ->orderBy('created_at', 'asc')
                ->first();
            
            if (!$item) {
                // Skip rows where item name doesn't match any existing item
                continue;
            }

            // Find the corresponding AtkDivisionStock record for the selected division
            $atkDivisionStock = AtkDivisionStock::where('item_id', $item->id)
                ->where('division_id', $this->divisionId)
                ->first();

            if ($atkDivisionStock) {
                // Update the current_stock with the quantity from Excel
                $atkDivisionStock->update([
                    'current_stock' => $row['quantity']
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'quantity.required' => 'The quantity field is required.',
            'quantity.integer' => 'The quantity field must be an integer.',
            'quantity.min' => 'The quantity field must be at least 0.',
        ];
    }
}