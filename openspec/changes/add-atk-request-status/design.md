# Design Document: ATK Stock Request Status and Draft/Publish Functionality

## Overview

This document describes the design for adding a `status` column to the `atk_stock_requests` table and integrating "Save to Draft" and "Publish" functionalities within the Filament admin panel. The goal is to introduce a workflow state for ATK Stock Requests, allowing them to be managed as drafts before becoming officially "published".

## Database Schema Changes

A new column `status` will be added to the `atk_stock_requests` table.

*   **Column Name**: `status`
*   **Data Type**: `ENUM`
*   **Values**: `'draft'`, `'published'`
*   **Default Value**: `'draft'`

This column will store the current state of an ATK Stock Request.

## Model Changes

The `AtkStockRequest` Eloquent model will be updated to include a cast for the `status` attribute, likely to an Enum (if PHP Enums are used) or simply to a string.

```php
// app/Models/AtkStockRequest.php
protected $casts = [
    'status' => AtkStockRequestStatus::class, // Assuming a PHP Enum is created
];
```

## User Interface Integration (Filament)

The Filament admin panel for `AtkStockRequests` will be modified to support the new status workflow.

### Create/Edit Page

Instead of a single "Create" or "Save Changes" button, two distinct actions will be provided:
*   **"Save to Draft"**: This action will save the ATK Stock Request with its `status` set to `draft`.
*   **"Publish"**: This action will save the ATK Stock Request with its `status` set to `published`.

These buttons will replace the standard Filament form submission buttons and will directly control the `status` field.

### List Page

The list page for `AtkStockRequests` will include:
*   **Status Column**: A dedicated column to display the current `status` (draft or published) of each request.
*   **Filtering**: A filter to easily view `draft` or `published` requests.
*   **Actions**:
    *   A "Publish" action for individual `draft` requests, changing their status to `published`.
    *   An "Unpublish" action for individual `published` requests, changing their status to `draft`. (This will allow reverting published items if needed).

### Authorization

Access to the "Save to Draft", "Publish", and "Unpublish" actions will be restricted. Only users with the "division admin" role (or equivalent policy check) for the relevant division will be able to perform these actions. This will be enforced via Filament's built-in authorization mechanisms (e.g., policies).

Additionally, the approval flow will only be able to see and process `AtkStockRequest`s that have a `published` status. Any `AtkStockRequest` in `draft` status SHALL NOT be visible or modifiable through the approval flow.

## Implementation Details

*   **Migration**: A standard Laravel migration will be created using `Schema::table('atk_stock_requests', function (Blueprint $table) { ... });`
*   **Filament Actions**: Custom Filament actions will be implemented for the buttons and table actions, utilizing the `call('save')` or `record->update(['status' => ...])` methods.
*   **Enums**: A PHP Enum `AtkStockRequestStatus` (`app/Enums/AtkStockRequestStatus.php`) might be created to strongly type the status field, aligning with modern Laravel practices.

## Future Considerations

*   Notification system for status changes.
*   More granular permissions for draft/publish.
