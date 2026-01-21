# Tasks

This document outlines the tasks required to implement the "Link Status Widgets to List Pages with Applied Filters" feature.

1.  **Modify `AtkStockRequestStatus` Widget:**
    *   [x] Identify the `AtkStockRequestStatus` widget.
    *   [x] Implement click functionality to navigate to the `AtkStockRequests` list page.
    *   [x] Generate a URL that includes the necessary filter parameters to display only requests with the corresponding status.

2.  **Modify `AtkStockUsageStatus` Widget:**
    *   [x] Identify the `AtkStockUsageStatus` widget.
    *   [x] Implement click functionality to navigate to the `AtkStockUsages` list page.
    *   [x] Generate a URL that includes the necessary filter parameters to display only usages with the corresponding status.

3.  **Modify `AtkTransferStockStatus` Widget:**
    *   [x] Identify the `AtkTransferStockStatus` widget.
    *   [x] Implement click functionality to navigate to the `AtkTransferStocks` list page.
    *   [x] Generate a URL that includes the necessary filter parameters to display only transfers with the corresponding status.

4.  **Testing:**
    *   [x] Add feature tests for each modified widget to verify that clicking them navigates to the correct list page with the correct filters applied.
    *   [x] Verify that the filtered list displays only the relevant records. (Added approval_status filters to tables to support this).

5.  **Refactor/Optimize (if necessary):**
    *   [x] Review the code for consistency in URL generation and filter application.

6.  **Documentation:**
    *   [x] Update relevant documentation (if any) for the enhanced widget functionality.
