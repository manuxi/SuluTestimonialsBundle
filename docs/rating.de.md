# Bewertungssystem

[← Zurück zur README](../README.de.md)

## Übersicht

Das Testimonial-Bewertungssystem unterstützt zwei Skalen:
- **5-Punkte-Skala**: Einfache 1-5 Sterne-Bewertung
- **10-Punkte-Skala**: 1-10 Bewertung als 5 Sterne mit Halbstern-Abstufungen

## Konfiguration

```yaml
# config/packages/sulu_testimonials.yaml
sulu_testimonials:
    rating:
        max_value: 10           # 5 oder 10
        default_value: 7        # Vorausgewählter Wert für neue Testimonials
        use_star_symbols: true  # Sterne im Admin-Dropdown anzeigen
```

## Bewertungsanzeige-Logik

### 5-Punkte-Skala
![Bewertung](img/rating-5.png)

| Bewertung | Sterne | Anzeige |
|-----------|--------|---------|
| 0 | ☆☆☆☆☆ | 0/5 |
| 1 | ★☆☆☆☆ | 1/5 |
| 2 | ★★☆☆☆ | 2/5 |
| 3 | ★★★☆☆ | 3/5 |
| 4 | ★★★★☆ | 4/5 |
| 5 | ★★★★★ | 5/5 |

### 10-Punkte-Skala
![img.png](img/rating-10.png)

| Bewertung | Sterne | Anzeige |
|-----------|--------|---------|
| 0 | ☆☆☆☆☆ | 0/10 |
| 1 | ⯪☆☆☆☆ | 1/10 |
| 2 | ★☆☆☆☆ | 2/10 |
| 3 | ★⯪☆☆☆ | 3/10 |
| 4 | ★★☆☆☆ | 4/10 |
| 5 | ★★⯪☆☆ | 5/10 |
| 6 | ★★★☆☆ | 6/10 |
| 7 | ★★★⯪☆ | 7/10 |
| 8 | ★★★★☆ | 8/10 |
| 9 | ★★★★⯪ | 9/10 |
| 10 | ★★★★★ | 10/10 |

## Twig-Implementierung

Das Bundle stellt die globale Variable `testimonials_rating_max_value` bereit, die in allen Template-Kontexten verfügbar ist.

### Einfache Bewertungsanzeige

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {% set maxRating = testimonials_rating_max_value %}
    <span>{{ testimonial.rating }}/{{ maxRating }}</span>
{% endif %}
```

### Sternanzeige mit Halbsternen

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {% set ratingValue = testimonial.rating %}
    {% set maxRating = testimonials_rating_max_value %}
    {% set starValue = ratingValue %}
    
    {# 10-Punkte in 5-Sterne-Skala umrechnen #}
    {% if maxRating == 10 %}
        {% set starValue = ratingValue / 2 %}
    {% endif %}
    
    {# Auf nächste 0.5 runden für Halbstern-Unterstützung #}
    {% set displayedRating = (starValue * 2)|round / 2 %}
    
    <div class="stars" aria-label="Bewertung: {{ ratingValue }}/{{ maxRating }}">
        {% for i in 1..5 %}
            {% if displayedRating >= i %}
                {# Voller Stern #}
                <span class="star full">★</span>
            {% elseif displayedRating >= i - 0.5 %}
                {# Halber Stern #}
                <span class="star half">⯪</span>
            {% else %}
                {# Leerer Stern #}
                <span class="star empty">☆</span>
            {% endif %}
        {% endfor %}
        <span class="value">({{ ratingValue }}/{{ maxRating }})</span>
    </div>
{% endif %}
```

### CSS Halbstern-Alternative

Für bessere Optik kann CSS-Overlay statt dem ⯪-Zeichen verwendet werden:

```twig
{% elseif displayedRating >= i - 0.5 %}
    <div class="star-half">
        <span class="empty">★</span>
        <span class="filled" style="width: 50%; overflow: hidden;">★</span>
    </div>
{% endif %}
```

```css
.star-half {
    position: relative;
    display: inline-block;
}
.star-half .empty {
    color: #ccc;
}
.star-half .filled {
    position: absolute;
    top: 0;
    left: 0;
    color: #ffc107;
    overflow: hidden;
    width: 50%;
}
```

## Bewertung 0 behandeln

Standardmäßig kann eine Bewertung von 0 "keine Bewertung abgegeben" bedeuten. Anzeige-Optionen:

### Option 1: Leere Sterne anzeigen

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {# Zeigt 5 leere Sterne bei Bewertung 0 #}
{% endif %}
```

### Option 2: Bewertungsblock ausblenden

```twig
{% if testimonial.rating is defined and testimonial.rating > 0 %}
    {# Zeigt nur bei Bewertung 1 oder höher #}
{% endif %}
```

### Option 3: "Nicht bewertet"-Text anzeigen

```twig
{% if testimonial.rating is defined %}
    {% if testimonial.rating > 0 %}
        {# Sterne anzeigen #}
    {% else %}
        <span class="not-rated">Nicht bewertet</span>
    {% endif %}
{% endif %}
```

## Barrierefreiheit

Immer ARIA-Labels für Screenreader einbinden:

```twig
<div class="rating" 
     role="img" 
     aria-label="Bewertung: {{ testimonial.rating }} von {{ maxRating }} Sternen">
    {# Visuelle Sternanzeige #}
</div>
```

## Migration zwischen Skalen

Beim Wechsel von 5-Punkte- zu 10-Punkte-Skala (oder umgekehrt) bleiben bestehende Bewertungen unverändert. Bei Bedarf Datenmigration ausführen:

```php
// Beispiel: 5-Punkte zu 10-Punkte konvertieren
// Bewertung 3/5 wird 6/10
$newRating = $oldRating * 2;

// Beispiel: 10-Punkte zu 5-Punkte konvertieren
// Bewertung 7/10 wird 3.5, gerundet auf 4/5
$newRating = (int) round($oldRating / 2);
```
