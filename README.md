EventsBundle
============

EventsBundle does the following:

- Displays event requested,
- Provides tools to manage events,
- Provides a carousel of events,
- Provides a link to integrate to calendar (ics),
- Integrates with your web design,

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/), [jQuery](https://jquery.com/), [Bootstrap](http://getbootstrap.com/) and [Bootstrap DatePicker](https://github.com/uxsolutions/bootstrap-datepicker) and requires Twig/Extensions for localizing dates and time.

[EventsBundle dedicated web page](https://975l.com/en/pages/events-bundle).

[EventsBundle API documentation](https://975l.com/apidoc/c975L/EventsBundle.html).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Use [Composer](https://getcomposer.org) to install the library
```bash
    composer require c975l/evebts-bundle
```

Step 2: Enable the Bundles
--------------------------
Then, enable the bundles by adding them to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new c975L\EventsBundle\c975LEventsBundle(),
        ];
    }
}
```

Step 3: Configure the Bundles
-----------------------------
Check [KnpPaginatorBundle](https://github.com/KnpLabs/KnpPaginatorBundle) for its specific configuration.

Setup your Tinymce API key if you use the cloud version, in `parameters.yml`
```yml
    #(Optional) Your Tinymce Api key if you use the cloud version
    #tinymceApiKey: 'YOUR_API_KEY'
```

And then in `parameters.yml.dist`
```yml
    #(Optional) Your Tinymce Api key if you use the cloud version
    #tinymceApiKey:     ~
```

Then, in the `app/config.yml` file of your project, define the following:

```yml
c975_l_events:
    #Path where the pictures will be stored. The full path ('web/images/[folderPictures]') has to be added to .gitignore if Git is used
    folderPictures: 'events'
    #User's role needed to enable access to the edition of page
    roleNeeded: 'ROLE_ADMIN' #default 'ROLE_ADMIN'
    #Base url for sitemap creation without leading slash
    sitemapBaseUrl: 'http://example.com'
    #(Optional) Array of available languages of the website
    sitemapLanguages: ['en', 'fr', 'es']
    #(Optional) Your tinymce language if you use one, MUST BE placed in 'web/vendor/tinymce/[tinymceLanguage].js'
    tinymceLanguage: 'fr_FR' #default null
```

**If you use Git for version control, you need to add the full path `web/images/[folderPictures]` in the `.gitignore`, otherwise all the content will be altered by Git.**

Step 4: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
c975_l_events:
    resource: "@c975LEventsBundle/Controller/"
    type: annotation
    prefix: /
    #Multilingual website use the following
    #prefix: /{_locale}
    #defaults:   { _locale: '%locale%' }
    #requirements:
    #    _locale: en|fr|es
```

Step 5: Create MySql table
--------------------------
- Use `/Resources/sql/events.sql` to create the table `events`. The `DROP TABLE` is commented to avoid dropping by mistake.

Step 6: Link and initialization of TinyMce
------------------------------------------
It is strongly recommended to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LEventsBundle/views/` in your app and then duplicate the files `layout.html.twig` and `tinymceInit.html.twig` in it, to override the existing Bundle files, then apply your needed changes, such as language, etc.

In `layout.html.twig`, it will mainly consist to extend your layout and define specific variables, i.e. :
```twig
{% extends 'layout.html.twig' %}

{# Defines specific variables #}
{% set title = 'Events (' ~ title ~ ')' %}

{% block content %}
    {% block events_content %}
    {% endblock %}
{% endblock %}
```

It is recommended to use [Tinymce Cloud version](https://go.tinymce.com/cloud/). You will need a [free API key](https://store.ephox.com/my-account/api-key-manager/).
**OR** you can download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

If you want to keep all the available tools and make no change to Tinymce as it is, you don't need to overwrite `tinymceInit.html.twig`. You just need to provide, in `config.yml` your `tinymceApiKey`, if you use the cloud version and the `tinymceLanguage` (+ upload the corresponding file on your server under `web/vendor/tinymce/[tinymceLanguage].js`). Or you can overwrite `tinymceInit.html.twig`.

Step 7: Link and initialization of Bootstrap DatePicker
-------------------------------------------------------
In the overridding file setup above, you must add a link to the cloud version (recommended) `https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/js/bootstrap-datepicker.min.js` of Bootstrap DatePicker.

You may also want to setup the specific locales from [cdnjs.com](https://cdnjs.com/libraries/bootstrap-datepicker).

Initialization is done inside the templates of the Bundle.

How to use
----------
The Route to display an event is `http://example.com/events/{event}`, the one to edit is `http://example.com/events/edit/{event}`.

A toolbar is displayed below the title if user is identified and has the acess rights.

Link to a page, in Twig, can be done by `<a href="{{ path('events_display', { 'slug': 'slug' }) }}">Title of the event</a>`.

The different Routes (naming self-explanatory) available are:
- events_display
- events_create
- events_modify
- events_duplicate
- events_delete
- events_dashboard
- events_all
- events_ical
- events_slug
- events_help

Include carousel
----------------
To include the carousel in a page, simply use `{{ events_carousel(number) }}` where you want it to appear and where `number` is the number of events to display.

Create Sitemap
--------------
In a console use `php bin/console events:createSitemap` to create a `sitemap-events.xml` in the `web` folder of your project. You can use a crontab to generate it every day.
You can add this file in a `sitemap-index.xml`that groups all your sitemaps or directly use it if you have only one.

**If this project help you to reduce time to develop, you can [buy me a coffee](https://www.buymeacoffee.com/LaurentMarquet) :)**