# PPID Empat Lawang — REST API

Base URL: `{APP_URL}/api/v1` (e.g. `https://ppid.example.go.id/api/v1`)

All endpoints below require a **Bearer token**. Every response is JSON.

---

## 1. Authentication

The token is a single static secret stored in `.env`:

```env
API_TOKEN=secret-token
```

Send it on every request:

```http
Authorization: Bearer secret-token
Accept: application/json
```

```bash
curl -H "Authorization: Bearer $API_TOKEN" -H "Accept: application/json" \
  https://ppid.example.go.id/api/v1/news
```

| Situation | Result |
|---|---|
| Header missing / wrong token | `401 {"message":"Unauthenticated."}` |
| `API_TOKEN` empty or unset in `.env` | API is closed — every request returns `401` |

> **Security**
> - `secret-token` is a placeholder. Set a long random value in production:
>   `php -r "echo bin2hex(random_bytes(32));"`
> - The token grants full admin access (create/update/delete everything). Keep it server-side only — never put it in browser JS or a mobile app bundle.
> - Rotate by changing `API_TOKEN` in `.env`, then `php artisan config:clear` (or `config:cache` if you cache config).
> - Always call the API over HTTPS.

Rate limit: **120 requests / minute** per client IP. Over limit → `429 Too Many Requests` (see `Retry-After` header).

---

## 2. Conventions

### Request bodies
- JSON (`Content-Type: application/json`) for endpoints without files.
- `multipart/form-data` when uploading files.
- **Updating with a file:** PHP does not parse multipart bodies on real `PUT`/`PATCH`. Send `POST` with an extra field `_method=PUT` (or `PATCH`):

  ```bash
  curl -X POST https://.../api/v1/news/12 \
    -H "Authorization: Bearer $API_TOKEN" -H "Accept: application/json" \
    -F _method=PATCH -F image=@cover.jpg
  ```

### Partial updates
`PUT`/`PATCH` on resources are **partial**: send only fields you want to change. Required fields are required only on create.

### Booleans
Accepts `true/false`, `1/0`, `"1"/"0"`. `is_published` defaults to `true` on create when omitted.

### File URLs
Stored paths are returned as-is (`image`, `file_path`, …) **plus** a ready-to-use absolute URL field (`image_url`, `file_url`, `cover_image_url`, `photo_url`, `ktp_file_url`).

### Pagination
List endpoints marked *paginated* accept `?page=` and `?per_page=` (1–100) and return Laravel's paginator shape:

```json
{
  "current_page": 1,
  "data": [ { ... } ],
  "per_page": 15,
  "total": 42,
  "last_page": 3,
  "next_page_url": "https://.../api/v1/news?page=2",
  "prev_page_url": null,
  "...": "first_page_url, last_page_url, from, to, links, path"
}
```

### Status codes & errors

| Code | Meaning |
|---|---|
| `200` | OK — body is the resource |
| `201` | Created — body is the new resource |
| `204` | Deleted — empty body |
| `401` | Missing / wrong token |
| `404` | Resource not found — `{"message":"..."}` |
| `413` | Upload larger than server `post_max_size` |
| `422` | Validation failed |
| `429` | Rate limited |

Validation error shape:

```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": ["The title field is required."],
    "category": ["The selected category is invalid."]
  }
}
```

### Bulk actions
Documents, news and galleries have `POST …/bulk`:

```json
{ "ids": [1, 2, 3], "action": "publish" }
```

`action`: `publish` | `unpublish` | `delete`. Every id must exist (else `422`). Response: `{"affected": 3}`. `delete` also removes stored files.

---

## 3. Endpoint index

