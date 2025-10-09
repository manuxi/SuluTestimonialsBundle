# SuluTestimonialsBundle!
![php workflow](https://github.com/manuxi/SuluTestimonialsBundle/actions/workflows/php.yml/badge.svg)
![symfony workflow](https://github.com/manuxi/SuluTestimonialsBundle/actions/workflows/symfony.yml/badge.svg)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
<a href="https://github.com/manuxi/SuluTestimonialsBundle/tags" target="_blank">
<img src="https://img.shields.io/github/v/tag/manuxi/SuluTestimonialsBundle" alt="GitHub license">
</a>

I made this bundle to have the possibility to manage testimonials in my projects.

This bundle contains
- Several filters for Testimonials Content Type
- Link Provider
- Sitemap Provider
- Handler for Trash Items
- Handler for Automation
- Possibility to assign a contact as author
- Twig Extension for resolving Testimonials / get a list of Testimonials
- Events for displaying Activities
- Search indexes
  - refresh whenever entity is changed
  - distinct between normal and draft
  and more...

The testimonials are translatable.

Please feel comfortable submitting feature requests. 
This bundle is still in development. Use at own risk 🤞🏻

![image](https://github.com/user-attachments/assets/273d4912-8b50-4bd2-8c9b-bb53f338cb37)

## 👩🏻‍🏭 Installation
Install the package with:
```console
composer require manuxi/sulu-testimonials-bundle
```
If you're *not* using Symfony Flex, you'll also
need to add the bundle in your `config/bundles.php` file:

```php
return [
    //...
    Manuxi\SuluTestimonialsBundle\SuluTestimonialsBundle::class => ['all' => true],
];
```
Please add the following to your `routes_admin.yaml`:
```yaml
SuluTestimonialsBundle:
    resource: '@SuluTestimonialsBundle/Resources/config/routes_admin.yml'
```
Don't forget fo add the index to your sulu_search.yaml:

add "testimonials"!

"testimonials" is the index of published, "testimonials_draft" the index of unpublished elements.
```yaml
sulu_search:
    website:
        indexes:
            - testimonials
            - ...
``` 

Last but not least the schema of the database needs to be updated.  

Some tables will be created (prefixed with app_):  
testimonials, testimonials_translation.  

See the needed queries with
```
php bin/console doctrine:schema:update --dump-sql
```  
Update the schema by executing 
```
php bin/console doctrine:schema:update --force
```  

Make sure you only process the bundles schema updates!

## 🎣 Usage
First: Grant permissions for testimonials. 
After reload you should see the testimonials item in the navigation. 
Start to create testimonials.
Use smart_content property type to show a list of testimonials, e.g.:
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
Example of the corresponding twig template for the testimonials list:
```html
{% for testimonial in testimonials %}
    <div class="col">
        <h2>
            {{ testimonial.contact.fullname }}
        </h2>
        <p>
            {{ testimonial.text|raw }}
        </p>
    </div>
{% endfor %}
```

## 👩‍🍳 Contributing
For the sake of simplicity this extension was kept small.
Please feel comfortable submitting issues or pull requests. As always I'd be glad to get your feedback to improve the extension :).
