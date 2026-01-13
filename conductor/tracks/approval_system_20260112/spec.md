# Spec: Flexible & UI-Driven Approval System

## Overview
Transform the approval system into a generic engine that can be applied to any model via a simple opt-in. All routing logic, priority rules, and feature toggles (branching/skipping) will be managed through a centralized Super Admin UI.

## Functional Requirements
- **Generic Opt-in Mechanism:**
    - Provide a `HasApprovals` Trait and `Approvable` Interface.
    - Adding this Trait to a model (e.g., `MarketingMediaRequest`) makes it compatible with the engine.
- **Centralized Approval Management UI:**
    - A dedicated Filament Resource for Super Admins to manage "Approval Mappings."
    - **Mapping Configuration:** Choose a Model (from a list of compatible models) and assign it a Flow.
    - **Logic Toggles (Checkboxes):** For each mapping/flow, enable/disable:
        - [ ] **Value-Based Branching:** Trigger different steps based on total amount.
        - [ ] **Division-Specific Routing:** Automatically route based on the requester's division.
        - [ ] **Dynamic Step Skipping:** Auto-skip steps if certain conditions are met (e.g., Requester is the Approver).
- **Hybrid Routing Engine:**
    - Use **Explicit Mapping** as the base.
    - Apply **Global Priority Rules** to resolve which specific flow version to use when multiple conditions overlap.
- **Opt-in Scope:** Support `AtkStockRequest`, `AtkStockUsage`, and future `MarketingMedia` resources.

## Acceptance Criteria
- [ ] Adding `HasApprovals` to a new model allows it to appear in the Approval Management UI.
- [ ] Super Admin can toggle "Step Skipping" in the UI, and the engine respects that toggle without code changes.
- [ ] Value-based branching correctly splits a request into different approval paths based on the UI configuration.
- [ ] Division-specific flows are correctly selected based on the user's division.

## Out of Scope
- Dynamic creation of database tables for new models (models must still be created via migrations).
- Visual "Node-based" flow editor.
