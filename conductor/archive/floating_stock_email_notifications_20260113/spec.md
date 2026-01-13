# Track Specification: Floating Stock Request Email Notifications

## Overview
Implement email notification functionality for `AtkRequestFromFloatingStock` requests, mirroring the behavior of the existing `AtkStockRequest` email system. This ensures that requesters and approvers are kept informed about the status of requests specifically for floating stock items.

## Functional Requirements
- **Email Triggers:**
    - **Submitted:** When a new floating stock request is created/submitted.
    - **Final Decisions:** When a request is fully approved, rejected, or partially approved.
- **Recipients:**
    - **Requester:** Receives notifications for submission and final status updates.
    - **Approver(s):** Receive a notification when a new request is submitted and requires their action.
- **Content Requirements:**
    - Request Number (e.g., ATK-FS-20260113-0001).
    - List of requested items and quantities.
    - Current status of the request.
    - Notes or reasons provided by the requester or approver.
    - A direct link to view the request within the Filament admin panel.
- **Technical Components:**
    - New Mailable class: `App\Mail\AtkRequestFromFloatingStockMail`.
    - Dedicated Blade view: `resources/views/emails/atk-request-floating-stock.blade.php`.
    - Integration with `ApprovalProcessingService` or dedicated observers to trigger emails.

## Non-Functional Requirements
- **Consistency:** The email style and tone should match existing `AtkStockRequest` emails.
- **Performance:** Emails should be queued to avoid blocking the main application flow.

## Acceptance Criteria
- [ ] Submitting a floating stock request triggers an email to the requester.
- [ ] Submitting a floating stock request triggers an email to the first-step approver(s).
- [ ] A final approval/rejection triggers an email to the requester.
- [ ] Emails contain correct request details (number, items, status).
- [ ] The "View Request" button in the email correctly directs the user to the specific request in the Filament panel.
- [ ] All email dispatches are covered by Pest feature tests.

## Out of Scope
- Real-time browser notifications (Websockets).
- SMS or WhatsApp notifications.
- Step-by-step approval notifications (only submission and final status).
