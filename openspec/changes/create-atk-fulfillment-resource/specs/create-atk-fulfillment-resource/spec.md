## ADDED Requirements

### Requirement: AtkFulfillment Resource Creation
The `AtkFulfillment` resource SHALL provide a dedicated interface for managing the fulfillment process of ATK stock requests.

#### Scenario: Dedicated Fulfillment Management Interface
Given that `AtkStockRequest` items need to be fulfilled,
When a user with appropriate permissions and division access views the Filament admin panel,
Then they should see a new resource named "Fulfillment" (or similar, based on convention) dedicated to managing the fulfillment process of ATK stock requests.

#### Scenario: Visibility of Fully Approved Requests
Given that `AtkStockRequest`s go through an approval process,
When a user accesses the `AtkFulfillment` resource,
Then they should only see `AtkStockRequest` records that have been fully approved.

#### Scenario: Division-Specific Visibility ("IPC initial")
Given that fulfillment is managed by specific divisions,
When a user from a division with "IPC initial" accesses the `AtkFulfillment` resource,
Then they should only see `AtkStockRequest` records associated with their own "IPC initial" division.
And users from other divisions should not see this resource or any requests.

#### Scenario: Display of Request Information
Given a list of approved `AtkStockRequest`s,
When viewing the `AtkFulfillment` resource list,
Then each request should display key information such as:
- Request Number
- Requester Name
- Division Name
- Current Fulfillment Status (Pending, Partially Fulfilled, Fulfilled)

#### Scenario: Update Item Received Quantity
Given a detailed view of an `AtkStockRequest` within the `AtkFulfillment` resource,
When a user updates the `received_quantity` for an `AtkStockRequestItem`,
Then the system should save the updated quantity.
And the `AtkStockRequestItem.received_quantity` should not exceed the `AtkStockRequestItem.quantity`.
And `received_quantity` should not be negative.

#### Scenario: Automatic Fulfillment Status Update
Given that `AtkStockRequestItem`s `received_quantity` is updated,
When the changes are saved,
Then the `FulfillmentStatus` of the parent `AtkStockRequest` should automatically update based on the received quantities of its items:
- If all items are fully received (`received_quantity >= quantity`), status should be `Fulfilled`.
- If some items are partially received (`received_quantity > 0` for some, but not all fully received), status should be `PartiallyFulfilled`.
- If no items are received (`received_quantity == 0` for all), status should be `Pending`.

### Requirement: Authorization Policy for AtkFulfillment Resource
The authorization policy MUST ensure that only users from the "IPC initial" division can access and manage the `AtkFulfillment` resource.

#### Scenario: Access for IPC Division
Given that the "IPC initial" division is responsible for fulfillment,
When a user belongs to a division with the "IPC initial" `initial` attribute,
Then they should be able to view and manage the `AtkFulfillment` resource.

#### Scenario: Restricted Access for Other Divisions
Given that other divisions are not responsible for fulfillment,
When a user does not belong to a division with the "IPC initial" `initial` attribute,
Then they should not be able to view or manage the `AtkFulfillment` resource.