| Area | Method & path |
|---|---|
| **Documents** (Informasi Publik, Standar Layanan, Laporan, Pengadaan) | `GET/POST /documents`, `GET/PUT/PATCH/DELETE /documents/{id}`, `POST /documents/{id}/toggle-status`, `POST /documents/bulk` |
| **News** (Berita) | `GET/POST /news`, `GET/PUT/PATCH/DELETE /news/{id}`, `POST /news/{id}/toggle-status`, `POST /news/{id}/toggle-headline`, `POST /news/bulk` |
| **Galleries** (Galeri) | `GET/POST /galleries`, `GET/PUT/PATCH/DELETE /galleries/{id}`, `POST /galleries/{id}/toggle-status`, `POST /galleries/bulk`, `POST /galleries/{id}/photos`, `PUT /galleries/{id}/photos/order`, `DELETE /gallery-photos/{photoId}` |
| **Events** (Agenda) | `GET/POST /events`, `GET/PUT/PATCH/DELETE /events/{id}` |
| **Officials** (Profil Pejabat) | `GET/POST /officials`, `GET/PUT/PATCH/DELETE /officials/{id}`, `POST /officials/{id}/toggle-status` |
| **Profiles** (Profil PPID) | `GET /profiles`, `GET/PUT/PATCH /profiles/{slug}` |
| **Contacts** (Pesan Masuk) | `GET/POST /contacts`, `GET/DELETE /contacts/{id}`, `POST /contacts/{id}/read` |
| **Information requests** (Permohonan Informasi) | `GET/POST /information-requests`, `GET/PUT/PATCH/DELETE /information-requests/{id}` |
| **Complaints** (Pengajuan Keberatan) | `GET/POST /complaints`, `GET/PUT/PATCH/DELETE /complaints/{id}` |
| **Settings** | `GET/PUT/PATCH /settings/contact`, `GET/PUT/PATCH /settings/stats` |

---

## 4. Documents

One table backs four site sections; the section is the `category`.

| Section | `category` values |
|---|---|
| Informasi Publik | `informasi-publik-berkala`, `informasi-publik-serta-merta`, `informasi-publik-setiap-saat`, `informasi-publik-dikecualikan` |
| Standar Layanan | `standar_layanan_alur`, `standar_layanan_tata_cara`, `standar_layanan_permohonan`, `standar_layanan_keberatan`, `standar_layanan_sengketa`, `standar_layanan_sop`, `standar_layanan_maklumat`, `standar_layanan_biaya` |
| Laporan | `laporan_pemda`, `laporan_ppid` |
| Pengadaan | `pengadaan_info`, `pengadaan_regulasi` |

**Document object**

```json
{
  "id": 7,
  "title": "Laporan Tahunan PPID 2025",
  "category": "laporan_ppid",
  "description": "Ringkasan layanan informasi 2025",
  "file_path": "documents/Xy12....pdf",
  "external_url": null,
  "download_count": 0,
  "is_published": true,
  "created_at": "2026-09-25T04:00:00.000000Z",
  "updated_at": "2026-09-25T04:00:00.000000Z",
  "file_url": "https://bucket.s3.../documents/Xy12....pdf"
}
```

### `GET /documents` — list (paginated, default 15)

| Query | Description |
|---|---|
| `category` | Exact value **or prefix**, comma-separated. `laporan` → both laporan categories; `informasi-publik` → all four; `laporan_ppid,pengadaan_info` → both. |
| `is_published` | `1` / `0` |
| `search` | Matches title or description |
| `sort_field` | `title` \| `category` \| `created_at` (default) \| `is_published` |
| `sort_direction` | `asc` \| `desc` (default) |
| `page`, `per_page` | Pagination |

```bash
curl -H "Authorization: Bearer $API_TOKEN" \
  "https://.../api/v1/documents?category=standar_layanan&is_published=1&per_page=50"
```

### `GET /documents/{id}` — show

### `POST /documents` — create (`multipart/form-data` if file)

| Field | Rules |
|---|---|
| `title` | **required**, string ≤255 |
| `category` | **required**, one of the values above |
| `file` | optional file: pdf, doc, docx, xls, xlsx, csv, jpg, png — max 10 MB |
| `description` | optional string |
| `external_url` | optional string ≤255 (link instead of / in addition to a file) |
| `is_published` | optional boolean, default `true` |

