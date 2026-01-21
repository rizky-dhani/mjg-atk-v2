# Tasks

This document outlines the tasks required to implement the "Add Status Column to ATK Stock Requests and Implement Draft/Publish Functionality" features.

1.  **Database Migration:**
    *   Create a migration to add an `enum` column named `status` to the `atk_stock_requests` table with `draft` and `published` values.
    *   Default the new `status` column to `draft`.
    *   Update the `AtkStockRequest` model to cast the `status` attribute.

2.  **Filament UI Modifications for ATK Stock Requests:**
    *   **Creation/Update Page:**
        *   Add "Save to Draft" button to create an `AtkStockRequest` with `status` as `draft`.
        *   Add "Publish" button to create/update an `AtkStockRequest` with `status` as `published`.
        *   Ensure these buttons are visible only to division administrators.
    *   **List Page:**
        *   Add "Publish" action for `draft` requests, visible only to division administrators, changing the status to `published`.
        *   Add "Unpublish" action for `published` requests, visible only to division administrators, changing the status to `draft`.
        *   Implement filtering for `draft` and `published` requests on the list page.
    *   Implement logic to ensure the approval flow only displays and processes `AtkStockRequest`s with `published` status.
3.  **Testing:**
    *   Add unit/feature tests for the database migration.
    *   Add feature tests for Filament actions/buttons, verifying `status` changes and authorization.
    *   Add feature tests for the approval flow's interaction with `published` status.

4.  **Refactor/Optimize (if necessary):**
    *   Review the code for performance and maintainability.

5.  **Documentation:**
    *   Update relevant documentation (if any) for the new status workflow.
