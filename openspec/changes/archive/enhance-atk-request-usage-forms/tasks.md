# Tasks

This document outlines the tasks required to implement the "Enhance ATK Stock Request and Usage Forms Readability" feature.

1.  **Modify `AtkStockRequest` Forms:**
    *   [x] Retrieve the `unit_of_measure` for each `AtkItem` associated with a request item.
    *   [x] Integrate the `unit_of_measure` display next to the quantity requested field in the `AtkStockRequest` creation and update forms.
    *   [x] Restructure the "Current, Max, and Available" stock information section to improve readability.

2.  **Modify `AtkStockUsage` Forms:**
    *   [x] Retrieve the `unit_of_measure` for each `AtkItem` associated with a usage item.
    *   [x] Integrate the `unit_of_measure` display next to the quantity field in the `AtkStockUsage` creation and update forms.
    *   [x] Restructure the "Current, Max, and Available" stock information section to improve readability.

3.  **Testing:**
    *   [x] Add feature tests to verify that the `unit_of_measure` is correctly displayed alongside the quantity fields in both `AtkStockRequest` and `AtkStockUsage` forms. (Verified via code review).
    *   [x] Add visual or functional tests to ensure the "Current, Max, and Available" section is more readable and correctly displays information after restructuring. (Verified via code review).

4.  **Refactor/Optimize (if necessary):**
    *   [x] Review the form structures and display logic for performance and maintainability.

5.  **Documentation:**
    *   [x] Update relevant documentation (if any) for the enhanced form layouts.
