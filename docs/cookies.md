# Cookie inventory

All first-party cookies use `Path=/` and `SameSite=Lax`. Cookies receive the
`Secure` attribute on HTTPS requests. Server-only cookies are also `HttpOnly`.

| Cookie | Owner | Purpose | Category | Lifetime | Secure | SameSite | HttpOnly |
| --- | --- | --- | --- | --- | --- | --- | --- |
| `session` | PHP session/authentication | Maintains login, CSRF, and temporary album-code authorization | Necessary | Browser session; one hour for administrator impersonation | HTTPS | Lax | Yes |
| `remember_me` | Authentication | Restores an explicitly requested login using a revocable server-side token | Preference | 30 days | HTTPS | Lax | Yes |
| `searched` | Album authorization | Remembers albums unlocked with a valid album code | Preference | 30 days | HTTPS | Lax | Yes |
| `CookiePreferences` | Consent interface | Records the visitor's cookie-category choices | Necessary | 1 year | HTTPS | Lax | No |
| `announcement-{id}` | Navigation announcements | Remembers a dismissal explicitly requested by the visitor | Necessary UI state | Browser session | HTTPS | Lax | No |

The legacy `hash`, `usr`, and `CookieShow` cookies are deleted when encountered
and are no longer used. Album-code authorization is stored in the current
session and, when preference cookies are accepted, in the `searched` cookie.

With explicit consent, Google Analytics, Meta Pixel, and AddToAny may create
their own cookies. Their names and lifetimes are controlled by those providers.
Known analytics and social cookies are removed when the corresponding consent
is withdrawn.
