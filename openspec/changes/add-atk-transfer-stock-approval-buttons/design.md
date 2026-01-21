# Design Document: Approve/Reject Buttons for AtkTransferStocks

## Overview

This document describes the design for adding "Approve" and "Reject" buttons to the Filament admin panel for `AtkTransferStock` records. The primary goal is to provide a direct and intuitive interface for authorized users to interact with the existing approval workflow for `AtkTransferStock` models.

## User Interface Integration (Filament)

"Approve" and "Reject" buttons will be integrated into the `AtkTransferStock` view/edit page within the Filament admin panel.

### Button Placement

These buttons will likely be placed prominently on the `AtkTransferStock` detail page, perhaps in the header actions or within a dedicated section, allowing users to clearly see and interact with the approval status.

### Button States

The visibility and enabled state of these buttons will depend on:
*   The current status of the `AtkTransferStock` (e.g., "pending approval").
*   The authorization of the logged-in user.

## Integration with Existing Approval Flow

The application is assumed to have an existing approval flow mechanism for `AtkTransferStock`. The new Filament Actions will serve as a UI trigger for this backend logic.

*   **Existing Logic**: The Filament Actions will call the appropriate methods or services (e.g., `TransferStockApprovalService::approve()`, `TransferStockApprovalService::reject()`) that encapsulate the existing approval flow logic.
*   **Parameters**: The actions will pass the `AtkTransferStock` instance and the current user's context to the approval service.
*   **Status Update**: Upon successful approval or rejection by the backend service, the `AtkTransferStock` record's status will be updated accordingly in the database.

## Authorization

Access to the "Approve" and "Reject" buttons will be strictly controlled.

*   **Policy Enforcement**: Laravel Policies (e.g., `AtkTransferStockPolicy`) will be used to determine if the currently authenticated user has permission to perform the `approve` or `reject` action on a given `AtkTransferStock` instance.
*   **Button Visibility**: Filament will leverage these policies to control the visibility of the "Approve" and "Reject" buttons in the UI. Unauthorized users will not see these buttons.

## Implementation Details

*   **Filament Actions**: Custom Filament Actions will be implemented within the `AtkTransferStockResource` or related page. These actions will define the UI for the buttons and their associated logic.
*   **Confirmation Modals**: To prevent accidental approvals/rejections, the actions will likely include confirmation modals.
*   **Notifications**: Upon successful approval or rejection, a Filament notification will be displayed to the user confirming the action.
