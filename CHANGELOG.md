# 2.1.0
Improvements:
* Added `$guzzleConfig` to the `Connector`constructor
* Added `$guzzleConfig` to the `Client`constructor
* Added `illuminate/config && illuminate/support` version 10 in the `Composer.json`


# 2.0.2
Fixed retrieval of container field data, with session token.

* Added `getDataContainerContent` to the `Connector`
* Added `getDataContainerContent` to the `Client`
* Added `getDataContainerContent` to the `FmBaseRepository`
* **Breaking!** Renamed `getDataContainerUrlWithToken` to `getDataContainerToken` on `FmBaseRepository`

# 2.0.1
Lowered dependency version guzzlehttp/guzzle to 7.4.5

# 2.0.0
Major upgrade FileMaker Data API to 2.0.0

Improvements:
* Upgraded to PHP 8.0
* Added Eloquent dependencies (Laravel) v9 support
* Added FmBase abstract classes (FmBaseObject, FmBasePortalObject and FmBaseRepository) for dev friendly API record consuming
* Tuned the Readme.md
* Added newly added FmBase class usage instructions to Readme.md
* Added a Changelog markdown file
* Added and centralized Session Token management using the Illuminate Cache
* Fixed a BUG when the FileMaker config values are not configured yet (empty)
* Added the ability to consume container data with Session Token security
* Added the custom exception: FilemakerDataApiConfigInvalidException when the filemaker config has invalid values
* Upgraded dependency: `guzzlehttp/guzzle` from `7.0.0` to `7.5.0`

# dev-master
Released the 17th of September 2020
The basics needed for consuming the FileMaker Data API.
