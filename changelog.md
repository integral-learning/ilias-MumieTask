# MumieTask - Changelog

All important changes to this plugin will be documented in this file.

## [v1.3.0] - TODO
### Changed
- Adjusted plugin to work with ilias 6. MUMIE Task no longer supports Ilias versions prior to 6.0

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