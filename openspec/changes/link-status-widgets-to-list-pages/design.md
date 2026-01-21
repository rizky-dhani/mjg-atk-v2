# Design Document: Linking Status Widgets to List Pages

## Overview

This document describes the design for enhancing status-related widgets in Filament by making them clickable and linking them to their respective list pages with pre-applied status filters. This feature aims to streamline user workflows by providing quick access to filtered lists of records based on their status (e.g., pending requests, completed usages, rejected transfers).

## User Interface Integration (Filament Widgets)

Existing Filament widgets that display status counts or summaries for `AtkStockRequest`, `AtkStockUsage`, and `AtkTransferStock` will be modified.

### Clickable Widgets

Each widget will become clickable. Upon clicking, the user will be redirected to the relevant Filament resource list page.

### URL Generation with Filters

The navigation will be achieved by generating a URL to the target Filament resource's list page. This URL will include query parameters to activate the appropriate status filter.

*   **Filter Structure**: Filament typically uses query parameters like `?table[filters][status]=pending` to apply filters. The widget will construct this URL dynamically based on the status it represents.
*   **Example**: A widget showing "Pending ATK Stock Requests" will generate a URL similar to `/admin/atk-stock-requests?table[filters][status]=pending`.

## Affected Widgets and Resources

1.  **`AtkStockRequestStatus` Widget**:
    *   Links to: `AtkStockRequests` List Page (e.g., `/admin/atk-stock-requests`)
    *   Filter: `status` (e.g., `draft`, `published`, `pending`, `approved`, `rejected` - depending on the specific status the widget displays).
2.  **`AtkStockUsageStatus` Widget**:
    *   Links to: `AtkStockUsages` List Page (e.g., `/admin/atk-stock-usages`)
    *   Filter: `status` (e.g., `pending`, `completed`).
3.  **`AtkTransferStockStatus` Widget**:
    *   Links to: `AtkTransferStocks` List Page (e.g., `/admin/atk-transfer-stocks`)
    *   Filter: `status` (e.g., `pending`, `approved`, `rejected`).

## Implementation Details

*   **Filament Widget Modification**: The `render()` method or a similar mechanism within each widget will be adapted to wrap its content in an anchor tag (`<a>`) with a dynamically generated `href`.
*   **URL Helper**: Laravel's `route()` helper or Filament's `getResourceUrl()` helper will be used to generate the base URL, and then query parameters will be appended manually or via a helper function to include the filters.
*   **Status Mapping**: A clear mapping between widget display values and actual database status enum values will be maintained for accurate filtering.

## Future Considerations

*   Customizable filter parameters for widgets.
*   More complex filtering logic (e.g., date ranges, multiple statuses).
