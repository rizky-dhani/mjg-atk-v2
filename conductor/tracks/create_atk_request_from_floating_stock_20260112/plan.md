# Plan: Create AtkRequestFromFloatingStock Resource

## Phase 1: Model and Migration Setup [checkpoint: 8a16f6b]

- [x] **Task 1: Create Migrations** fa7d2ac
  - Create migrations for `atk_requests_from_floating_stock` and `atk_requests_from_floating_stock_items`.
  - Include necessary foreign keys and indexes.

- [x] **Task 2: Create Models** 4800df1
  - Create `AtkRequestFromFloatingStock` and `AtkRequestFromFloatingStockItem` models.
  - Define relationships and fillable fields.
  - Integrate with `StockRequestModelTrait` if applicable or create a specialized trait.

- [x] **Task 3: Write Unit Tests for Request Logic** 0c934ad
  - Verify model creation and relationships.
  - Verify validation logic (e.g., quantity check against floating stock).

- [ ] **Task: Conductor - User Manual Verification 'Model Setup' (Protocol in workflow.md)**

## Phase 2: Logic and Approval Integration [checkpoint: 433ab59]

- [x] **Task 4: Integrate with Approval System** c9f6c45
  - Register the new model in the approval flow configuration.
  - Implement logic to trigger stock transfer upon `final_approved` status.

- [x] **Task 5: Write Tests for Approval and Stock Movement** 11dba9c
  - Verify that final approval triggers `AtkFloatingStock::distributeBulkToDivision`.
  - Verify transaction histories are recorded correctly.

- [ ] **Task: Conductor - User Manual Verification 'Approval Integration' (Protocol in workflow.md)**

## Phase 3: UI Implementation - Filament Resource

- [x] **Task 6: Create AtkRequestFromFloatingStockResource** 93a7ba5
  - Generate the resource using Artisan.
  - Configure the Form with a repeater for items.
  - Configure the Table with status and requester information.

- [x] **Task 7: Write UI Tests for Filament Resource** 21d3463
  - Verify form submission and validation.
  - Verify approval actions are visible and functional.

- [ ] **Task: Conductor - User Manual Verification 'UI Implementation' (Protocol in workflow.md)**

## Phase 4: Final Verification and Standards

- [ ] **Task 8: Global Code Quality Audit**
  - Run all tests and ensure high coverage.
  - Run Laravel Pint for formatting.

- [ ] **Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)**
