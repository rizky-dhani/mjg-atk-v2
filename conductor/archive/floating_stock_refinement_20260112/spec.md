# Specification: Refine Floating Stock Transaction Types and Add Destination Division

## 1. Overview
This track refines the usage of transaction types in `AtkFloatingStockTransactionHistory` and adds a `destination_division_id` column to provide a complete "From/To" visibility for items moving in and out of the floating stock.

## 2. Functional Requirements
- **Refined Types:**
    - Use `in` for all transactions where items enter the floating stock (e.g., transfers from a division to general/floating stock).
    - Use `out` for all transactions where items leave the floating stock (e.g., distributions from general/floating stock to a division).
- **Division Tracking:**
    - Add `destination_division_id` column to the `atk_floating_stock_trx` table.
    - **Logic for `in` (Incoming):** `source_division_id` stores the originating division; `destination_division_id` is NULL (representing the floating stock pool).
    - **Logic for `out` (Outgoing):** `source_division_id` is NULL; `destination_division_id` stores the target division receiving the items.
- **Dashboard Visibility:** Display both source and destination divisions in the Filament transaction history table.

## 3. Technical Requirements
- **Database:** Migration to add `destination_division_id` (foreignId to `user_divisions`, nullable) to `atk_floating_stock_trx`.
- **Model:**
    - Add `destination_division_id` to `$fillable` in `AtkFloatingStockTransactionHistory`.
    - Define `destinationDivision` relationship to `UserDivision`.
- **Service:** Update `FloatingStockService::recordTransaction` to support the new column and ensure the refined type usage is enforced in callers.
- **UI:** Update `AtkFloatingStockTransactionHistoryTable` to include the "Destination Division" column.

## 4. Acceptance Criteria
- Transactions moving items *to* floating stock are marked as `in` with a `source_division_id`.
- Transactions moving items *from* floating stock are marked as `out` with a `destination_division_id`.
- Both columns are visible and searchable in the Filament dashboard.
