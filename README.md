# DumpBadLogins

PocketMine-MP plugin used for debugging issues with Xbox Live sign-in.

**This plugin REQUIRES that `online-mode` be enabled in server.properties.**

## WARNING
This plugin allows non-Xbox players to join the server. Ensure that you have backup authentication methods in case players are not logged into Xbox Live.

## Dumping logins
Logins from bad players will be written to the `xbl_not_authed` file in the plugin's data folder. It contains two types of entries:
- `not XBOX`: Player was probably using a proxy.
- `XBOX but not signed`: Player might have experienced a bug with Xbox Live sign-in or is using a third-party client.