# Plan: Flexible & UI-Driven Approval System

## Phase 1: Foundational Abstractions & Generic Service [checkpoint: ]

- [ ] **Task 1: Define `Approvable` Interface and `HasApprovals` Trait**
  - Create `App\Contracts\Approvable` interface with methods for getting total value, division, and requester.
  - Create `App\Traits\HasApprovals` trait to handle the `morphOne` relationship to `Approval`.
  - Write unit tests to verify trait/interface integration.

- [ ] **Task 2: Refactor `ApprovalService` for Generic Handling**
  - Update `ApprovalValidationService` and `ApprovalProcessingService` to accept `Approvable` instead of specific models.
  - Remove hardcoded model checks from validation logic.
  - Write tests using a mock `Approvable` model.

- [ ] **Task 3: Create `ApprovalRule` Model and Migration**
  - Migration for `approval_rules` table: `id`, `model_type`, `flow_id`, `priority`, `conditions` (JSON), `options` (JSON for toggles).
  - Define `ApprovalRule` model with logic to evaluate conditions (value, division).
  - Write tests for rule matching and priority resolution.

- [ ] Task: Conductor - User Manual Verification 'Foundational Abstractions' (Protocol in workflow.md)

## Phase 2: Super Admin UI (Filament Resource) [checkpoint: ]

- [ ] **Task 4: Create `ApprovalMappingResource`**
  - Implement a Filament resource to manage `ApprovalRule`.
  - Add fields for Model selection (filtered by models using `HasApprovals`), Flow selection, and Priority.
  - Implement the "Logic Toggles" section with checkboxes for Branching, Skipping, and Division logic.

- [ ] **Task 5: Enhance `ApprovalFlowResource` with Rule Integration**
  - (Optional/Refinement) Allow viewing which rules are associated with a flow.
  - Ensure UI allows for complex condition building (e.g., using Filament's Builder or Repeater for conditions).

- [ ] Task: Conductor - User Manual Verification 'Management UI' (Protocol in workflow.md)

## Phase 3: Advanced Routing Logic Implementation [checkpoint: ]

- [ ] **Task 6: Implement Dynamic Step Skipping**
  - Update `ApprovalProcessingService` to check the "Step Skipping" rule.
  - If requester is the intended approver and skipping is enabled, auto-approve and advance.
  - Write tests for various skipping scenarios.

- [ ] **Task 7: Implement Value-Based Branching**
  - Update `ApprovalService` to select different flows or skip specific steps based on `Approvable::getApprovalValue()`.
  - Implement the logic to resolve the correct flow based on the `ApprovalRule` priority.
  - Write tests for boundary values and multiple rule matches.

- [ ] **Task 8: Implement Division-Specific Routing**
  - Ensure the rule engine correctly filters flows based on the requester's `division_id`.
  - Write tests for cross-division and division-specific approval paths.

- [ ] Task: Conductor - User Manual Verification 'Routing Logic' (Protocol in workflow.md)

## Phase 4: Integration & Global Audit [checkpoint: ]

- [ ] **Task 9: Migrate Existing Models**
  - Refactor `AtkStockRequest` and `AtkStockUsage` to implement `Approvable` and use the trait.
  - Remove old model-specific methods from `ApprovalService`.
  - Update tests to ensure legacy functionality still passes with the new engine.

- [ ] **Task 10: Final Coverage and Standards Audit**
  - Run full test suite with coverage report (target >99%).
  - Run Laravel Pint for final code formatting.

- [ ] Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)
