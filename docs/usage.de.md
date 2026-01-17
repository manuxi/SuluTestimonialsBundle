# Verwendung & Templates

[← Zurück zur README](../README.de.md)

## Content Types

### Smart Content

Die flexibelste Methode zur Anzeige von Testimonials. Unterstützt Filterung, Sortierung und Paginierung.

```xml
<property name="testimonials" type="smart_content">
    <meta>
        <title lang="en">Testimonials</title>
        <title lang="de">Testimonials</title>
    </meta>
    <params>
        <param name="provider" value="testimonials"/>
        <param name="max_per_page" value="5"/>
        <param name="page_parameter" value="page"/>
    </params>
</property>
```

**Verfügbare Sortieroptionen:**
- `title` - Alphabetisch nach Titel
- `rating` - Nach Bewertungswert
- `authored` - Nach Erstellungsdatum
- `created` - Nach Anlagedatum
- `changed` - Nach letzter Änderung

### Einzelauswahl

Ein bestimmtes Testimonial auswählen:

```xml
<property name="featured_testimonial" type="single_testimonial_selection">
    <meta>
        <title lang="de">Ausgewähltes Testimonial</title>
    </meta>
</property>
```

### Mehrfachauswahl

Mehrere Testimonials auswählen:

```xml
<property name="testimonials" type="testimonial_selection">
    <meta>
        <title lang="de">Ausgewählte Testimonials</title>
    </meta>
</property>
```

## Twig-Funktionen

### `sulu_resolve_testimonial`

Ein einzelnes Testimonial per ID auflösen:

```twig
{% set testimonial = sulu_resolve_testimonial(42, app.request.locale) %}
{% if testimonial %}
    <h3>{{ testimonial.title }}</h3>
{% endif %}
```

### `sulu_get_testimonials`

Eine Liste von Testimonials abrufen:

```twig
{% set testimonials = sulu_get_testimonials(10, app.request.locale) %}
{% for testimonial in testimonials %}
    <div>{{ testimonial.title }}</div>
{% endfor %}
```

## Template-Beispiele

### Einfache Testimonial-Liste

```twig
{% for testimonial in testimonials %}
    <article class="testimonial">
        <blockquote>
            {{ testimonial.text|raw }}
        </blockquote>
        
        {% if testimonial.contact %}
            <footer>
                <cite>{{ testimonial.contact.fullName }}</cite>
            </footer>
        {% endif %}
    </article>
{% endfor %}
```

### Mit Bewertungsanzeige

```twig
{% for testimonial in testimonials %}
    <article class="testimonial">
        {{ testimonial.text|raw }}
        
        {% if testimonial.rating is defined and testimonial.rating >= 0 %}
            {% set maxRating = testimonials_rating_max_value %}
            {% set starValue = testimonial.rating %}
            
            {# 10-Punkte in 5-Sterne-Anzeige umrechnen #}
            {% if maxRating == 10 %}
                {% set starValue = testimonial.rating / 2 %}
            {% endif %}
            
            <div class="rating" aria-label="Bewertung: {{ testimonial.rating }}/{{ maxRating }}">
                {% for i in 1..5 %}
                    {% if starValue >= i %}
                        <span class="star filled">★</span>
                    {% elseif starValue >= i - 0.5 %}
                        <span class="star half">⯪</span>
                    {% else %}
                        <span class="star empty">☆</span>
                    {% endif %}
                {% endfor %}
                <span class="rating-text">({{ testimonial.rating }}/{{ maxRating }})</span>
            </div>
        {% endif %}
    </article>
{% endfor %}
```

### Vollständiges Template

```twig
{% for testimonial in testimonials %}
    <article class="testimonial card">
        {# Bild #}
        {% if testimonial.image %}
            {% set media = sulu_resolve_media(testimonial.image, app.request.locale) %}
            {% if media %}
                <img src="{{ media.thumbnails['sulu-240x'] }}" 
                     alt="{{ testimonial.title }}"
                     class="testimonial-image">
            {% endif %}
        {% endif %}
        
        <div class="testimonial-content">
            {# Titel #}
            <h3>{{ testimonial.title }}</h3>
            
            {# Zitat-Text #}
            <blockquote>
                {{ testimonial.text|raw }}
            </blockquote>
            
            {# Autor-Info #}
            {% if testimonial.showContact|default and testimonial.contact %}
                <div class="author">
                    <strong>{{ testimonial.contact.fullName }}</strong>
                    
                    {# Organisation #}
                    {% if testimonial.showOrganisation|default %}
                        {% if testimonial.contact.accountContacts|length > 0 %}
                            <span class="organisation">
                                {{ testimonial.contact.accountContacts[0].account.name }}
                            </span>
                        {% endif %}
                    {% endif %}
                </div>
            {% endif %}
            
            {# Datum #}
            {% if testimonial.showDate|default and testimonial.date %}
                <time datetime="{{ testimonial.date|date('Y-m-d') }}">
                    {{ testimonial.date|format_datetime('medium', 'none', locale=app.request.locale) }}
                </time>
            {% endif %}
            
            {# Bewertung #}
            {% if testimonial.rating >= 0 %}
                {% set maxRating = testimonials_rating_max_value %}
                <div class="rating">
                    {{ testimonial.rating }}/{{ maxRating }} Sterne
                </div>
            {% endif %}
            
            {# Quelle #}
            {% if testimonial.source %}
                <div class="source">
                    {% if testimonial.website %}
                        <a href="{{ testimonial.website }}" target="_blank" rel="noopener">
                            {{ testimonial.source }}
                        </a>
                    {% else %}
                        {{ testimonial.source }}
                    {% endif %}
                </div>
            {% endif %}
        </div>
    </article>
{% endfor %}
```

## Verfügbare Properties

| Property | Typ | Beschreibung |
|----------|-----|--------------|
| `title` | string | Testimonial-Titel |
| `text` | string | Haupt-Testimonial-Text (HTML) |
| `rating` | int | Bewertungswert (0 bis max_value) |
| `source` | string | Quelle des Testimonials |
| `website` | string | URL zur Originalquelle |
| `date` | DateTime | Testimonial-Datum |
| `showDate` | bool | Ob das Datum angezeigt werden soll |
| `showContact` | bool | Ob Kontaktinfos angezeigt werden sollen |
| `showOrganisation` | bool | Ob die Organisation angezeigt werden soll |
| `contact` | Contact | Verknüpfter Sulu-Kontakt |
| `image` | Media | Testimonial-Bild |

## Templates überschreiben

Eigenes Template erstellen durch Kopieren des Bundle-Templates:

```bash
# Bundle-Template ins Projekt kopieren
cp vendor/manuxi/sulu-testimonials-bundle/src/Resources/views/testimonial.html.twig \
   templates/testimonials/testimonial.html.twig
```

Dann nach Bedarf anpassen. Das Bundle verwendet automatisch das eigene Template, wenn es in den Standard-Symfony-Template-Verzeichnissen existiert.
