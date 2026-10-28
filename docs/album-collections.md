# Parent and child albums — #122

Status: requirements approved; implementation in progress on \`feature/122-parent-child-albums\`.

## Client-facing contract

- A collection is a logical parent, never a photo album. One level only.
- Parent page uses the current header, page title, breadcrumbs, Bootstrap grid, footer and protected cover cards.
- Child pages keep the current album header, viewer, favorites and download controls. Extend the existing breadcrumb with a real link to the parent **only if the viewer may access it**. Separate URLs preserve browser Back.
- Parent code unlocks the collection and all children for **viewing**, not downloads or sharing. A child code unlocks only its child. Codes are unique over both types.
- Client My Albums lists an authorized collection once rather than duplicating all its children. A child accessible independently appears on its own if the parent is inaccessible. No sibling/parent information leaks via direct URLs or APIs.
- Newly uploaded photos are immediately visible to already authorized clients.

## Administration contract

- Existing admin and uploader tables, album modals, file-upload components and workflows remain the foundation.
- Admins manage any collection and membership. Uploaders can create/edit/delete their own **unshared** collections, organize albums they own, and upload images; they cannot set access codes or sharing/download grants.
- A collection with a code or explicit client assignment is shared; only admins may attach, detach or move children of a shared collection. When either source or target is shared, membership moves are admin-only.
- Reordering is drag-and-drop with keyboard-accessible controls; order persists, newly attached children go last.
- Deleting a parent **only** deletes its grouping. All children, photos, favorites, individual codes and permissions survive as standalone albums. Show explicit confirmation of lost inherited access, never offer a cascading delete.
- Moving a child preserves its ID, images, favorites, owner, code, direct URL and individual permissions. Inherited viewing access is recalculated.
- Automatic cover uses the first available photo; admin/owning uploader can select a photo already in the appropriate child. The parent defaults to a child's cover. No separate image upload in #122.
- Covers must be served with server-side viewing authorization and protected derivatives only. Missing/deleted covers use a safe fallback. Do not reuse the public image-crop endpoint as a protected image endpoint.

## Test-first acceptance matrix

| Layer | Required cases |
| --- | --- |
| Unit | Admin/uploader/guest policy, ownership, shared-source/target move, code-change restriction, ordering validation |
| Integration | Parent/child identity and access inheritance, code uniqueness, no nested collections, stable IDs, safe detach/delete, cover reference and fallback |
| API | Methods and CSRF, anonymous/guest/user/uploader/admin auth, malformed or missing IDs, code collisions, shared collection mutation denial, ordering, deletion, no accidental new download rights |
| Page/HTTP | Parent/child pages, theme/header/footer/breadcrumbs, empty collection, unauthorized navigation, no leakage of protected image paths |
| Jest | Collection table rendering, single entry/no duplicates, modal reuse, reorder success/error and rollback, cover picker interactions |
| Behat | Parent code -> collection -> child -> browser Back; child-only access does not reveal parent; admin and uploader lifecycle; reorder persistence; protected cover |
| Existing regression | Standalone albums, uploaders' upload workflow, favorites, sharing, downloads, lazy loading, thumbnail protection |

Test implementation must precede each production slice; do not claim the feature complete until all layers and CI are green.
