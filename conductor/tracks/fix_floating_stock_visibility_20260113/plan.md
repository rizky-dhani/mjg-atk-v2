# Implementation Plan - Fix AtkRequestFromFloatingStocks Approval Visibility

This plan outlines the steps to resolve the visibility bug where Floating Stock requests do not correctly appear for the next approver in the workflow.

## Phase 1: Bug Reproduction and Test Setup
Establish a baseline by creating tests that fail under the current implementation.

- [x] Task: Create a feature test `AtkFloatingStockApprovalVisibilityTest` to reproduce the issue.
    - Sub-task: **CRITICAL:** Use `DatabaseTransactions` to ensure no data is persisted to the database.
    - Sub-task: Test that a GA Admin (Step 2) cannot see a request at Step 1.
    - Sub-task: Test that a GA Admin *can* see a request once it reaches Step 2.
    - Sub-task: Test that a Division Head (Step 1) no longer sees the request after approving it.
- [x] Task: Conductor - User Manual Verification 'Phase 1: Bug Reproduction and Test Setup' (Protocol in workflow.md)

## Phase 2: Query Refactoring
Update the approval page logic to strictly filter by the current active step.

- [x] Task: Refactor the query in `ApprovalAtkRequestFromFloatingStock.php`.
    - Sub-task: Align logic with `ApprovalAtkStockRequest` by using the `current_step` from the `approvals` table.
    - Sub-task: Ensure the join/relationship logic correctly matches the user's role and division to the active step.
- [x] Task: Implement a navigation badge for the Floating Stock approval menu to match `AtkStockRequest`.
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Query Refactoring' (Protocol in workflow.md)

## Phase 3: Final Verification and Standards
Ensure the fix is robust and adheres to project quality standards.

- [ ] Task: Verify all tests in `AtkFloatingStockApprovalVisibilityTest` pass.
- [ ] Task: Run `vendor/bin/pint` to ensure code style compliance.
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Final Verification and Standards' (Protocol in workflow.md)
