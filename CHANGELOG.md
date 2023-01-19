
# v2.0.0
Major upgrade FileMaker Data API to v2.0.0

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

# dev-master
Released the 17th of September 2020
The basics needed for consuming the FileMaker Data API.