# SuluTestimonialsBundle

![php workflow](https://github.com/manuxi/SuluTestimonialsBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluTestimonialsBundle/actions/workflows/symfony.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://github.com/manuxi/SuluTestimonialsBundle/blob/main/LICENSE)
![GitHub Tag](https://img.shields.io/github/v/tag/manuxi/SuluTestimonialsBundle)
![Supports Sulu 3.0 or later](https://img.shields.io/badge/Sulu->=3.0-0088cc?color=00b2df)

[English](README.md) | **Deutsch**

Ein Sulu CMS Bundle zur Verwaltung von Kundenbewertungen, Reviews und Zitaten mit konfigurierbaren Sternebewertungen.

![Admin Ansicht](docs/img/template.extended.en.png)

## ✨ Features

- **Testimonial-Verwaltung** - Erstellen, bearbeiten und veröffentlichen von Kundenbewertungen
- **Sternebewertungssystem** - Konfigurierbare 5- oder 10-Punkte-Skala mit Sternsymbolen
- **Kontakt-Integration** - Verknüpfung von Testimonials mit Sulu-Kontakten
- **Smart Content Provider** - Testimonials in jeder Sulu-Seite über Smart Content nutzen
- **Selection Content Types** - Einzel- und Mehrfachauswahl von Testimonials
- **Workflow-Unterstützung** - Entwurf/Veröffentlicht-Workflow mit Versionierung
- **Mehrsprachigkeit** - Vollständige Übersetzungsunterstützung
- **SEO & Sitemap** - Integrierte SEO- und Sitemap-Unterstützung
- **Suchintegration** - Admin- und Website-Suchindizes
- **Papierkorb** - Gelöschte Testimonials wiederherstellen
- **Aktivitätsprotokoll** - Alle Änderungen nachverfolgen

## 📋 Voraussetzungen

- PHP 8.2+
- Sulu CMS 3.0+
- Symfony 6.4+ / 7.0+

## 👩🏻‍🏭 Installation

### Schritt 1: Installation via Composer

```bash
composer require manuxi/sulu-testimonials-bundle
```

### Schritt 2: Bundle registrieren

Falls Symfony Flex nicht verwendet wird, in `config/bundles.php` hinzufügen:

```php
return [
    // ...
    Manuxi\SuluTestimonialsBundle\SuluTestimonialsBundle::class => ['all' => true],
];
```

### Schritt 3: Routen konfigurieren

In `config/routes/sulu_admin.yaml` hinzufügen:

```yaml
SuluTestimonialsBundle:
    resource: '@SuluTestimonialsBundle/Resources/config/routes_admin.yaml'
```

### Schritt 4: Datenbank aktualisieren

```bash
# Änderungen anzeigen
php bin/console doctrine:schema:update --dump-sql

# Änderungen anwenden
php bin/console doctrine:schema:update --force
```

### Schritt 5: Admin-Assets bauen

```bash
cd assets/admin
npm install
npm run build
```

### Schritt 6: Berechtigungen vergeben

1. Zu **Einstellungen → Benutzerrollen** im Sulu Admin gehen
2. Die entsprechende Rolle auswählen
3. Berechtigungen für **Testimonials** aktivieren
4. Speichern und neu laden

## 🧶 Konfiguration

`config/packages/sulu_testimonials.yaml` erstellen:

```yaml
sulu_testimonials:
    rating:
        max_value: 5            # 5 oder 10 Punkte-Skala
        default_value: 3        # Standard-Bewertung für neue Testimonials
        use_star_symbols: true  # Sterne im Admin-Dropdown anzeigen
        use_star_widget: false  # Interaktives Sterne-Widget (zukünftig)
```

Siehe [Konfigurationsdokumentation](docs/configuration.de.md) für alle Optionen.

## 🎣 Verwendung

### Smart Content

Testimonials in beliebigen Seitenvorlagen verwenden:

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

### Selection Types

Einzelnes Testimonial:

```xml
<property name="featured_testimonial" type="single_testimonial_selection">
    <meta>
        <title lang="de">Ausgewähltes Testimonial</title>
    </meta>
</property>
```

Mehrere Testimonials:

```xml
<property name="testimonials" type="testimonial_selection">
    <meta>
        <title lang="de">Testimonials</title>
    </meta>
</property>
```

### Twig-Template

```twig
{% for testimonial in testimonials %}
    <div class="testimonial">
        <blockquote>{{ testimonial.text|raw }}</blockquote>
        
        {% if testimonial.contact %}
            <cite>{{ testimonial.contact.fullName }}</cite>
        {% endif %}
        
        {# Bewertungsanzeige - nutzt globale Variable #}
        {% if testimonial.rating >= 0 %}
            {% set maxRating = testimonials_rating_max_value %}
            <div class="rating">
                {{ testimonial.rating }}/{{ maxRating }}
            </div>
        {% endif %}
    </div>
{% endfor %}
```

## 📖 Dokumentation

- [Installation](docs/installation.de.md)
- [Konfiguration](docs/configuration.de.md)
- [Verwendung & Templates](docs/usage.de.md)
- [Bewertungssystem](docs/rating.de.md)
- [Admin-Listen-Anpassung](docs/admin-list.de.md)

## 🔌 Optionale Integrationen

### Sternebewertung in Admin-Listen

Falls [SuluTweaksBundle](https://github.com/manuxi/SuluTweaksBundle) installiert ist, kann die Sternebewertungsanzeige in Admin-Listen aktiviert werden. Siehe [Admin-Listen-Dokumentation](docs/admin-list.de.md).

## 👩‍🍳 Mitwirken

Beiträge sind willkommen! Issues und Pull Requests können gerne eingereicht werden.

## 📄 Lizenz

Dieses Bundle steht unter der [MIT-Lizenz](LICENSE).