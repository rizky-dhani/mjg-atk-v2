# Tasks

This document outlines the tasks required to implement the "Implement Partial Fulfillment for ATK Stock Request Items" feature.

1.  **Database Modifications:**
    *   [x] Create a migration to add a `received_quantity` (integer) column to the `atk_stock_request_items` table, defaulting to 0.
    *   [x] Consider adding a `status` column to `atk_stock_request_items` (e.g., `pending`, `partially_received`, `fully_received`) or updating the logic of an existing one.

2.  **Model Modifications:**
    *   [x] Update the `AtkStockRequestItem` model to handle the new `received_quantity` and potentially `status` attributes.
    *   [x] Update `AtkStockRequest` model logic to derive its overall status (e.g., `pending`, `partially_fulfilled`, `fulfilled`) based on the status/quantities of its `AtkStockRequestItem`s.

3.  **Filament UI Modifications (`AtkStockRequest` Detail Page):**
    *   [x] For each `AtkStockRequestItem` in the `AtkStockRequest` detail view, add a "Store Stock" button/action.
    *   [x] This button should ideally trigger a modal or a form allowing the user to input the `quantity` of stock being received for that specific item.
    *   [x] Display the `requested_quantity` and `received_quantity` for each item, and visually indicate the remaining quantity to be received.
    *   [x] Implement a selection mechanism (e.g., checkboxes) for `AtkStockRequestItem`s.
    *   [x] Add a "Bulk Store Stock" button/action that appears when items are selected, triggering a bulk fulfillment modal/form.
    *   [x] Adjust the overall `AtkStockRequest` status display to reflect partial fulfillment.

4.  **Backend Logic for Stock Storage:**
    *   [x] Implement a service or action that processes the received quantity for an `AtkStockRequestItem`.
    *   [x] This logic MUST:
        *   [x] Update the `received_quantity` on the `AtkStockRequestItem`.
        *   [x] Update the actual inventory/stock levels for the corresponding `AtkItem`.
        *   [x] Record a stock transaction (e.g., `AtkStockTransaction`) for the received quantity.
        *   [x] Update the `AtkStockRequestItem`'s status based on `received_quantity` vs `requested_quantity`.
        *   [x] Update the parent `AtkStockRequest`'s status based on its items' statuses.

5.  **Adapt Current Codebase:**
    *   [x] Review and modify existing codebase (e.g., reporting, stock level checks, fulfillment processes) that currently assumes full `AtkStockRequest` fulfillment to correctly account for partial receipts. This includes any associated services, policies, or listeners.

6.  **Testing:**
    *   [x] Add unit/feature tests for database modifications.
    *   [x] Add feature tests for the "Store Stock" button/action, verifying quantity updates, inventory changes, and status transitions.
    *   [x] Add feature tests for the "Bulk Store Stock" button/action, verifying quantity updates, inventory changes, and status transitions for multiple selected items.
    *   [x] Add tests for the adapted codebase to ensure it correctly handles partial fulfillment.

7.  **Refactor/Optimize (if necessary):**
    *   [x] Review the new and modified code for performance, scalability, and maintainability.

8.  **Documentation:**
    *   [x] Update relevant documentation (if any) for the new partial fulfillment workflow.
