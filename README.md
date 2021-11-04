# craft3-universal-dam-integrator

The purpose of this Craft CMS plugin is to offer a generalized way of interacting with ideally most digital asset manager (DAM), a CMS for assets.

## How does this differ than the Craft CMS CRUD API?

Currently, Craft CMS does not off the option to save only metadata concerning an asset. The CMS makes the assumption that there will always be certain fields populated, such as `tempFilePath` and `newLocation`, etc.

This plugin overrides the core Craft CRUD API functions such that conditional checks for these upload-specific fields are removed, including validation, so that asset metadata records can be inserted into the `elements`, `assets`, `content` and `element_sites` tables without an actual upload.

## External volumes vs read-only assets

This plugin does not make use of any external volume, and insteads treats the asset files themselves as read-only (no transformations from within Craft, currently) such that the hosting of assets is not duplicated between the DAM hosting provider and Craft hosting.

## Reconciliation

Notes forthcoming