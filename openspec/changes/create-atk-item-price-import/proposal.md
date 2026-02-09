# Proposal: Create ATK Item Price Import

Create an "Import" button on the `AtkItemPrice` list page to allow bulk uploading of item prices from an Excel file. This will use the mapping from `List_Harga_ATK.xlsx` and leverage `maatwebsite/excel`.

## User Review Required

> [!IMPORTANT]
> - Should we create new `AtkItem` records if they don't exist in the database but are present in the Excel file? (Assumed: Yes, following `AtkDivisionStockImport` pattern).
> - Should the `effective_date` be a required field in the import form for all imported prices? (Assumed: Yes).
> - Should we update the `unit_of_measure` of the `AtkItem` if it differs from the Excel? (Assumed: Yes).

- **Change ID:** `create-atk-item-price-import`
- **Target Specs:** `import-capability`
- **Related Specs:** `AtkItemPrice`
