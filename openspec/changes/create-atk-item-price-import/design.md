# Design: ATK Item Price Import

## Overview
The import functionality will consist of a Filament Action that triggers a modal with a file upload field and an effective date selector. The processing will be handled by a dedicated Laravel Excel import class.

## Components

### 1. `App\Imports\AtkItemPriceImport`
- Implements `ToCollection`, `WithHeadingRow`, `WithValidation`.
- `headingRow()` returns 7.
- Logic:
    - Track `currentCategoryId` based on section headers (rows where `No` is a letter and `Harga` is empty).
    - For item rows:
        - Find/Create `AtkItem` by name (from `Item Description`).
        - Update item's `unit_of_measure` and `category_id`.
        - Create `AtkItemPrice` with:
            - `item_id`: Item ID.
            - `category_id`: Current Category ID.
            - `unit_price`: From `Harga`.
            - `effective_date`: Passed from the form.
            - `is_active`: `true`.

### 2. `App\Filament\Actions\ImportAtkItemPriceAction`
- A reusable Filament Action class.
- Form fields:
    - `excel_file`: `FileUpload`.
    - `effective_date`: `DatePicker`.
- Action logic:
    - Instantiate `AtkItemPriceImport` with the `effective_date`.
    - Execute `Excel::import()`.
    - Send success/error notifications.

### 3. `App\Filament\Resources\AtkItemPrices\Pages\ListAtkItemPrices`
- Add `ImportAtkItemPriceAction::make()` to `getHeaderActions()`.

## Data Mapping
Based on `List_Harga_ATK.xlsx`:
- **Row 7 Headers:** `No`, `Item Description`, `Merk`, `UOM`, `Harga`.
- `Item Description` -> `AtkItem.name`.
- `UOM` -> `AtkItem.unit_of_measure`.
- `Harga` -> `AtkItemPrice.unit_price`.
- Category rows are identified when `No` is a single character (A-Z) and `Harga` is null.
