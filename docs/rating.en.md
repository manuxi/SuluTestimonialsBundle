# Rating System

[← Back to README](../README.md)

## Overview

The testimonial rating system supports two scales:
- **5-point scale**: Simple 1-5 star rating
- **10-point scale**: 1-10 rating displayed as 5 stars with half-star increments

## Configuration

```yaml
# config/packages/sulu_testimonials.yaml
sulu_testimonials:
    rating:
        max_value: 10           # 5 or 10
        default_value: 7        # Pre-selected value for new testimonials
        use_star_symbols: true  # Show stars in admin dropdown
```

## Rating Display Logic

### 5-Point Scale

| Rating | Stars | Display |
|--------|-------|---------|
| 0 | ☆☆☆☆☆ | 0/5 |
| 1 | ★☆☆☆☆ | 1/5 |
| 2 | ★★☆☆☆ | 2/5 |
| 3 | ★★★☆☆ | 3/5 |
| 4 | ★★★★☆ | 4/5 |
| 5 | ★★★★★ | 5/5 |

### 10-Point Scale

| Rating | Stars | Display |
|--------|-------|---------|
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

## Twig Implementation

The bundle provides the global variable `testimonials_rating_max_value` which is available in all template contexts.

### Basic Rating Display

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {% set maxRating = testimonials_rating_max_value %}
    <span>{{ testimonial.rating }}/{{ maxRating }}</span>
{% endif %}
```

### Star Display with Half-Stars

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {% set ratingValue = testimonial.rating %}
    {% set maxRating = testimonials_rating_max_value %}
    {% set starValue = ratingValue %}
    
    {# Convert 10-point to 5-star scale #}
    {% if maxRating == 10 %}
        {% set starValue = ratingValue / 2 %}
    {% endif %}
    
    {# Round to nearest 0.5 for half-star support #}
    {% set displayedRating = (starValue * 2)|round / 2 %}
    
    <div class="stars" aria-label="Rating: {{ ratingValue }}/{{ maxRating }}">
        {% for i in 1..5 %}
            {% if displayedRating >= i %}
                {# Full star #}
                <span class="star full">★</span>
            {% elseif displayedRating >= i - 0.5 %}
                {# Half star #}
                <span class="star half">⯪</span>
            {% else %}
                {# Empty star #}
                <span class="star empty">☆</span>
            {% endif %}
        {% endfor %}
        <span class="value">({{ ratingValue }}/{{ maxRating }})</span>
    </div>
{% endif %}
```

### CSS Half-Star Alternative

For better visual appearance, you can use CSS overlays instead of the ⯪ character:

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

## Handling Rating 0

By default, a rating of 0 can represent "no rating given". You can choose how to display this:

### Option 1: Show Empty Stars

```twig
{% if testimonial.rating is defined and testimonial.rating >= 0 %}
    {# Shows 5 empty stars for rating 0 #}
{% endif %}
```

### Option 2: Hide Rating Block

```twig
{% if testimonial.rating is defined and testimonial.rating > 0 %}
    {# Only shows if rating is 1 or higher #}
{% endif %}
```

### Option 3: Show "Not Rated" Text

```twig
{% if testimonial.rating is defined %}
    {% if testimonial.rating > 0 %}
        {# Show stars #}
    {% else %}
        <span class="not-rated">Not rated</span>
    {% endif %}
{% endif %}
```

## Accessibility

Always include proper ARIA labels for screen readers:

```twig
<div class="rating" 
     role="img" 
     aria-label="Rating: {{ testimonial.rating }} out of {{ maxRating }} stars">
    {# Visual star display #}
</div>
```

## Migration Between Scales

When changing from 5-point to 10-point scale (or vice versa), existing ratings are preserved as-is. Consider running a data migration if you need to convert values:

```php
// Example: Convert 5-point to 10-point
// Rating 3/5 becomes 6/10
$newRating = $oldRating * 2;

// Example: Convert 10-point to 5-point  
// Rating 7/10 becomes 3.5, rounded to 4/5
$newRating = (int) round($oldRating / 2);
```
