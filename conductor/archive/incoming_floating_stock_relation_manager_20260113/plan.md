# Implementation Plan - Floating Stock Incoming Transactions Relation Manager

This plan outlines the steps to add a relation manager to `AtkDivisionStock` that displays incoming transactions from Floating Stock requests.

## Phase 1: Model Preparation
Prepare the Eloquent model to support the new relationship.

- [x] Task: Update `AtkDivisionStock` model with `incomingFloatingStockRequests()` relationship. bdfce22
    - Sub-task: Use `HasMany` to `AtkRequestFromFloatingStockItem`.
    - Sub-task: Apply constraints for matching `item_id` and parent request `division_id`.
- [x] Task: Conductor - User Manual Verification 'Phase 1: Model Preparation' (Protocol in workflow.md)

## Phase 2: Relation Manager Implementation
Create the Filament component to display the transactions.

- [x] Task: Generate `FloatingStockRequestsRelationManager` using Filament artisan command. ff0c62d
- [x] Task: Configure the relation manager table columns (Request Number, Quantity, Status, Date). ff0c62d
- [x] Task: Implement the "View" action to link to the original request. ff0c62d
- [x] Task: Register the relation manager in `AtkDivisionStockResource`. ff0c62d
- [x] Task: Conductor - User Manual Verification 'Phase 2: Relation Manager Implementation' (Protocol in workflow.md)

## Phase 3: Final Refinement and Standards
Ensure quality and consistency.

- [x] Task: Verify filtering logic in the UI (ensure only correct items/divisions are shown). d0bfe29
- [x] Task: Run `vendor/bin/pint` to ensure code style compliance. d0bfe29
- [x] Task: Conductor - User Manual Verification 'Phase 3: Final Refinement and Standards' (Protocol in workflow.md)