```bash
curl -X POST https://.../api/v1/documents \
  -H "Authorization: Bearer $API_TOKEN" -H "Accept: application/json" \
  -F title="SOP Pelayanan Informasi" \
  -F category=standar_layanan_sop \
  -F file=@sop.pdf
```

→ `201` document object.

### `PUT|PATCH /documents/{id}` — update
Same fields, all optional. Uploading a new `file` deletes the old one. Use `POST` + `_method=PATCH` when sending a file.

### `DELETE /documents/{id}` → `204` (stored file removed)

### `POST /documents/{id}/toggle-status` — flip `is_published` → document object

### `POST /documents/bulk` — see [Bulk actions](#bulk-actions)

---

## 5. News (Berita)

**News object**

```json
{
  "id": 12,
  "title": "Rapat Koordinasi PPID",
  "slug": "rapat-koordinasi-ppid-1758770000",
  "content": "<p>HTML content...</p>",
  "image": "news/abc.jpg",
  "author": "Admin",
  "published_at": "2026-09-25 09:00:00",
  "is_published": 1,
  "is_headline": 0,
  "created_at": "...",
  "updated_at": "...",
  "image_url": "https://.../news/abc.jpg"
}
```

### `GET /news` — list (paginated, default 15)

| Query | Description |
|---|---|
| `is_published` | `1` / `0` |
| `is_headline` | `1` / `0` |
| `search` | Matches title |
| `sort_field` | `title` \| `author` \| `created_at` (default) \| `is_published` \| `published_at` |
| `sort_direction` | `asc` \| `desc` |

### `GET /news/{id}` — show

### `POST /news` — create

| Field | Rules |
|---|---|
| `title` | **required**, string ≤255 |
| `content` | **required**, string (HTML allowed) |
| `image` | optional image: jpg, jpeg, png — max 2 MB |
| `author` | optional string ≤100, default `Admin` |
| `published_at` | optional date (`2026-09-25 09:00`), default now. Future date = scheduled (hidden on public site until then) |
| `is_published` | optional boolean, default `true` |

`slug` is generated: `slug(title)-<unix time>`.

```bash
curl -X POST https://.../api/v1/news \
  -H "Authorization: Bearer $API_TOKEN" -H "Accept: application/json" \
  -F title="Rapat Koordinasi PPID" -F content="<p>Isi berita</p>" -F image=@foto.jpg
```

### `PUT|PATCH /news/{id}` — update
All fields optional. Changing `title` regenerates slug as `slug(title)-{id}`. New `image` replaces and deletes the old one.

### `DELETE /news/{id}` → `204`

### `POST /news/{id}/toggle-status` — flip `is_published`

### `POST /news/{id}/toggle-headline`
Makes the item the **only** headline (clears any other); calling again on the current headline un-sets it.

### `POST /news/bulk` — see [Bulk actions](#bulk-actions)

---

## 6. Galleries (Galeri)

**Gallery object** (`show` includes `items` sorted by `order`)

```json
{
  "id": 3,
  "title": "Rapat Teknis SDI",
  "slug": "rapat-teknis-sdi",
  "description": null,
  "type": "photo",
  "cover_image": "galleries/covers/x.jpg",
  "url": null,
  "is_published": 1,
  "created_at": "...",
  "updated_at": "...",
  "cover_image_url": "https://.../galleries/covers/x.jpg",
  "items": [
    {
      "id": 41, "gallery_id": 3, "image_path": "galleries/items/a.jpg",
      "caption": "Pembukaan", "order": 1,
      "image_url": "https://.../galleries/items/a.jpg"
    }
  ]
}
```

### `GET /galleries` — list (paginated, default 12)
Query: `is_published`, `page`, `per_page`. Each item includes `items_count`.

### `GET /galleries/{id}` — show with photos

### `POST /galleries` — create album (multipart)

