EventsBundle
============

EventsBundle does the following:

- Displays event requested,
- Provides tools to manage events,
- Provides a carousel of events,
- Provides a link to integrate to calendar (ics),
- Integrates with your web design,

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/), [jQuery](https://jquery.com/), [Bootstrap](http://getbootstrap.com/) and [Bootstrap DatePicker](https://github.com/uxsolutions/bootstrap-datepicker) and requires Twig/Extensions for localizing dates and time.

[Events Bundle dedicated web page](https://975l.com/en/pages/events-bundle).

Bundle installation
===================

Step 1: Download the Bundle
---------------------------
Add the following to your `composer.json > require section`
```
"require": {
    ...
    "c975L/events-bundle": "1.*"
},
```
Then open a command console, enter your project directory and update composer, by executing the following command, to download the latest stable version of this bundle:

```bash
$ composer update
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

Step 2: Enable the Bundles
--------------------------
Then, enable the bundles by adding them to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new c975L\EventsBundle\c975LEventsBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundles
-----------------------------
Then, in the `app/config.yml` file of your project, define `roleNeeded` (the user's role needed to enable access to the edition of events) and `folderPIctures` (where the events's pictures will be saved).

```yml
#app/config/config.yml

#https://github.com/KnpLabs/KnpPaginatorBundle
knp_paginator:
    default_options:
        page_name: p
        distinct: true
    template:
        pagination: 'KnpPaginatorBundle:Pagination:twitter_bootstrap_v3_pagination.html.twig'

c975_l_events:
    #Path where the pictures will be stored. The full path ('web/images/[folderPictures]') has to be added to .gitignore if Git is used
    folderPictures: 'events'
    #User's role needed to enable access to the edition of page
    roleNeeded: 'ROLE_ADMIN'
    #Base url for sitemap creation without leading slash
    sitemapBaseUrl: 'http://example.com'
    #(Optional) Array of available languages of the website
    sitemapLanguages: ['en', 'fr', 'es']
```

**If you use Git for version control, you need to add the full path `web/images/[folderPictures]` in the `.gitignore`, otherwise all the content will be altered by Git.**

Step 4: Enable the Routes
-------------------------
Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
#app/config/routing.yml

...
c975_l_events:
    resource: "@c975LEventsBundle/Controller/"
    type:     annotation
    #Multilingual website use: prefix: /{_locale}
    prefix:   /
```

Step 5: Create MySql table
--------------------------
- Use `/Resources/sql/events.sql` to create the tables `events`. The `DROP TABLE` is commented to avoid dropping by mistake.

Step 6: Link and initialization of TinyMce
------------------------------------------
It is strongly recommend to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LEventsBundle/views/` in your app and then duplicate the files `layout.html.twig` and `tinymceInit.html.twig` in it, to override the existing Bundle files, then aply your needed changes, such as language, etc.

In `tinymceInit.html.twig`, you must add a link to the cloud version (recommended) `https://cloud.tinymce.com/stable/tinymce.min.js` of TinyMce. You will need a free API key (available from the download link) **OR** download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/). You also need to initialize TinyMce for specific tools and options ([language_url pack](https://www.tinymce.com/download/language-packages/), `content_css`, etc.).

Example of initialization (see `tinymceInit.html.twig` file).

```javascript
    {# datePicker - https://github.com/uxsolutions/bootstrap-datepicker #}
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/js/bootstrap-datepicker.min.js"></script>
    {# Include datePicket locale file if neede #}
    {# <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/locales/bootstrap-datepicker.fr.min.js"></script> #}

    {# TinyMceCloud - https://www.tinymce.com #}
    <script type="text/javascript" src="//cloud.tinymce.com/stable/tinymce.min.js{# ?apiKey=YOUR_API_KEY #}"></script>

    {# TinyMce Initialization #}
    {# For options, see: https://www.tinymce.com/docs/get-started-cloud/editor-and-features/ #}
    <script type="text/javascript">
        tinymce.init({
            selector: 'textarea.tinymce',
            statusbar: true,
            menubar: false,
            browser_spellcheck: true,
            contextmenu: false,
            schema: 'html5 strict',
            content_css : [
                'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
            ],
            //language_url : '{# absolute_url(asset('vendor/tinymce/fr_FR.js')) #}',
            //language_url : 'http://example.com/js/tinymce/fr_FR.js',
            plugins: [
                'advlist autolink lists link charmap print preview hr anchor pagebreak',
                'searchreplace wordcount visualblocks visualchars fullscreen',
                'insertdatetime nonbreaking save table contextmenu directionality',
                'emoticons template paste textcolor colorpicker textpattern toc help',
            ],
            toolbar: [
                'styleselect | removeformat bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
                'undo redo | cut copy paste | link emoticons table | print preview | fullscreen help',
            ],
            link_context_toolbar: true,
        });
    </script>
```

Step 7: Link and initialization of Bootstrap DatePicker
-------------------------------------------------------
In the overridding file setup above, you must add a link to the cloud version (recommended) `https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/js/bootstrap-datepicker.min.js` of Bootstrap DatePicker.

You may also want to setup the specific locales from [cdnjs.com](https://cdnjs.com/libraries/bootstrap-datepicker).

Initialization is done inside the templates of the Bundle.

How to use
----------
The Route to display an event is `http://example.com/events/{event}`, the one to edit is `http://example.com/events/edit/{event}`.

A toolbar is displayed below the title if user is identified and has the acess rights.

Link to a page, in Twig, can be done by `<a href="{{ path('events_display', { 'page': 'slug' }) }}">Title of the event</a>`.

The different Routes (naming self-explanatory) available are:
- events_display
- events_new
- events_edit
- events_delete
- events_dashboard
- events_carousel
- events_all
- events_ical
- events_slug
- events_help

Include carousel
----------------
To include the carousel in a page, simply use `{{ render(controller('c975LEventsBundle:Events:carousel', {'number': 3})) }}` where you want it to appear.

Create Sitemap
--------------
In a console use `php bin/console events:createSitemap` to create a `sitemap-events.xml` in the `web` folder of your project. You can use a crontab to generate it every day.
You can add this file in a `sitemap-index.xml`that groups all your sitemaps or directly use it if you have only one.