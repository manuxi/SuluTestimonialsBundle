# Suche

[← Zurück zur README](../README.de.md)

Veröffentlichte Testimonials stehen im Suchindex `website` (Sulu Search, SEAL). Das Schema dieses Index gehört Sulu: `title`, `url`, `content`, `mediaId`, `authoredAt` und `metadata`. Alles andere, was ein Treffer anzeigen soll, liegt deshalb in `metadata.preview`. Felder außerhalb des Schemas verwirft der Suchadapter.

| Feld | Inhalt |
| --- | --- |
| `title`, `url`, `mediaId` | Titel, Adresse und Bild des Testimonials |
| `content` | Text und Quelle als reiner Text (ohne HTML) |
| `metadata.preview.description` | Anfang des Textes (höchstens 500 Zeichen) |
| `metadata.preview.author` | Name des Kontakts, nur wenn das Testimonial den Kontakt zeigt |
| `metadata.preview.rating` | Bewertung |
| `metadata.preview.source` | Quelle (z. B. Google) |
| `metadata.preview.date` | Datum (ISO 8601), nur wenn das Testimonial das Datum zeigt |

Leere Felder fehlen im Index. Ein Template sollte jedes Feld deshalb mit einem Standardwert lesen (`hit.metadata.preview.author ?? null`).

Beim Veröffentlichen und beim vollständigen Neuaufbau (`php bin/console sulu:search:reindex`) entsteht dasselbe Dokument (`Search/TestimonialWebsiteDocument`). Testimonials, die vor dieser Version indiziert wurden, brauchen einmal den Neuaufbau, damit die Vorschaudaten im Index stehen.
