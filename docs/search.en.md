# Search

[← Back to README](../README.md)

Published testimonials are in the `website` search index (Sulu Search, SEAL). Sulu owns the schema of this index: `title`, `url`, `content`, `mediaId`, `authoredAt` and `metadata`. Anything else a hit should show therefore lives in `metadata.preview`. Fields outside the schema are dropped by the search adapter.

| Field | Content |
| --- | --- |
| `title`, `url`, `mediaId` | Title, address and image of the testimonial |
| `content` | Text and source as plain text (no HTML) |
| `metadata.preview.description` | Beginning of the text (500 characters at most) |
| `metadata.preview.author` | Name of the contact, only if the testimonial shows the contact |
| `metadata.preview.rating` | Rating |
| `metadata.preview.source` | Source (e.g. Google) |
| `metadata.preview.date` | Date (ISO 8601), only if the testimonial shows the date |

Empty fields are missing from the index, so a template should read each field with a default (`hit.metadata.preview.author ?? null`).

Publishing and the full rebuild (`php bin/console sulu:search:reindex`) write the same document (`Search/TestimonialWebsiteDocument`). Testimonials indexed before this version need one rebuild so that the preview data is in the index.
