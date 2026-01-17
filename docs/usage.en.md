# Usage & Templates

[← Back to README](../README.md)

## Content Types

### Smart Content

The most flexible way to display testimonials. Supports filtering, sorting, and pagination.

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

**Available sorting options:**
- `title` - Alphabetical by title
- `rating` - By rating value
- `authored` - By authored date
- `created` - By creation date
- `changed` - By last modification

### Single Testimonial Selection

Select one specific testimonial:

```xml
<property name="featured_testimonial" type="single_testimonial_selection">
    <meta>
        <title lang="en">Featured Testimonial</title>
    </meta>
</property>
```

### Multiple Testimonial Selection

Select multiple specific testimonials:

```xml
<property name="testimonials" type="testimonial_selection">
    <meta>
        <title lang="en">Selected Testimonials</title>
    </meta>
</property>
```

## Twig Functions

### `sulu_resolve_testimonial`

Resolve a single testimonial by ID:

```twig
{% set testimonial = sulu_resolve_testimonial(42, app.request.locale) %}
{% if testimonial %}
    <h3>{{ testimonial.title }}</h3>
{% endif %}
```

### `sulu_get_testimonials`

Get a list of testimonials:

```twig
{% set testimonials = sulu_get_testimonials(10, app.request.locale) %}
{% for testimonial in testimonials %}
    <div>{{ testimonial.title }}</div>
{% endfor %}
```

## Template Examples

### Basic Testimonial List

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

### With Rating Display

```twig
{% for testimonial in testimonials %}
    <article class="testimonial">
        {{ testimonial.text|raw }}
        
        {% if testimonial.rating is defined and testimonial.rating >= 0 %}
            {% set maxRating = testimonials_rating_max_value %}
            {% set starValue = testimonial.rating %}
            
            {# Convert 10-point to 5-star display #}
            {% if maxRating == 10 %}
                {% set starValue = testimonial.rating / 2 %}
            {% endif %}
            
            <div class="rating" aria-label="Rating: {{ testimonial.rating }}/{{ maxRating }}">
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

### Full Featured Template

```twig
{% for testimonial in testimonials %}
    <article class="testimonial card">
        {# Image #}
        {% if testimonial.image %}
            {% set media = sulu_resolve_media(testimonial.image, app.request.locale) %}
            {% if media %}
                <img src="{{ media.thumbnails['sulu-240x'] }}" 
                     alt="{{ testimonial.title }}"
                     class="testimonial-image">
            {% endif %}
        {% endif %}
        
        <div class="testimonial-content">
            {# Title #}
            <h3>{{ testimonial.title }}</h3>
            
            {# Quote Text #}
            <blockquote>
                {{ testimonial.text|raw }}
            </blockquote>
            
            {# Author Info #}
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
            
            {# Date #}
            {% if testimonial.showDate|default and testimonial.date %}
                <time datetime="{{ testimonial.date|date('Y-m-d') }}">
                    {{ testimonial.date|format_datetime('medium', 'none', locale=app.request.locale) }}
                </time>
            {% endif %}
            
            {# Rating #}
            {% if testimonial.rating >= 0 %}
                {% set maxRating = testimonials_rating_max_value %}
                <div class="rating">
                    {{ testimonial.rating }}/{{ maxRating }} Stars
                </div>
            {% endif %}
            
            {# Source #}
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

## Available Properties

| Property | Type | Description |
|----------|------|-------------|
| `title` | string | Testimonial title |
| `text` | string | Main testimonial text (HTML) |
| `rating` | int | Rating value (0 to max_value) |
| `source` | string | Source of the testimonial |
| `website` | string | URL to original source |
| `date` | DateTime | Testimonial date |
| `showDate` | bool | Whether to display the date |
| `showContact` | bool | Whether to display contact info |
| `showOrganisation` | bool | Whether to display organisation |
| `contact` | Contact | Linked Sulu contact |
| `image` | Media | Testimonial image |

## Overriding Templates

Create your own template by copying the bundle template:

```bash
# Copy bundle template to your project
cp vendor/manuxi/sulu-testimonials-bundle/src/Resources/views/testimonial.html.twig \
   templates/testimonials/testimonial.html.twig
```

Then customize as needed. The bundle will automatically use your template if it exists in the standard Symfony template locations.
