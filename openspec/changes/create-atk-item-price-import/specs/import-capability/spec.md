# Spec: ATK Item Price Import

## ADDED Requirements

### Requirement: Import ATK Item Prices from Excel
The system SHALL provide a way to bulk import item prices from an Excel file to reduce manual data entry.

#### Scenario: Admin imports item prices from a standardized Excel file
- **Given** an Excel file following the `List_Harga_ATK.xlsx` format (header on row 7).
- **When** the admin uploads the file and selects an effective date.
- **Then** the system should identify items by their description.
- **And** update or create `AtkItem` records with the correct category and UOM.
- **And** create new `AtkItemPrice` records with the specified effective date and price.
- **And** set the new prices as `is_active = true`, automatically deactivating previous prices for those items.

### Requirement: Categorization during import
The import process MUST correctly assign categories to items based on the section headers within the Excel sheet.

#### Scenario: Section headers update the current category
- **Given** a row where `No` is a letter (e.g., "A") and `Harga` is empty.
- **When** the import processes this row.
- **Then** it should update the `currentCategoryId` for subsequent items to match the category name in `Item Description`.