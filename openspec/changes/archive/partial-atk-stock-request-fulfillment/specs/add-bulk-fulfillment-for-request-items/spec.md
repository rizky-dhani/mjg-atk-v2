## ADDED Requirements

### Requirement: Implement Bulk Fulfillment for Selected ATK Stock Request Items
This requirement SHALL provide a mechanism for users to record stock received for multiple selected `AtkStockRequestItem`s within a single `AtkStockRequest` simultaneously.

#### Scenario: User performs bulk stock storage for selected items

Given a user is authenticated and on the `AtkStockRequest` detail page,
And multiple `AtkStockRequestItem`s have `requested_quantity` greater than their `received_quantity`,
When the user selects these `AtkStockRequestItem`s using the provided selection mechanism,
And clicks the "Bulk Store Stock" button,
And inputs valid quantities for each selected item in the prompted bulk form,
And submits the form,
Then the `received_quantity` for each selected `AtkStockRequestItem` MUST be updated,
And the inventory for their corresponding `AtkItem`s MUST be incremented by the respective received quantities,
And stock transactions MUST be recorded for each received item,
And the `AtkStockRequest`'s overall status SHOULD update to reflect partial or full fulfillment,
And a confirmation notification SHOULD be displayed to the user.

#### Scenario: "Bulk Store Stock" button visibility

Given a user is authenticated and on the `AtkStockRequest` detail page,
When no `AtkStockRequestItem`s are selected,
Then the "Bulk Store Stock" button MUST NOT be visible or enabled.

Given a user is authenticated and on the `AtkStockRequest` detail page,
When one or more `AtkStockRequestItem`s are selected,
Then the "Bulk Store Stock" button MUST be visible and enabled.
