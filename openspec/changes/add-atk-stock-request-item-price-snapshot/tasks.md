# Tasks

This document outlines the tasks required to implement the "Add Item Price Snapshot to ATK Stock Request Items" feature.

1.  **Database Migration:**
    *   Create a migration to add a `decimal` column (e.g., `requested_price`) to the `atk_stock_request_items` table to store the price snapshot.

2.  **Model Modifications:**
    *   Update the `AtkStockRequestItem` model to handle the new `requested_price` attribute.
    *   Modify the creation logic for `AtkStockRequestItem` to automatically populate `requested_price` with the current `AtkItem` price when a new request item is created.

3.  **Filament UI Modifications (Optional, but recommended):**
    *   Display the `requested_price` in the Filament UI for `AtkStockRequest` details (e.g., within the `AtkStockRequestItem` repeater or table).

4.  **Testing:**
    *   Add unit/feature tests for the database migration.
    *   Add feature tests to verify that `requested_price` is correctly captured and stored upon `AtkStockRequestItem` creation.
    *   Add feature tests to verify the display of `requested_price` in the Filament UI (if implemented).

5.  **Refactor/Optimize (if necessary):**
    *   Review the code for performance and maintainability.

6.  **Documentation:**
    *   Update relevant documentation (if any) for the new price snapshot feature.
