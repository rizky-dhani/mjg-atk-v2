## ADDED Requirements

### Requirement: Track Partial Quantity Received for ATK Stock Request Items
This requirement SHALL ensure that the quantity of stock received for individual `AtkStockRequestItem`s can be tracked and recorded, allowing for partial fulfillment of `AtkStockRequest`s.

#### Scenario: `received_quantity` column exists in `atk_stock_request_items` table

Given the application's database,
When a migration is run,
Then the `atk_stock_request_items` table MUST have a new `received_quantity` column of type `INTEGER`,
And the `received_quantity` column MUST default to `0`.

#### Scenario: `AtkStockRequest` status reflects partial fulfillment

Given an `AtkStockRequest` contains multiple `AtkStockRequestItem`s,
And some `AtkStockRequestItem`s have `received_quantity` less than their `requested_quantity`, while others have `received_quantity` equal to their `requested_quantity`,
When the `AtkStockRequest`'s overall status is evaluated,
Then the `AtkStockRequest`'s status MUST reflect a 'partially fulfilled' state.

Given an `AtkStockRequest` contains `AtkStockRequestItem`s,
And all `AtkStockRequestItem`s have `received_quantity` equal to their `requested_quantity`,
When the `AtkStockRequest`'s overall status is evaluated,
Then the `AtkStockRequest`'s status MUST reflect a 'fully fulfilled' state.
