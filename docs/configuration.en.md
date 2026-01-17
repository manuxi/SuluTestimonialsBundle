# Configuration

[← Back to README](../README.md)

## Bundle Configuration

Create `config/packages/sulu_testimonials.yaml` in your project:

```yaml
sulu_testimonials:
    rating:
        max_value: 5            # Rating scale (5 or 10)
        default_value: 3        # Default value for new testimonials
        use_star_symbols: true  # Display stars in admin dropdown
        use_star_widget: false  # Interactive star widget (reserved for future use)
```

## Configuration Options

### Rating Scale (`max_value`)

| Value | Description | Admin Display | Frontend Display |
|-------|-------------|---------------|------------------|
| `5` | 5-point scale | 0-5 | ★★★★★ |
| `10` | 10-point scale | 0-10 | ★★★★★ (with half stars) |

The 10-point scale is displayed as 5 stars with half-star increments (0.5 = ⯪).

### Default Value (`default_value`)

The pre-selected rating when creating a new testimonial. Must be within the configured `max_value`.

### Star Symbols (`use_star_symbols`)

When `true`, the admin dropdown shows star symbols:

**5-point scale:**
```
☆☆☆☆☆ (0/5)
★☆☆☆☆ (1/5)
★★☆☆☆ (2/5)
★★★☆☆ (3/5)
★★★★☆ (4/5)
★★★★★ (5/5)
```

**10-point scale:**
```
☆☆☆☆☆ (0/10)
⯪☆☆☆☆ (1/10)
★☆☆☆☆ (2/10)
★⯪☆☆☆ (3/10)
...
★★★★★ (10/10)
```

When `false`, simple text is displayed: "0 stars", "1 star", "2 stars", etc.

## Twig Global Variable

The bundle provides a Twig global variable for accessing the configured rating scale:

```twig
{{ testimonials_rating_max_value }}  {# Returns 5 or 10 #}
```

This variable is available in all contexts:
- Smart Content templates
- Selection templates  
- Direct controller templates
- Custom Twig templates

## Example: Different Scales

### 5-Star Scale (default)

```yaml
sulu_testimonials:
    rating:
        max_value: 5
        default_value: 3
        use_star_symbols: true
```

![5-Star Scale](img/rating-5-stars.png)

### 10-Point Scale

```yaml
sulu_testimonials:
    rating:
        max_value: 10
        default_value: 7
        use_star_symbols: true
```

![10-Point Scale](img/rating-10-stars.png)

## Overriding Translations

You can override the rating labels in your project:

```yaml
# translations/admin.en.yaml
sulu_testimonials:
    rates:
        default:
            0: "No rating"
            1: "Poor"
            2: "Fair"
            3: "Good"
            4: "Very Good"
            5: "Excellent"
```

For the 10-point scale with stars:

```yaml
# translations/admin.en.yaml
sulu_testimonials:
    rates:
        ten:
            0: "☆☆☆☆☆ (0/10)"
            1: "⯪☆☆☆☆ (1/10)"
            # ... etc
```
