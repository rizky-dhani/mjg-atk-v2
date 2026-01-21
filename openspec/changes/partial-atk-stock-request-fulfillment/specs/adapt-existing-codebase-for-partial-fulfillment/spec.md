## MODIFIED Requirements

### Requirement: Adapt Existing Codebase to Handle Partial ATK Stock Request Fulfillment
This requirement SHALL ensure that existing parts of the codebase that interact with `AtkStockRequest`s correctly account for and function with partial fulfillment states.

#### Scenario: Reporting accurately reflects partially fulfilled requests

Given existing reports that display `AtkStockRequest` status or fulfillment,
When an `AtkStockRequest` is partially fulfilled,
Then these reports MUST accurately reflect the `partially_fulfilled` status and quantities.

#### Scenario: Stock level checks consider received quantities

Given automated stock level checks or inventory management logic,
When an `AtkStockRequestItem` has a `received_quantity`,
Then these checks MUST correctly incorporate the `received_quantity` into their calculations and decision-making.

#### Scenario: Approval processes are compatible with partial fulfillment statuses

Given an `AtkStockRequest` has reached a `partially_fulfilled` status,
When any associated approval processes are invoked or reviewed,
Then these processes MUST be compatible with and correctly interpret the `partially_fulfilled` status, without causing errors or incorrect state transitions.
