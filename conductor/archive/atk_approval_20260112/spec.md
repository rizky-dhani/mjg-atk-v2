# Specification: ATK Stock Request Approval Workflow Refinement

## 1. Overview
This specification details the refinement of the approval workflow for ATK Stock Requests. The goal is to transition from a static process to a flexible, multi-step system that supports dynamic approvers based on division and role, provides real-time notifications, and is optimized for mobile decision-making.

## 2. Functional Requirements

### 2.1 Flexible Multi-step Logic
- Support for multiple approval stages (e.g., Department Head -> Finance -> General Affairs).
- Approval steps must be configurable via the existing `ApprovalFlow` and `ApprovalFlowStep` models.
- The system must automatically determine the next required approver(s) based on the current step and the requester's division.

### 2.2 Integration with Stock Requests
- When an `AtkStockRequest` is submitted, it should trigger the assigned `ApprovalFlow`.
- Stock levels should only be adjusted *after* the final approval in the sequence.

### 2.3 Real-time Notifications
- Send email notifications to the next approver(s) as soon as a request moves into their stage.
- Implement system notifications (Filament Notifications) for approvers upon login.

### 2.4 Mobile-Optimized Interface
- Refine Filament Actions to ensure buttons and modals are easily interactable on mobile viewports.
- Summarize request items in a concise list for quick review on smaller screens.

## 3. Technical Implementation

### 3.1 Models & Relationships
- Utilize `AtkStockRequest`, `Approval`, and `ApprovalHistory`.
- Ensure `ApprovalFlow` is linked to `AtkStockRequest`.

### 3.2 Services
- `ApprovalService`: Central logic for processing approvals and moving through steps.
- `StockUpdateService`: Logic for adjusting division stock upon final approval.

### 3.3 UI/UX (Filament)
- Use `Filament\Actions\Action` for "Approve" and "Reject" workflows.
- Implement responsive layout components in the approval modals.

## 4. Testing & Quality
- **Unit Tests:** Validate step transitions, authorization checks, and stock adjustment logic.
- **Integration Tests:** End-to-end flow from submission to final approval and stock update.
- **Coverage:** Minimum 99% coverage required for all new service and model logic.
