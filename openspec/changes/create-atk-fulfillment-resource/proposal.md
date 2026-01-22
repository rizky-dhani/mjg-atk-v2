# Proposal: Create AtkFulfillment Resource

This proposal outlines the creation of a new Filament resource, `AtkFulfillment`, to manage the fulfillment of fully approved `AtkStockRequest` items. This resource will provide a dedicated interface for the "IPC initial" division to track and update the receipt of requested items.

## Motivation

Currently, the fulfillment process for approved `AtkStockRequest` items is not clearly defined or managed through a dedicated interface. This leads to inefficiencies in tracking and updating the status of received goods. A dedicated `AtkFulfillment` resource will streamline this process, improve visibility, and ensure that only authorized personnel from the "IPC initial" division can manage fulfillment.

## High-Level Design

The `AtkFulfillment` resource will:
- Be accessible via Filament, requiring appropriate permissions.
- Display a list of `AtkStockRequest` records that have been fully approved and are associated with a division marked as "IPC initial".
- Provide a detailed view for each `AtkStockRequest`, showing the requested items (`AtkStockRequestItem`) and their quantities.
- Allow users to update the `received_quantity` for each `AtkStockRequestItem`.
- Update the `FulfillmentStatus` of the `AtkStockRequest` based on the received quantities.

## Security Considerations

- **Access Control**: A new policy will be implemented to restrict access to this resource. Only users belonging to a division with the "IPC initial" role will be able to view and manage fulfillment.
- **Data Integrity**: Updates to `received_quantity` will be validated to prevent over-receiving or negative quantities.

## Impact

- **Improved Efficiency**: Streamlined fulfillment process.
- **Enhanced Visibility**: Clear overview of stock request fulfillment status.
- **Better Control**: Restricted access ensures only authorized personnel can manage fulfillment.