# Proposal: Implement Partial Fulfillment for ATK Stock Request Items

This proposal outlines the implementation of a partial fulfillment mechanism for `AtkStockRequestItem`s. Currently, `AtkStockRequest`s are assumed to be fulfilled entirely at once. This change will introduce the ability to record stock received for individual items within a single `AtkStockRequest` in stages, allowing for more flexible and accurate inventory management. This involves adding a "Store Stock" button for each item and adapting the codebase to accommodate these changes.
