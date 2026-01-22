## ADDED Requirements

### Requirement: Capture Item Price Snapshot on ATK Stock Request Item Creation
This requirement SHALL ensure that the price of an `AtkItem` is captured and stored as a snapshot when it is added to an `AtkStockRequest`.

#### Scenario: Item price is captured when a new ATK Stock Request Item is created

Given an `AtkItem` has a current price,
When a new `AtkStockRequestItem` is created for that `AtkItem`,
Then the `atk_stock_request_items` table MUST store the `AtkItem`'s price at the time of creation in a new `requested_price` column.

#### Scenario: Captured item price is displayed in Filament UI

Given an `AtkStockRequest` has `AtkStockRequestItem`s with a captured `requested_price`,
When a user views the details of that `AtkStockRequest` in the Filament admin panel,
Then the `requested_price` for each `AtkStockRequestItem` MUST be displayed.
