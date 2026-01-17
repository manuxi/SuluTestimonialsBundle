# Installation

[← Zurück zur README](../README.de.md)

## Voraussetzungen

- PHP 8.2 oder höher
- Sulu CMS 3.0 oder höher
- Symfony 6.4 / 7.0 oder höher
- MySQL 5.7+ / MariaDB 10.2+ / PostgreSQL 11+

## Schritt-für-Schritt-Installation

### 1. Installation via Composer

```bash
composer require manuxi/sulu-testimonials-bundle
```

### 2. Bundle registrieren

Falls Symfony Flex **nicht** verwendet wird, das Bundle in `config/bundles.php` hinzufügen:

```php
<?php

return [
    // ... andere Bundles
    Manuxi\SuluTestimonialsBundle\SuluTestimonialsBundle::class => ['all' => true],
];
```

### 3. Admin-Routen konfigurieren

In `config/routes/sulu_admin.yaml` hinzufügen:

```yaml
SuluTestimonialsBundle:
    resource: '@SuluTestimonialsBundle/Resources/config/routes_admin.yaml'
```

### 4. Website-Routen konfigurieren (Optional)

Falls Testimonial-Detailseiten mit eigenen URLs verwendet werden sollen, in `config/routes/sulu_website.yaml` hinzufügen:

```yaml
SuluTestimonialsBundle:
    resource: '@SuluTestimonialsBundle/Resources/config/routes_website.yaml'
```

### 5. Datenbankschema aktualisieren

Änderungen anzeigen:

```bash
php bin/console doctrine:schema:update --dump-sql
```

Änderungen anwenden:

```bash
php bin/console doctrine:schema:update --force
```

**Erstellte Tabellen:**
- `te_testimonials` - Haupt-Testimonial-Entität
- `te_testimonials_dimension_content` - Dimension Content (Übersetzungen, Stages)
- `te_testimonials_settings` - Bundle-Einstellungen

### 6. Admin-Assets bauen

```bash
cd assets/admin
npm install
npm run build
```

### 7. Cache leeren

```bash
php bin/console cache:clear
```

### 8. Berechtigungen vergeben

1. In Sulu Admin einloggen
2. Zu **Einstellungen → Benutzerrollen** navigieren
3. Die Rolle auswählen, die Testimonials verwalten soll
4. **Testimonials** in der Berechtigungsliste finden
5. Gewünschte Berechtigungen aktivieren:
   - **Anzeigen**: Kann Testimonials sehen
   - **Hinzufügen**: Kann neue Testimonials erstellen
   - **Bearbeiten**: Kann bestehende Testimonials ändern
   - **Löschen**: Kann Testimonials entfernen
   - **Live**: Kann Testimonials veröffentlichen
6. Speichern und Seite neu laden

![Berechtigungen](img/permissions1.png)
![Berechtigungen gesetzt](img/permissions2.png)

## Installation überprüfen

Nach der Installation sollte sichtbar sein:

1. **Testimonials**-Eintrag in der Hauptnavigation
2. Möglichkeit, neue Testimonials zu erstellen
3. Smart Content Provider "testimonials" in Seitenvorlagen verfügbar

## Fehlerbehebung

### Bundle erscheint nicht in der Navigation

- Cache leeren: `php bin/console cache:clear`
- Admin-Assets neu bauen: `cd assets/admin && npm run build`
- Prüfen, ob Berechtigungen für die Benutzerrolle vergeben sind

### Datenbankfehler

- Sicherstellen, dass das Schema-Update ausgeführt wurde
- Prüfen, ob alle Migrationen angewendet sind

### Admin JS-Fehler

- `npm install` in `assets/admin` ausführen
- Mit `npm run build` neu bauen
- Browser-Cache leeren

### Suche funktioniert nicht

- Prüfen, ob der Suchindex in `sulu_search.yaml` konfiguriert ist
- Suchindex neu aufbauen: `php bin/console sulu:search:reindex`

## Upgrade

Beim Upgrade auf eine neue Version:

```bash
composer update manuxi/sulu-testimonials-bundle
php bin/console doctrine:schema:update --force
cd assets/admin && npm install && npm run build
php bin/console cache:clear
```

## Deinstallation

1. Aus `config/bundles.php` entfernen
2. Routen-Konfigurationen entfernen
3. `config/packages/sulu_testimonials.yaml` entfernen
4. Datenbanktabellen löschen (vorher Backup!)
5. `composer remove manuxi/sulu-testimonials-bundle` ausführen
