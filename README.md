# Stashd Plex plugin

Plex Broadcast plugin for Stashd. It publishes eligible Vault video and
caption assets into a rebuildable Plex-compatible layout and can test, discover,
and refresh a configured Plex server.

Configure a Plex server connection, library, and API credential in Stashd. The
plugin uses the granted connection and credential; it does not access core
records or the Vault directly.

Install as `stashd/plex`. Run the provider contract check with `./tests/run.sh`;
application lifecycle coverage belongs to the core integration suite.
