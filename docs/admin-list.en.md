# Admin List Customization

[← Back to README](../README.md)

## Rating Display in Admin Lists

By default, the rating is displayed as a plain number in admin lists. You can enhance this with star ratings using the optional [SuluTweaksBundle](https://github.com/manuxi/SuluTweaksBundle).

## Using SuluTweaksBundle (Optional)

### Installation

```bash
composer require manuxi/sulu-tweaks-bundle
```

### Enable Star Rating Transformer

Edit `config/packages/sulu_tweaks.yaml`:

```yaml
sulu_tweaks:
    star_rating:
        show_value: true      # Show numeric value next to stars
        max_value: 10         # Must match your sulu_testimonials.rating.max_value
```

### Activate in List Configuration

The bundle includes commented transformer hints in the list XML files. To activate:

**Option 1: Override in Project**

Copy the list XML to your project and uncomment the transformer:

```bash
mkdir -p config/lists
cp vendor/manuxi/sulu-testimonials-bundle/src/Resources/config/lists/testimonials.xml \
   config/lists/testimonials.xml
```

Then edit `config/lists/testimonials.xml`:

```xml
<property name="rating" translation="sulu_testimonials.rating" visibility="yes" sortable="true">
    <field-name>rating</field-name>
    <entity-name>dimensionContent</entity-name>
    <joins ref="dimensionContent"/>
    <transformer type="star_rating">
        <params>
            <param name="max_value" value="10"/>
            <param name="show_value" value="true"/>
        </params>
    </transformer>
</property>
```

**Option 2: Using Global TweaksBundle Config**

If `max_value` matches your global TweaksBundle config, you can omit the params:

```xml
<transformer type="star_rating"/>
```

## Result

With the star rating transformer enabled:

![Star Rating in List](img/admin-list-stars.png)

| Without Transformer | With Transformer |
|---------------------|------------------|
| `7` | ★★★⯪☆ (7/10) |
| `5` | ★★⯪☆☆ (5/10) |
| `10` | ★★★★★ (10/10) |

## Other List Customizations

### Publish State Indicator

The bundle also supports the `publish_state_indicator` transformer from TweaksBundle:

```xml
<property name="publishedState" translation="sulu_admin.publish_state_indicator" visibility="always">
    <field-name>workflowPlace</field-name>
    <entity-name>dimensionContent</entity-name>
    <joins ref="dimensionContent"/>
    <transformer type="publish_state_indicator"/>
</property>
```

### Ghost Locale Indicator

For multi-language setups:

```xml
<property name="ghostLocale" translation="sulu_tweaks.ghost_locale" visibility="always">
    <field-name>ghostLocale</field-name>
    <transformer type="ghost_locale_indicator"/>
</property>
```

## Without SuluTweaksBundle

If you don't want to install SuluTweaksBundle, you can create your own list field transformer. See [Sulu Documentation](https://docs.sulu.io/en/2.x/book/extend-admin.html#list-field-transformers) for details.

## Bundle List Files

The bundle includes these list configurations:

| File | Purpose |
|------|---------|
| `testimonials.xml` | Main admin list (all states) |
| `testimonials_published.xml` | Published testimonials only (for overlays/selections) |

Both files include commented hints for the star rating transformer.
