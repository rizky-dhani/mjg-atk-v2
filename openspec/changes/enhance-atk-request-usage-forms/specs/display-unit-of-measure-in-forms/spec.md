## ADDED Requirements

### Requirement: Display Unit of Measure in ATK Stock Request and Usage Forms
This requirement SHALL ensure that the `unit_of_measure` for each `AtkItem` is displayed alongside its requested quantity field in the Filament creation and update forms for `AtkStockRequest` and `AtkStockUsage`.

#### Scenario: Unit of measure is displayed for AtkItem in AtkStockRequest form

Given a user is creating or updating an `AtkStockRequest`,
And an `AtkItem` with a defined `unit_of_measure` is selected or present in the form,
When the quantity field for that `AtkItem` is displayed,
Then the `unit_of_measure` for that `AtkItem` MUST be visible adjacent to the quantity input field.

#### Scenario: Unit of measure is displayed for AtkItem in AtkStockUsage form

Given a user is creating or updating an `AtkStockUsage`,
And an `AtkItem` with a defined `unit_of_measure` is selected or present in the form,
When the quantity field for that `AtkItem` is displayed,
Then the `unit_of_measure` for that `AtkItem` MUST be visible adjacent to the quantity input field.
