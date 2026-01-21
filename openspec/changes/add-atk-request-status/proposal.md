# Proposal: Add Status Column to ATK Stock Requests and Implement Draft/Publish Functionality

This proposal outlines the implementation of a new `status` column to the `atk_stock_requests` table and the integration of "Save to Draft" and "Publish" functionalities within the Filament admin panel for ATK Stock Requests.

The new `status` column will be an ENUM type with `draft` and `published` as its values, allowing users to save their stock requests as drafts before final publication. This enhances the workflow by providing an intermediate state for stock requests.
