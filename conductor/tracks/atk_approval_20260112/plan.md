# Plan: ATK Stock Request Approval Workflow Refinement

## Phase 1: Core Approval Logic & Step Transitions [checkpoint: 07e02ed]

- [x] **Task 1: Define Approval Logic Tests**
  - Write Pest tests for dynamic step transitions in `ApprovalService`.
  - Validate that the system identifies the correct next approver based on `ApprovalFlowStep`.
  - Target: Failing tests (Red).

- [x] **Task 2: Implement Approval Step Logic**
  - Implement the logic to move an `AtkStockRequest` through its assigned `ApprovalFlow`.
  - Ensure authorization checks are enforced at each step.
  - Target: Passing tests (Green).

- [x] **Task 3: Stock Adjustment Integration Tests**
  - Write tests to ensure stock is *not* adjusted until the final approval step.
  - Write tests to verify stock *is* adjusted correctly after final approval.

- [x] **Task 4: Implement Final Approval Stock Update**
  - Connect `StockUpdateService` to the final step of the `ApprovalService`.
  - Ensure database transactions wrap the approval and stock update.

- [x] **Task: Conductor - User Manual Verification 'Core Approval Logic' (Protocol in workflow.md)**

## Phase 2: Real-time Notifications [checkpoint: 9147f1e]

- [x] **Task 5: Notification Tests**
  - Write tests to verify email dispatch using `AtkStockRequestMail`.
  - Write tests for system notification triggering.

- [x] **Task 6: Implement Email & System Notifications**
  - Integrate `Notification` dispatches within the `ApprovalService` transitions.
  - Refine email templates for mobile readability.

- [x] **Task: Conductor - User Manual Verification 'Notifications' (Protocol in workflow.md)**

## Phase 3: Mobile-Optimized Interface [checkpoint: 4376b2f]

- [x] **Task 7: UI Component Testing**
  - Write Pest browser tests for the "Approve" and "Reject" Filament actions.
  - Assert that responsive classes are present on modal components.

- [x] **Task 8: Refine Filament Actions for Mobile**
  - Update the `AtkStockRequestResource` to use mobile-friendly layouts in action modals.
  - Implement a condensed "Request Summary" view for small screens.

- [x] **Task: Conductor - User Manual Verification 'Mobile UI' (Protocol in workflow.md)**

## Phase 4: Final Verification & Coverage

- [ ] **Task 9: Global Coverage Audit**
  - Run full test suite with coverage report.
  - Address any gaps to ensure >99% coverage for new modules.

- [ ] **Task: Conductor - User Manual Verification 'Final Integration' (Protocol in workflow.md)**
