## MODIFIED Requirements

### Requirement: Restructure Quantity Information Section for Readability
This requirement SHALL restructure the "Current, Max, and Available" stock information section within the `AtkStockRequest` and `AtkStockUsage` forms to improve readability.

#### Scenario: Quantity information section is more readable in AtkStockRequest form

Given a user is creating or updating an `AtkStockRequest`,
When the stock-related information (Current, Max, Available) is displayed for an `AtkItem`,
Then this section MUST be organized in a visually clear and concise manner, improving its readability.

#### Scenario: Quantity information section is more readable in AtkStockUsage form

Given a user is creating or updating an `AtkStockUsage`,
When the stock-related information (Current, Max, Available) is displayed for an `AtkItem`,
Then this section MUST be organized in a visually clear and concise manner, improving its readability.
