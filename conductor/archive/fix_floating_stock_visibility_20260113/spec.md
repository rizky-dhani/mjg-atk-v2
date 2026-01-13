# Track Specification: Fix AtkRequestFromFloatingStocks Approval Visibility

## Overview
Currently, the `AtkRequestFromFloatingStocks` approval list does not correctly show requests to the next approver in the workflow after the initial approval (e.g., after the Division Head). The system should only display requests to users who are eligible to approve the **current active step** of the approval flow.

## Functional Requirements
- **Active Step Filtering:** The "Approval" list for Floating Stock requests must be restricted to users who match the role and division requirements of the `current_step` defined in the `approvals` table.
- **Role & Division Matching:**
    - If a step has a specific `division_id`, the user must have the required role AND belong to that division.
    - If a step has a `null` division_id, it should match based on the requester's division (standard behavior for "Division Head" steps).
- **Consistency:** Align the query logic in `ApprovalAtkRequestFromFloatingStock` with the more robust logic found in `ApprovalAtkStockRequest`.

## Technical Components
- **Page Update:** `App\Filament\Resources\AtkRequestFromFloatingStocks\Pages\ApprovalAtkRequestFromFloatingStock`
    - Refactor the `table` query to join with the active `approval_flow_steps` based on the `current_step` index.
- **Service Audit (Optional):** Ensure `ApprovalValidationService` correctly handles the "current step" logic for all models to prevent side effects.

## Acceptance Criteria
- [ ] A GA Admin (Step 2) cannot see a request that is still at Step 1 (Division Head).
- [ ] After the Division Head (Step 1) approves, the request successfully appears in the GA Admin's (Step 2) approval list.
- [ ] The request disappears from the Division Head's list once they have approved it.
- [ ] The "Persetujuan Permintaan Stok Umum" badge count correctly reflects the number of requests waiting for the current user's action.

## Out of Scope
- Changes to the core `ApprovalService` logic (unless a bug is found during implementation).
- Redesigning the Approval UI.