| Field | Rules |
|---|---|
| `title` | **required**, string ≤255 |
| `cover_image` | **required** image: jpeg, png, jpg, gif, webp — max 10 MB |
| `description` | optional string |
| `is_published` | optional boolean, default `true` |

`slug` is generated once from the title and never changes (shared links stay valid).

### `PUT|PATCH /galleries/{id}` — update
`title`, `description`, `cover_image`, `is_published` — all optional.

### `DELETE /galleries/{id}` → `204` (cover + all photos deleted)

### `POST /galleries/{id}/toggle-status` — flip `is_published`

### `POST /galleries/bulk` — see [Bulk actions](#bulk-actions)

### `POST /galleries/{id}/photos` — add photos (multipart)

| Field | Rules |
|---|---|
| `photos[]` | **required**, one or more images: jpeg, png, jpg, gif, webp — max 10 MB each |
| `captions[]` | optional, caption per photo (same index as `photos[]`) |

New photos are appended after the current last `order`.

```bash
curl -X POST https://.../api/v1/galleries/3/photos \
  -H "Authorization: Bearer $API_TOKEN" -H "Accept: application/json" \
  -F "photos[]=@1.jpg" -F "captions[]=Pembukaan" \
  -F "photos[]=@2.jpg" -F "captions[]=Diskusi"
```

→ `201` array of created photo items.

> Total request size is capped by server `post_max_size` / `upload_max_filesize`. For many or big photos, send several smaller requests.

### `PUT /galleries/{id}/photos/order` — reorder

```json
{ "orders": { "41": 2, "42": 1 } }
```

Keys = photo ids (must belong to this gallery; others are ignored), values = new order. → gallery with items.

### `DELETE /gallery-photos/{photoId}` → `204` (file deleted)

---

## 7. Events (Agenda)

**Event object**

```json
{
  "id": 5,
  "title": "Sosialisasi KIP",
  "slug": "sosialisasi-kip-ab12c",
  "description": "Sosialisasi keterbukaan informasi",
  "location": "Aula Setda",
  "start_date": "2026-10-01 09:00:00",
  "end_date": "2026-10-01 12:00:00",
  "created_at": "...",
  "updated_at": "..."
}
```

### `GET /events` — list (not paginated, sorted by `start_date` asc)

| Query | Description |
|---|---|
| `start` | Date; events whose `start_date` ≥ that day 00:00:00 |
| `end` | Date; events whose `start_date` ≤ that day 23:59:59 |

```bash
curl -H "Authorization: Bearer $API_TOKEN" "https://.../api/v1/events?start=2026-10-01&end=2026-10-31"
```

### `GET /events/{id}` — show

### `POST /events` — create (JSON)

| Field | Rules |
|---|---|
| `title` | **required**, string ≤255 |
| `description` | **required**, string |
| `location` | **required**, string ≤255 |
| `start_date` | **required**, datetime (`2026-10-01 09:00` or ISO 8601) |
| `end_date` | optional, ≥ `start_date`; default = start day 23:59:59 |

```bash
curl -X POST https://.../api/v1/events \
  -H "Authorization: Bearer $API_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"title":"Sosialisasi KIP","description":"...","location":"Aula Setda","start_date":"2026-10-01 09:00"}'
```

### `PUT|PATCH /events/{id}` — update (all fields optional; title change regenerates slug)

### `DELETE /events/{id}` → `204`

---

## 8. Officials (Profil Pejabat)

**Official object**

```json
{
  "id": 1, "name": "Nama Pejabat", "position": "Bupati",
  "photo": "officials/p.jpg", "bio": "...", "order": 1, "is_published": true,
  "created_at": "...", "updated_at": "...",
  "photo_url": "https://.../officials/p.jpg"
}
```

### `GET /officials` — list (not paginated, sorted by `order`, then `name`)
Query: `is_published`.

### `GET /officials/{id}` — show

### `POST /officials` — create

