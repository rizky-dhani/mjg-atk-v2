# Specification: Add Division Source to Floating Stock Transactions

## 1. Overview
Currently, `AtkFloatingStockTransactionHistory` tracks transactions but doesn't explicitly store which division an item is coming from (or going to). This feature adds a `source_division_id` column to provide better visibility.

## 2. Functional Requirements
- Store the division ID associated with each floating stock transaction.
- Display the source division in the Filament dashboard for floating stock transaction history.

## 3. Technical Requirements
- Database migration to add `source_division_id` to `atk_floating_stock_trx` table.
- Update `AtkFloatingStockTransactionHistory` model:
    - Add `source_division_id` to `$fillable`.
    - Define `sourceDivision` relationship to `UserDivision`.
- Update `FloatingStockService::recordTransaction` to populate `source_division_id` from the `transactionSource` if available.
- Update Filament Resource `AtkFloatingStockTransactionHistoryResource` to include the new column in the table.

## 4. Testing
- Unit test for `FloatingStockService` to ensure `source_division_id` is saved.
- Feature test for Filament table to ensure the column is visible.
