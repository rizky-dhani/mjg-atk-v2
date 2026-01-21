# Design Document: ATK Stock Request and Usage Forms Readability Enhancements

## Overview

This document describes the design for improving the readability and user experience of Filament forms for `AtkStockRequest` and `AtkStockUsage`. The enhancements focus on providing clearer context for quantity fields by displaying units of measure and organizing stock information more effectively.

## User Interface Integration (Filament Forms)

Modifications will be made to the `AtkStockRequestResource` and `AtkStockUsageResource` forms.

### Display Unit of Measure

The `unit_of_measure` associated with each `AtkItem` will be displayed directly within the form, adjacent to the quantity input field.

*   **Location**: The unit of measure will be appended to the quantity input field or displayed as an inline text/badge immediately following it.
*   **Data Source**: The `unit_of_measure` will be retrieved from the related `AtkItem` model. This implies that the `AtkItem` model (and potentially `AtkStockRequestItem`/`AtkStockUsageItem` models if they hold a relationship to `AtkItem`) must expose this attribute.
*   **Example**: Instead of "Quantity: [ 10 ]", it might appear as "Quantity: [ 10 ] pcs" or "Quantity: [ 10 ] boxes".

### Restructure "Current, Max, and Available" Section

The section displaying "Current Stock", "Maximum Stock", and "Available Stock" (or similar stock-related information) will be reorganized to improve its readability and make it easier for users to digest.

*   **Layout**: Consider using Filament's `Grid` or `Fieldset` components to group related information logically.
*   **Visual Cues**: Use appropriate text styling (e.g., bolding, different colors) or icons to highlight key stock figures.
*   **Context**: Ensure that the labels clearly indicate what each number represents.

## Implementation Details

*   **Form Schema Modification**: The Filament form schemas for `AtkStockRequestResource` and `AtkStockUsageResource` will be updated.
*   **Relationship Access**: Ensure that the `AtkItem` model is eagerly loaded or easily accessible to retrieve the `unit_of_measure` for each item in the repeater fields.
*   **Custom Form Fields**: Custom Filament form fields or Livewire components might be utilized to achieve the desired display and restructuring, especially for dynamic `unit_of_measure` display alongside the quantity input.

## Future Considerations

*   Dynamic updates to stock information based on quantity changes.
*   Clearer visual indicators for stock levels (e.g., progress bars, color-coded warnings).
