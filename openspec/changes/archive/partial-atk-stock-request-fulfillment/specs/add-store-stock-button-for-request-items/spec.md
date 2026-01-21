## ADDED Requirements

### Requirement: Add "Store Stock" Button to AtkStockRequest Item Details
This requirement SHALL integrate a "Store Stock" button or action for each `AtkStockRequestItem` within the `AtkStockRequest` detail view in Filament, allowing users to record received quantities.

#### Scenario: User records partial stock for an item

Given a user is authenticated and on the `AtkStockRequest` detail page,
And an `AtkStockRequestItem` has a `requested_quantity` greater than its `received_quantity`,
When the user clicks the "Store Stock" button for that item,
And inputs a valid quantity (less than or equal to the remaining quantity) in the prompted form,
And submits the form,
Then the `received_quantity` for that `AtkStockRequestItem` MUST be updated,
And the inventory for the corresponding `AtkItem` MUST be incremented by the received quantity,
And a stock transaction MUST be recorded,
And the `AtkStockRequest`'s overall status SHOULD update to reflect partial or full fulfillment,
And a confirmation notification SHOULD be displayed to the user.

#### Scenario: User cannot input received quantity exceeding remaining quantity

Given a user is on the `AtkStockRequest` detail page,
And an `AtkStockRequestItem` has a `requested_quantity` greater than its `received_quantity`,
When the user clicks the "Store Stock" button for that item,
And inputs a quantity greater than the `requested_quantity` minus `received_quantity`,
And attempts to submit the form,
Then the system MUST display a validation error,
And the `received_quantity` MUST NOT be updated.

#### Scenario: "Store Stock" button is not visible for fully received items

Given a user is on the `AtkStockRequest` detail page,
And an `AtkStockRequestItem` has `received_quantity` equal to its `requested_quantity`,
When the user views that item,
Then the "Store Stock" button for that `AtkStockRequestItem` MUST NOT be visible.