| Field | Rules |
|---|---|
| `name` | **required**, string ≤255 |
| `position` | **required**, string ≤255 |
| `photo` | optional image: jpeg, png, jpg, gif — max 10 MB |
| `bio` | optional string |
| `order` | optional integer ≥0, default `0` |
| `is_published` | optional boolean, default `true` |

### `PUT|PATCH /officials/{id}` — update (all optional; new `photo` replaces old)

### `DELETE /officials/{id}` → `204`

### `POST /officials/{id}/toggle-status` — flip `is_published`

---

## 9. Profiles (Profil PPID)

Fixed pages addressed by `slug` (seeded: `tentang-ppid`, `visi-misi`, `struktur-organisasi`, `tugas-fungsi`). Cannot be created or deleted via API.

**Profile object**

```json
{
  "id": 2, "title": "Visi & Misi", "slug": "visi-misi",
  "content": "<p>HTML...</p>", "image": null, "type": "visi_misi",
  "created_at": "...", "updated_at": "...", "image_url": null
}
```

### `GET /profiles` — all profiles

### `GET /profiles/{slug}` — show

### `PUT|PATCH /profiles/{slug}` — update

| Field | Rules |
|---|---|
| `title` | optional string ≤255 |
| `content` | optional string (HTML) |
| `image` | optional image: jpeg, png, jpg, gif — max 10 MB (replaces old) |

---

## 10. Contacts (Pesan Masuk)

Messages from the site's contact form.

**Contact object**

```json
{
  "id": 9, "name": "Siti", "email": "siti@mail.com", "phone": "0812...",
  "subject": "Pertanyaan", "message": "...", "is_read": 0,
  "created_at": "...", "updated_at": "..."
}
```

### `GET /contacts` — list (paginated, newest first)
Query: `is_read` (`1`/`0`), `page`, `per_page`.

### `GET /contacts/{id}` — show

### `POST /contacts` — submit a message (e.g. from another front-end)

| Field | Rules |
|---|---|
| `name` | **required**, string ≤255 |
| `email` | **required**, email |
| `phone` | optional string ≤50 |
| `subject` | **required**, string ≤255 |
| `message` | **required**, string |

### `POST /contacts/{id}/read` — mark as read → contact object

### `DELETE /contacts/{id}` → `204`

---

## 11. Information requests (Permohonan Informasi)

**Request object**

```json
{
  "id": 4,
  "ticket_number": "REG-1758770000",
  "name": "Andi", "nik": "1671...", "ktp_file": "ktp_files/k.jpg",
  "address": "Tebing Tinggi", "email": "andi@mail.com", "phone": "0813...",
  "info_requested": "Data APBD 2025", "reason": "Penelitian",
  "delivery_method": "email",
  "status": "pending", "admin_note": null,
  "created_at": "...", "updated_at": "...",
  "ktp_file_url": "https://.../ktp_files/k.jpg"
}
```

> Contains personal data (NIK, KTP scan). Do not expose these responses publicly.

### `GET /information-requests` — list (paginated, newest first)

| Query | Description |
|---|---|
| `status` | `pending` \| `processed` \| `approved` \| `rejected` |
| `ticket_number` | Exact ticket, e.g. `REG-1758770000` (status lookup) |

### `GET /information-requests/{id}` — show

### `POST /information-requests` — submit a request

| Field | Rules |
|---|---|
| `name` | **required**, string ≤255 |
| `nik` | **required**, string ≤32 |
| `address` | **required**, string ≤255 |
| `email` | **required**, email |
| `phone` | **required**, string ≤50 |
| `info_requested` | **required**, string |
| `reason` | **required**, string |
| `delivery_method` | **required**, e.g. `email`, `pos`, `ambil_langsung` |
| `ktp_file` | optional file: jpg, jpeg, png, pdf — max 2 MB |

→ `201` with generated `ticket_number` (`REG-<unix time>`) and `status: "pending"`.

### `PUT|PATCH /information-requests/{id}` — process (admin)

| Field | Rules |
|---|---|
| `status` | **required**: `pending` \| `processed` \| `approved` \| `rejected` |
| `admin_note` | optional string |

