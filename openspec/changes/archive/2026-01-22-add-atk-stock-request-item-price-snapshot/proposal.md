# Proposal: Add Item Price Snapshot to ATK Stock Request Items

This proposal outlines the implementation of an "Item Price Snapshot" feature for `AtkStockRequestItem`s. When an `AtkStockRequest` is created, the price of each `AtkItem` at the moment of request creation will be captured and stored. This will allow for historical price comparison against current `AtkItem` prices.
