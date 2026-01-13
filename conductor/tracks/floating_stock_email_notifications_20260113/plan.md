# Implementation Plan - Floating Stock Request Email Notifications

This plan outlines the steps to implement email notifications for floating stock requests, ensuring requesters and approvers are notified upon submission and final decision.

## Phase 1: Mailable and View Implementation
Preparation of the email templates and Mailable class.

- [x] Task: Create `AtkRequestFromFloatingStockMail` Mailable class. 7aa5c9b
- [x] Task: Create `emails.atk-request-floating-stock` Blade view. 7aa5c9b
- [ ] Task: Conductor - User Manual Verification 'Phase 1: Mailable and View Implementation' (Protocol in workflow.md)

## Phase 2: Email Dispatch Integration
Integrating the email triggers into the approval and submission process.

- [x] Task: Identify and hook into the submission process for `AtkRequestFromFloatingStock`. 146d9d2
- [x] Task: Update `ApprovalProcessingService` or relevant service to dispatch floating stock emails on submission and final decision. 146d9d2
- [x] Task: Ensure emails are sent to the correct recipients (Requester and Approvers). 146d9d2
- [ ] Task: Conductor - User Manual Verification 'Phase 2: Email Dispatch Integration' (Protocol in workflow.md)

## Phase 3: Testing and Refinement
Ensuring high quality and correct behavior through automated tests.

- [x] Task: Write feature tests for email submission notification. 146d9d2
- [x] Task: Write feature tests for final status (Approved/Rejected) notifications. 3eb10b1
- [x] Task: Verify 100% test coverage for new notification logic. 3eb10b1
- [x] Task: Run `vendor/bin/pint` to ensure code style compliance. 3eb10b1
- [ ] Task: Conductor - User Manual Verification 'Phase 3: Testing and Refinement' (Protocol in workflow.md)
