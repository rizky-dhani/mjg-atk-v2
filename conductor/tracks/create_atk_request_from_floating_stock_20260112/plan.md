# Plan: Create AtkRequestFromFloatingStock Resource

## Phase 1: Model and Migration Setup

- [x] **Task 1: Create Migrations** fa7d2ac
  - Create migrations for `atk_requests_from_floating_stock` and `atk_requests_from_floating_stock_items`.
  - Include necessary foreign keys and indexes.

- [ ] **Task 2: Create Models**
  - Create `AtkRequestFromFloatingStock` and `AtkRequestFromFloatingStockItem` models.
  - Define relationships and fillable fields.
  - Integrate with `StockRequestModelTrait` if applicable or create a specialized trait.

- [ ] **Task 3: Write Unit Tests for Request Logic**
  - Verify model creation and relationships.
  - Verify validation logic (e.g., quantity check against floating stock).

- [ ] **Task: Conductor - User Manual Verification 'Model Setup' (Protocol in workflow.md)**

## Phase 2: Logic and Approval Integration

- [ ] **Task 4: Integrate with Approval System**
  - Register the new model in the approval flow configuration.
  - Implement logic to trigger stock transfer upon `final_approved` status.

- [ ] **Task 5: Write Tests for Approval and Stock Movement**
  - Verify that final approval triggers `AtkFloatingStock::distributeBulkToDivision`.
  - Verify transaction histories are recorded correctly.

- [ ] **Task: Conductor - User Manual Verification 'Approval Integration' (Protocol in workflow.md)**

## Phase 3: UI Implementation - Filament Resource

- [ ] **Task 6: Create AtkRequestFromFloatingStockResource**
  - Generate the resource using Artisan.
  - Configure the Form with a repeater for items.
  - Configure the Table with status and requester information.

- [ ] **Task 7: Write UI Tests for Filament Resource**
  - Verify form submission and validation.
  - Verify approval actions are visible and functional.

- [ ] **Task: Conductor - User Manual Verification 'UI Implementation' (Protocol in workflow.md)**

## Phase 4: Final Verification and Standards

- [ ] **Task 8: Global Code Quality Audit**
  - Run all tests and ensure high coverage.
  - Run Laravel Pint for formatting.

- [ ] **Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)**
