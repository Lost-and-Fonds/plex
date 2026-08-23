# Dependency decisions

Plex uses PHP SimpleXML/XMLWriter and the SDK HTTP capability. No Plex client library is used: the plugin's XML surface is small and standard-library parsing keeps escaping and brokered requests explicit.
