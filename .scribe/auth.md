# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_KEY}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Authenticate using a Bearer token obtained from <code>POST /api/auth/login</code> or <code>POST /api/auth/register</code>. Include the token in the <code>Authorization</code> header as <code>Bearer {token}</code>.
