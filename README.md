# DumpBadLogins

Plugin used for debugging issues with Xbox Live sign-in.

## WARNING: This plugin allows non-Xbox players to join the server. Ensure that you have backup authentication methods in case players are not logged into Xbox Live.

Logins from bad players will be written to the `xbl_not_authed` file in the plugin's data folder. It contains two types of entries:
- `not XBOX`: Player was probably using a proxy.
- `XBOX but not signed`: Player might have experienced a bug with Xbox Live sign-in or is using a third-party client.