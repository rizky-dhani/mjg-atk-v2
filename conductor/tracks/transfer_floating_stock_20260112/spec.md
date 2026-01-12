# Specification: Transfer Floating Stock to Division

## 1. Overview
Add functionality to transfer ATK items from the "Floating Stock" (General Stock) directly to specific divisions. This will include both individual and bulk actions within the `AtkFloatingStock` management interface.

## 2. Functional Requirements
- **Transfer Action:** 
    - Add a "Transfer to.." button as a Table Action for individual items.
    - Add a "Transfer to.." button as a Bulk Action for multiple selected items.
- **Transfer Modal Form:**
    - **Target Division (Required):** Searchable select field to choose the receiving division.
    - **Quantity (Required):** Numeric input validated against available floating stock.
    - **Notes (Optional):** Textarea for optional remarks.
- **Stock Movement Logic:**
    - Upon confirmation, the system must:
        1. Decrease the `current_stock` in `AtkFloatingStock`.
        2. Increase/Create the `current_stock` in `AtkDivisionStock` for the target division.
        3. Record an `out` transaction in `AtkFloatingStockTransactionHistory` with the destination division.
        4. Record a `transfer` transaction in `AtkStockTransaction` for the receiving division.
- **Validation:**
    - Prevent transfers exceeding available floating stock.
    - Ensure target division is valid.

## 3. Technical Requirements
- **UI:** Update `AtkFloatingStocksTable.php` to include `Action` and `BulkAction`.
- **Logic:**
    - Use the existing `distributeToDivision` method in the `AtkFloatingStock` model, or refine it to support bulk operations and notes.
    - Ensure all updates are wrapped in a database transaction.
- **Permissions:** Ensure only authorized users (e.g., GA Admins) can perform these transfers.

## 4. Acceptance Criteria
- User can select one or more items from Floating Stock and transfer them to a chosen division.
- Floating stock decreases and division stock increases correctly.
- Transaction histories for both floating and division stock are accurately updated.
- Validation prevents over-transferring.
