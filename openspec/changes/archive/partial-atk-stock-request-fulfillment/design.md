# Design Document: Partial Fulfillment for ATK Stock Request Items

## Overview

This document outlines the design for introducing partial fulfillment capabilities for `AtkStockRequestItem`s within the `AtkStockRequest` workflow. The current system assumes complete fulfillment of stock requests. This enhancement allows users to record received stock quantities incrementally for each item, providing greater flexibility and accuracy in inventory management.

## Database Schema Changes

The `atk_stock_request_items` table will be modified to track received quantities.

*   **Table**: `atk_stock_request_items`
*   **Column Name**: `received_quantity`
*   **Data Type**: `INTEGER`
*   **Default Value**: `0`

Additionally, consideration will be given to updating the `status` column of `atk_stock_request_items` or introducing a new one to reflect states like `pending`, `partially_received`, and `fully_received`. The `atk_stock_requests` table's `status` column will also need to be reviewed and potentially updated to reflect an overall `partially_fulfilled` state.

## Model Changes

### `AtkStockRequestItem` Model

*   **New Attribute**: `received_quantity`.
*   **Casting**: Ensure `received_quantity` is cast correctly.
*   **Status Derivation**: Methods to derive the fulfillment status of an individual item (e.g., `isFullyReceived()`, `isPartiallyReceived()`) based on `requested_quantity` and `received_quantity`.

### `AtkStockRequest` Model

*   **Status Derivation**: Add logic to `AtkStockRequest` to derive its overall fulfillment status (e.g., `pending`, `partially_fulfilled`, `fulfilled`) by aggregating the statuses or received quantities of its associated `AtkStockRequestItem`s.

## User Interface Integration (Filament)

The `AtkStockRequest` detail page will be the primary interface for managing partial fulfillment.

### "Store Stock" Button/Action

For each `AtkStockRequestItem` listed on the `AtkStockRequest` detail page, a "Store Stock" button or action will be added.

*   **Trigger**: Clicking this button will open a modal or inline form.
*   **Input**: The modal/form will allow the user to input the quantity of stock currently being received for that specific item. It will default to the remaining quantity to be received.
*   **Validation**: Input will be validated to ensure the received quantity does not exceed the remaining requested quantity.

### Bulk Fulfillment for `AtkStockRequestItem`s

To facilitate efficiency for multiple items, a bulk fulfillment option will be added:

*   **Selection Mechanism**: Checkboxes or a similar selection mechanism will be available next to each `AtkStockRequestItem` in the detail view.
*   **"Bulk Store Stock" Button**: A button will appear (e.g., at the top of the items list or as a bulk action) when one or more `AtkStockRequestItem`s are selected.
*   **Trigger**: Clicking this button will open a modal or a dedicated bulk fulfillment page.
*   **Input**: The modal/page will allow the user to input quantities for all selected items. It should clearly indicate remaining quantities for each selected item.
*   **Validation**: Validation will ensure that the received quantity for any item does not exceed its remaining requested quantity.

### Display of Quantities and Status

*   **`AtkStockRequestItem` Display**: Each item entry will show:
    *   `requested_quantity`
    *   `received_quantity`
    *   `remaining_quantity` (calculated: `requested_quantity` - `received_quantity`)
    *   Current fulfillment status (e.g., a badge indicating `Pending`, `Partial`, `Full`).
*   **`AtkStockRequest` Overall Status**: The main status of the `AtkStockRequest` will dynamically update to reflect the fulfillment progress (e.g., `Pending`, `Partially Fulfilled`, `Fulfilled`).

## Backend Logic for Stock Processing

When a user submits a quantity via the "Store Stock" action:

1.  **Update `AtkStockRequestItem`**: The `received_quantity` for the specific `AtkStockRequestItem` will be incremented by the amount entered. Its individual status will be updated accordingly.
2.  **Update Inventory**: The corresponding `AtkItem`'s stock levels in the main inventory system MUST be increased by the received quantity. This may involve interacting with `AtkDivisionStock` or similar models.
3.  **Record Transaction**: A new record in `AtkStockTransaction` (or a similar logging mechanism) MUST be created to log the stock receipt, including the `AtkItem`, quantity, source (e.g., `AtkStockRequestItem`), and user.
4.  **Update `AtkStockRequest` Status**: The overall `AtkStockRequest` status will be re-evaluated and updated based on the fulfillment state of all its `AtkStockRequestItem`s.

## Adapting Existing Codebase

Existing functionalities that rely on the assumption of full `AtkStockRequest` fulfillment will need careful review and adaptation. This includes:

*   **Reporting**: Reports on stock requests will need to account for partial fulfillment.
*   **Stock Level Checks**: Any automated stock checks or alerts related to requests might need adjustments.
*   **Approval Flows**: If approval flows are tied to fulfillment, they will need to understand partial states.
*   **Policies/Permissions**: Review if specific permissions are needed for recording partial receipts.

## Future Considerations

*   Tracking multiple receipts for a single `AtkStockRequestItem`.
*   Notifications for partial receipts.
*   Integration with purchasing/vendor management systems.
