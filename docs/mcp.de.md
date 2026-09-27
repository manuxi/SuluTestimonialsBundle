# MCP-Tools

Ist [sulu/mcp-bundle](https://github.com/sulu/SuluMcpBundle) installiert, bringt das Bundle Tools mit, mit denen KI-Assistenten Testimonials bearbeiten können. Ohne dieses Bundle wird nichts geladen.

| Tool | Zweck | Recht |
|---|---|---|
| `sulu_testimonial_list` | Testimonials einer Sprache (Entwürfe), seitenweise | Ansehen |
| `sulu_testimonial_get` | Ein Testimonial mit allen Template-Feldern | Ansehen |
| `sulu_testimonial_create` | Testimonial als Entwurf anlegen | Hinzufügen |
| `sulu_testimonial_update` | Felder eines Testimonials ändern | Bearbeiten |

Die Rechte sind die des Sicherheitskontexts der Testimonials (`sulu.testimonials.testimonials`).

`sulu_testimonial_create` erwartet `locale`, `title`, `template` (`testimonial` oder `testimonial_details`) und die Template-Felder in `content`, zum Beispiel `{"contact": 3, "rating": 5, "source": "Google", "text": "<p>...</p>"}`. `rating` ist eine ganze Zahl auf der Sternskala. Das Template `testimonial` braucht für `url` eine Seitenbaum-Route, `testimonial_details` einen Pfad.

Veröffentlichen gehört nicht zu den Tools. Das geht im Admin oder mit den [Sammelaktionen](https://github.com/manuxi/SuluBulkActionsBundle).
