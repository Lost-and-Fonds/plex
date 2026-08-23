# Stashd Plex plugin

Plex Broadcast plugin for Stashd. It publishes eligible Vault video and
caption assets into a rebuildable Plex-compatible layout and can test, discover,
and refresh a configured Plex server.

Configure a Plex server connection, library, and API credential in Stashd. The
plugin uses the granted connection and credential; it does not access core
records or the Vault directly.

Install as `stashd/plex`. Run `composer test`; application lifecycle coverage
belongs to the core integration suite.

## Release artifact

Core materializes this package from its locked Composer graph; this provider declares no helpers.
