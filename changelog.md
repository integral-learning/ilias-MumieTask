# MumieTask - Changelog

All important changes to this plugin will be documented in this file.

## [v2.1.1] - 2022-10-18
### Changed
- Plugin now supports Ilias v7.14

## [v2.1.0] - 2022-09-28
### Added 
- Sharing grades for the same MUMIE problems with other Ilias repositories can now be disabled.

### Changed
- Plugin now supports Ilias v7.13

### Fixed
- Fixed an issue where pushing **Force update** button in MUMIE Task's grade settings didn't work 

## [v2.0.1] - 2021-08-26
### Changed
- Added support for Ilias 6.10

## [v2.0.0] - 2021-08-12
### Changed
- Adjusted plugin to work with ilias 6. This version of MUMIE Task no longer supports Ilias versions prior to 6.0

### Fixed
- Fixed an error in MUMIE server form, where whitespace around the URL prefix could cause an error
- Fixed a warning during verification of SSO attempts that appeared in the server logs

## [v1.2.0] - 2021-04-29
### Fixed
- Editing a MUMIE Task no longer changes the selected problem.
- Launch container settings are no longer ignored when opening a MUMIE Task

### Changed
- Reworked UI for problem selection. Ilias now uses an external problem selector

## [v1.1.2] - 2020-11-05
### Fixed
- Fixed a bug, where a not attempted MUMIE Task could crash a container.
- Corrected some spelling errors

## [v1.1.1] - 2020-08-06
### Fixed
- Fixed an issue where a malformed grade sync request could cause an container to stop working.

## [v1.1.0] - 2020-06-16
### Added
- LEMON servers can now be added to MumieTask

### Changed
- MumieTask now uses proxy server if configured in Ilias

## [v1.0.1] - 2020-02-05

### Changed
- The plugin now sends a versioned request for available courses and Tasks to a MUMIE server

### Fixed
- Fixed a bug for versions greater than 5.4.6, where unencoded urls caused an error during single log out

## [v.1.0.0] - 2019-11-28
Initial release
