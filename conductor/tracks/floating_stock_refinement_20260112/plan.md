# Plan: Refine Floating Stock Transaction Types and Add Destination Division

## Phase 1: Database and Model Updates [checkpoint: 1ef5079]

- [x] **Task 1: Create Database Migration**
  - Create a migration to add `destination_division_id` to `atk_floating_stock_trx`.
  - Add foreign key constraint to `user_divisions`.

- [x] **Task 2: Update AtkFloatingStockTransactionHistory Model**
  - Add `destination_division_id` to `$fillable`.
  - Define `destinationDivision` relationship.

- [x] **Task: Conductor - User Manual Verification 'Phase 1' (Protocol in workflow.md)**

## Phase 2: Service and Logic Updates [checkpoint: a6f16d1]

- [x] **Task 3: Update FloatingStockService**
  - Modify `recordTransaction` to accept and store `destination_division_id`.
  - Implement logic to handle `in` (from division) and `out` (to division) correctly based on source/destination parameters.

- [x] **Task 4: Update FloatingStockService Tests**
  - Write failing tests for destination division storage and refined `in`/`out` logic.
  - Implement logic to make tests pass.

- [x] **Task: Conductor - User Manual Verification 'Phase 2' (Protocol in workflow.md)**

## Phase 3: Caller Integration and Refined Status Usage [checkpoint: 72852ea]

- [x] **Task 5: Refine Status Usage in Callers**
  - Review all callers of `recordTransaction`.
  - Ensure transfers *to* floating stock use `in` with `source_division_id`.
  - Ensure distributions *from* floating stock use `out` with `destination_division_id`.

- [x] **Task: Conductor - User Manual Verification 'Phase 3' (Protocol in workflow.md)**

## Phase 4: UI Updates

- [ ] **Task 6: Update Filament Resource Table**
  - Add "Destination Division" column to `AtkFloatingStockTransactionHistoryTable`.
  - Refine column labels for clarity (e.g., "From Division" and "To Division").

- [ ] **Task: Conductor - User Manual Verification 'Phase 4' (Protocol in workflow.md)**
