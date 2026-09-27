# MCP tools

If [sulu/mcp-bundle](https://github.com/sulu/SuluMcpBundle) is installed, the bundle adds tools for AI assistants to work with testimonials. Without that bundle nothing is loaded.

| Tool | Purpose | Permission |
|---|---|---|
| `sulu_testimonial_list` | Testimonials of one locale (drafts), paginated | view |
| `sulu_testimonial_get` | One testimonial with all template fields | view |
| `sulu_testimonial_create` | Create a testimonial as a draft | add |
| `sulu_testimonial_update` | Change fields of a testimonial | edit |

The permissions are those of the testimonials security context (`sulu.testimonials.testimonials`).

`sulu_testimonial_create` takes `locale`, `title`, `template` (`testimonial` or `testimonial_details`) and the template fields in `content`, for example `{"contact": 3, "rating": 5, "source": "Google", "text": "<p>...</p>"}`. `rating` is a whole number on the star scale. The template `testimonial` needs a page tree route for `url`, `testimonial_details` a path.

Publishing is not part of the tools. Publish in the admin, or with the [bulk actions](https://github.com/manuxi/SuluBulkActionsBundle).
