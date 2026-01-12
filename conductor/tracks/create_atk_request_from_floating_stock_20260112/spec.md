# Specification: Create AtkRequestFromFloatingStock Resource

## 1. Overview
Implement a new request mechanism where divisions can formally request ATK items from the "Floating Stock" (General Stock). This will replace or augment the direct "Transfer to Division" action with a request-based workflow that supports approvals.

## 2. Functional Requirements
- **Model: AtkRequestFromFloatingStock**
    - Fields: `request_number`, `requester_id`, `division_id`, `status`, `notes`.
    - Relationship: Belongs to `User` (requester), Belongs to `UserDivision`.
    - Relationship: Has Many `AtkRequestFromFloatingStockItem`.
- **Model: AtkRequestFromFloatingStockItem**
    - Fields: `request_id`, `item_id`, `quantity`.
    - Relationship: Belongs to `AtkItem`.
- **Approval Workflow:**
    - Integrate with the existing polymorphic `Approval` system.
    - Upon final approval, stock should be automatically transferred from `AtkFloatingStock` to the requesting division's `AtkDivisionStock`.
- **Filament Resource:**
    - Create `AtkRequestFromFloatingStockResource`.
    - Form: Repeater for items, searchable select for items (filtered by availability in floating stock).
    - Table: List requests with status, requester, and date.

## 3. Technical Requirements
- **Database:** Migrations for `atk_requests_from_floating_stock` and `atk_requests_from_floating_stock_items`.
- **Logic:**
    - Validation: Check if requested quantity is available in `AtkFloatingStock`.
    - Automation: Trigger stock movement on `final_approved` status.
- **UI:** Filament v4 resource with custom actions if needed.

## 4. Acceptance Criteria
- Users can create a request for items from Floating Stock.
- The request enters an approval flow.
- After approval, Floating Stock decreases and Division Stock increases.
- Transaction histories are recorded for both.
