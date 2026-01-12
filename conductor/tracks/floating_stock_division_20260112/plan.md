# Plan: Add Division Source to Floating Stock Transactions

## Phase 1: Database and Model Updates [checkpoint: 3251967]

- [x] **Task 1: Create Database Migration**
  - Create a migration to add `source_division_id` to `atk_floating_stock_trx`.
  - Add foreign key constraint to `user_divisions`.

- [x] **Task 2: Update AtkFloatingStockTransactionHistory Model**
  - Add `source_division_id` to `$fillable`.
  - Define `sourceDivision` relationship.

- [x] **Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md)**

## Phase 2: Service and Logic Updates

- [ ] **Task 3: Update FloatingStockService**
  - Modify `recordTransaction` to accept and store `source_division_id`.
  - Infer `source_division_id` from `transactionSource` if possible.

- [ ] **Task 4: Write Tests for Service**
  - Verify that transactions correctly store the source division.

- [ ] **Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md)**

## Phase 3: UI Updates

- [ ] **Task 5: Update Filament Resource**
  - Update `AtkFloatingStockTransactionHistoryResource` table configuration.
  - Add `sourceDivision.name` column.

- [ ] **Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md)**