### `DELETE /information-requests/{id}` → `204` (KTP file deleted)

---

## 12. Complaints (Pengajuan Keberatan)

**Complaint object**

```json
{
  "id": 2,
  "ticket_number": "ADU-1758770000",
  "request_ticket_number": "REG-1758700000",
  "name": "Andi", "email": "andi@mail.com", "phone": "0813...",
  "reason_complaint": "Permohonan tidak ditanggapi",
  "status": "pending", "admin_reply": null,
  "created_at": "...", "updated_at": "..."
}
```

### `GET /complaints` — list (paginated, newest first)
Query: `status` (`pending` \| `processed` \| `resolved` \| `rejected`), `ticket_number`.

### `GET /complaints/{id}` — show

### `POST /complaints` — submit

| Field | Rules |
|---|---|
| `name` | **required**, string ≤255 |
| `email` | **required**, email |
| `phone` | **required**, string ≤50 |
| `reason_complaint` | **required**, string |
| `request_ticket_number` | optional, original `REG-…` ticket |

→ `201` with `ticket_number` (`ADU-<unix time>`), `status: "pending"`.

### `PUT|PATCH /complaints/{id}` — process (admin)

| Field | Rules |
|---|---|
| `status` | **required**: `pending` \| `processed` \| `resolved` \| `rejected` |
| `admin_reply` | optional string |

### `DELETE /complaints/{id}` → `204`

---

## 13. Settings

### `GET /settings/contact` — contact page settings

```json
{
  "id": 1,
  "address": "Jl. ... Tebing Tinggi",
  "phones": ["0702-123456"],
  "emails": ["ppid@example.go.id"],
  "working_hours": ["Senin–Kamis 08:00–16:00", "Jumat 08:00–11:00"],
  "maps_embed": "<iframe ...>",
  "social_media": [
    { "platform": "instagram", "name": "Instagram", "username": "@ppid",
      "url": "https://instagram.com/ppid", "icon": "bi-instagram", "color": "#E4405F" }
  ],
  "created_at": "...", "updated_at": "..."
}
```

### `PUT|PATCH /settings/contact` — update (JSON)

| Field | Rules |
|---|---|
| `address` | optional string |
| `maps_embed` | optional string (iframe HTML) |
| `phones` | optional array of strings |
| `emails` | optional array of emails |
| `working_hours` | optional array of strings |
| `social_media` | optional array of objects; each needs `platform` and `name`; `username`, `url`, `icon`, `color` optional. `platform` is overwritten from the `url` domain when recognised (instagram, facebook, youtube, twitter/x, tiktok, whatsapp, telegram, linkedin) |

Arrays **replace** the stored list (empty entries dropped). Omitted keys stay unchanged.

```bash
curl -X PATCH https://.../api/v1/settings/contact \
  -H "Authorization: Bearer $API_TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"phones":["0702-123456","0812-0000-0000"]}'
```

### `GET /settings/stats` — homepage stats

```json
{ "stat_satisfaction_index": "98%" }
```

### `PUT|PATCH /settings/stats`

| Field | Rules |
|---|---|
| `stat_satisfaction_index` | **required**, string ≤20 (e.g. `"97,5%"`) |

---

## 14. Not exposed via API

| Web feature | Why |
|---|---|
| Admin account settings (`/admin/akun`) | Tied to a logged-in user session; the API token has no user identity. |
| Summernote editor image upload | Editor-internal helper; upload images through the resource's own image field. |
| Chunked gallery upload | Browser workaround for upload size limits; use `POST /galleries/{id}/photos` in several requests. |

---

## 15. Setup checklist

1. Add to `.env`: `API_TOKEN=<long-random-value>`
2. `php artisan config:clear` (or `php artisan config:cache` in production)
3. Test: `curl -i -H "Authorization: Bearer <token>" {APP_URL}/api/v1/profiles` → `200`
4. Without header → `401`
