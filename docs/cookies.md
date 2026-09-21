# Cookie inventory

All first-party cookies use `Path=/` and `SameSite=Lax`. Cookies receive the
`Secure` attribute on HTTPS requests. Server-only cookies are also `HttpOnly`.

| Cookie | Owner | Purpose | Category | Lifetime | Secure | SameSite | HttpOnly |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `session` | PHP session/authentication | Maintains login, CSRF, and temporary album-code authorization | Necessary | Browser session; one hour for administrator impersonation | HTTPS | Lax | Yes |
| `remember_me` | Authentication | Restores an explicitly requested login using a revocable server-side token | Preference | 30 days | HTTPS | Lax | Yes |
| `CookiePreferences` | Consent interface | Records the visitor's cookie-category choices | Necessary | 1 year | HTTPS | Lax | No |
| `announcement-{id}` | Navigation announcements | Remembers a dismissal explicitly requested by the visitor | Necessary UI state | Browser session | HTTPS | Lax | No |

The legacy `hash`, `usr`, and `CookieShow` cookies are deleted when encountered
and are no longer used. An existing `searched` cookie is migrated once into the
server-side session and then deleted, so current visitors do not abruptly lose
album access. New album-code authorization is never persisted across browser
sessions.

With explicit consent, Google Analytics, Meta Pixel, and AddToAny may create
their own cookies. Their names and lifetimes are controlled by those providers.
Known analytics and social cookies are removed when the corresponding consent
is withdrawn.
