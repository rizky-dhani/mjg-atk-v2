# Design Document: Item Price Snapshot for ATK Stock Request Items

## Overview

This document describes the design for implementing an "Item Price Snapshot" within `AtkStockRequestItem` records. The primary goal is to preserve the price of an `AtkItem` at the exact moment it is requested, enabling future analysis and comparison with potentially changed current prices. This ensures data integrity for historical records and financial reporting related to stock requests.

## Database Schema Changes

A new column will be added to the `atk_stock_request_items` table.

*   **Table**: `atk_stock_request_items`
*   **Column Name**: `requested_price`
*   **Data Type**: `DECIMAL(10, 2)` (or appropriate precision based on existing price columns)
*   **Nullable**: `FALSE` (or `TRUE` if existing records need to be backfilled, with a suitable default)
*   **Default Value**: `0.00` (or `NULL` if backfilling, then make non-nullable after backfill)

## Model Changes

The `AtkStockRequestItem` Eloquent model will be updated to include the new `requested_price` attribute.

*   **Fillable/Guarded**: Ensure `requested_price` is fillable or not guarded.
*   **Casting**: Cast `requested_price` to `float` or `decimal` if necessary.

### Price Capture Logic

The `requested_price` for each `AtkStockRequestItem` will be populated at the time of its creation.

*   When an `AtkStockRequestItem` is being created (e.g., within the `AtkStockRequest` creation process), the system MUST retrieve the current price from the associated `AtkItem` (e.g., `AtkItem::find($item_id)->price`) and assign it to the `requested_price` attribute before saving the `AtkStockRequestItem`.

## User Interface Integration (Filament)

The `requested_price` should be displayed alongside other `AtkStockRequestItem` details in the Filament admin panel.

*   **`AtkStockRequest` Detail View**: When viewing the details of an `AtkStockRequest`, the list of `AtkStockRequestItem`s should include the `requested_price`. This allows for direct comparison with the current `AtkItem` price (which may also be displayed).

## Implementation Details

*   **Migration**: A standard Laravel migration will be created.
*   **Event Listeners/Observers**: Consider using an Eloquent event listener (e.g., `creating` event on `AtkStockRequestItem`) or modifying the service responsible for creating `AtkStockRequestItem`s to inject the current `AtkItem` price.
*   **Data Consistency**: Ensure that the price capture mechanism is robust and accurately reflects the `AtkItem`'s price at the moment of request submission.

## Future Considerations

*   Highlighting price discrepancies between `requested_price` and current `AtkItem` price in the UI.
*   Reporting on historical price changes for `AtkItem`s via `AtkStockRequestItem` snapshots.
