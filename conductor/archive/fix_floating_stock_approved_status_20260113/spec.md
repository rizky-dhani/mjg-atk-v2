# Track Specification: Fix Final Approval Status for AtkRequestFromFloatingStock

## Overview
Currently, `AtkRequestFromFloatingStock` requests do not correctly reflect the "Approved" status even after all approvers have completed their steps. This track aims to identify why the status synchronization is failing for this specific model and ensure that the `Approved` state is correctly persisted and displayed.

## Functional Requirements
- **Status Synchronization:** When the final approval step is completed in `ApprovalProcessingService`, the corresponding `AtkRequestFromFloatingStock` model must have its status updated to "approved".
- **History Mapping:** The `getApprovalStatusAttribute` in the `AtkRequestFromFloatingStock` model must accurately retrieve the "approved" status from the latest `ApprovalHistory` or the linked `Approval` record.
- **Consistency:** Ensure the "Approved" status behavior matches that of `AtkStockRequest` and `AtkStockUsage`.

## Technical Components
- **Service Logic:** Audit `ApprovalProcessingService::processApprovalStep` to ensure `syncApprovalStatus($approvable)` is correctly handling `AtkRequestFromFloatingStock`.
- **Model Attribute:** Review `AtkRequestFromFloatingStock::getApprovalStatusAttribute()` to ensure it handles all action types correctly, including final approval.
- **UI Logic:** (If applicable) Ensure the Filament badge colors correctly represent the "approved" state.

## Acceptance Criteria
- [ ] A `AtkRequestFromFloatingStock` that has passed all approval steps shows "Approved" in the Filament table.
- [ ] The "Approved" status is correctly derived from either the `Approval` model or the `ApprovalHistory`.
- [ ] The badge color for "Approved" is consistently green across the system.
- [ ] Automated feature tests verify the status transition from "pending" to "approved" upon final action.

## Out of Scope
- Changes to the physical stock update logic (this is handled by `StockUpdateService`).
- Redesigning the approval workflow configuration.
