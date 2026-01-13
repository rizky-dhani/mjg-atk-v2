# Track Specification: Add Floating Stock Incoming Transactions to Division Stock

## Overview
Enhance the `AtkDivisionStock` resource in Filament by adding a new relation manager. This manager will display incoming transactions specifically from Floating Stock requests, allowing division users and admins to track when and how much stock was received from the central floating inventory.

## Functional Requirements
- **Relationship:** Create a specific relationship in the `AtkDivisionStock` model to link it with `AtkRequestFromFloatingStockItem` based on `item_id` and the parent request's `division_id`.
- **UI Component:** Add a Filament Relation Manager to the `AtkDivisionStock` resource.
- **Table Columns:**
    - **Request Number:** Display the `request_number` from the parent `AtkRequestFromFloatingStock`.
    - **Quantity:** Display the `quantity` of the item in that specific request.
    - **Status:** Display the current approval/fulfillment status of the request.
    - **Request Date:** Display when the request was created.
- **Filtering:** The list must be strictly filtered to only show items that match the current `AtkDivisionStock` record (same `item_id` AND same `division_id`).
- **Interaction:**
    - **View Action:** Users can click a "View" action to see the full details of the Floating Stock Request.
- **Read-Only (Modification):** The relation manager table should not have create, edit, or delete actions.

## Technical Components
- **Model Update:** `App\Models\AtkDivisionStock`
    - Implement a `floatingStockRequests()` relationship.
- **Filament Relation Manager:** `App\Filament\Resources\AtkDivisionStocks\RelationManagers\FloatingStockRequestsRelationManager`
- **Resource Update:** `App\Filament\Resources\AtkDivisionStocks\AtkDivisionStockResource`
    - Register the new relation manager in the `getRelations()` method.

## Non-Functional Requirements
- **Performance:** Ensure the relationship query is optimized to prevent slow loading times on the Division Stock detail page.
- **Consistency:** Use existing styling and column patterns found in other transaction-related tables.

## Acceptance Criteria
- [ ] Navigating to an `AtkDivisionStock` record shows a new tab/section for "Permintaan Stok Umum".
- [ ] The table correctly lists incoming floating stock items for that specific division and item.
- [ ] Clicking the "View" action correctly opens or redirects to the original Floating Stock Request.
- [ ] No action buttons (Create/Edit/Delete) are visible in the relation manager.

## Out of Scope
- Showing outgoing transfers to floating stock (already handled by existing transaction history).
