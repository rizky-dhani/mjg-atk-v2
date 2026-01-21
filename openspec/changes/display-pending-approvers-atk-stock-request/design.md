# Design Document: Display Approver Name and Position in AtkStockRequest View Page

## Overview

This document describes the design for enhancing the `AtkStockRequest` View page in Filament to display a clear overview of the associated approval flow, specifically showing the names and positions of the approvers involved. The key aspect is that this information should be available proactively, even before any approval action has been taken, providing full transparency into who is expected to approve the request.

## Data Retrieval and Relationships

The `AtkStockRequest` model is assumed to be associated with an `Approval Flow` through an existing relationship. The `Approval Flow` defines the sequence of steps and the users or roles responsible for approval at each step.

*   **`AtkStockRequest` to `Approval Flow`**: An `AtkStockRequest` will have a relationship to an `ApprovalFlow` instance (e.g., `hasOne` or `belongsTo`).
*   **`Approval Flow` to `Approval Flow Steps`**: An `ApprovalFlow` will consist of multiple `ApprovalFlowStep`s.
*   **`Approval Flow Steps` to `Approvers`**: Each `ApprovalFlowStep` will identify the user(s) or role(s) designated as approvers for that step. This might involve direct user IDs, role IDs, or more complex logic to determine the actual approver(s).
*   **Approver Details**: Once the approver `User` model(s) are identified, their `name` and `position` (or role/title) attributes will be retrieved.

## User Interface Integration (Filament `AtkStockRequest` View Page)

A new dedicated section will be added to the `AtkStockRequest` View page within Filament.

### Section Placement

The new section will be logically placed, possibly under the main request details or in a sidebar, providing clear visibility of the approval chain.

### Display Format

For each step in the approval flow, the following information will be displayed:

*   **Approver Name**: The full name of the individual user(s) expected to approve that step.
*   **Approver Position/Role**: Their designated position or role (e.g., "Department Head", "Manager", "Finance Approver").
*   **Current Status (Optional but Recommended)**: A clear indicator of the current status of that approval step (e.g., "Pending", "Approved", "Rejected", "Skipped"). This will provide dynamic context to the static approver list.

## Implementation Details

*   **Filament Infolist/Repeater**: Filament's Infolist feature or a custom Livewire component with a repeater field could be used to iterate through the approval flow steps and display the approver details.
*   **Eloquent Relationships**: Eager loading will be used to efficiently fetch the `Approval Flow`, its steps, and associated `User` details to avoid N-plus-1 query problems.
*   **Data Transformation**: Logic will be required to transform `ApprovalFlowStep` data into a presentable list of approvers and their positions. This might involve custom accessor methods on models or dedicated service classes.
*   **Authorization**: Ensure that the approver information is displayed based on appropriate authorization rules. While the approver list itself might be public for transparency, certain details or actions might be restricted.

## Future Considerations

*   Visual indicators of the active approval step.
*   Ability to notify pending approvers directly from this view.
*   Integration with an approval history log for detailed timestamps and comments.
