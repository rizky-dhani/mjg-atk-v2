# Plan: Transfer Floating Stock to Division

## Phase 1: Logic and Model Updates

- [x] **Task 1: Update AtkFloatingStock Model Logic** e1da280
- [x] **Task 2: Write Unit Tests for Transfer Logic** e1da280

- [ ] **Task: Conductor - User Manual Verification 'Logic and Model Updates' (Protocol in workflow.md)**

## Phase 2: UI Implementation - Table Action

- [ ] **Task 3: Implement Individual Table Action**
  - Add "Transfer to.." action to `AtkFloatingStocksTable.php`.
  - Configure the modal form with Target Division, Quantity, and Notes.
  - Implement the action logic using `AtkFloatingStock` model methods.

- [ ] **Task 4: Write UI Tests for Table Action**
  - Verify the action is visible to authorized users.
  - Verify the modal form fields and validation.
  - Verify successful submission triggers the stock transfer.

- [ ] **Task: Conductor - User Manual Verification 'Table Action' (Protocol in workflow.md)**

## Phase 3: UI Implementation - Bulk Action

- [ ] **Task 5: Implement Bulk Transfer Action**
  - Add "Transfer Selected to.." bulk action to `AtkFloatingStocksTable.php`.
  - Configure the modal form (Target Division and Notes apply to all selected; Quantity might need per-item handling or a simplified bulk distribution).
  - Implement the bulk action logic.

- [ ] **Task 6: Write UI Tests for Bulk Action**
  - Verify the bulk action appears when items are selected.
  - Verify the bulk transfer correctly updates all selected items.

- [ ] **Task: Conductor - User Manual Verification 'Bulk Action' (Protocol in workflow.md)**

## Phase 4: Final Verification and Standards

- [ ] **Task 7: Global Code Quality and Coverage Audit**
  - Run all tests and ensure >99% coverage for new code.
  - Run Laravel Pint for formatting.

- [ ] **Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)**
