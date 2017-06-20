EventsBundle
============

EventsBundle does the following:

- Displays event requested,
- Provides tools to manage events,
- Provides a carousel of events,
- Provides a link to integrate to calendar (ics),
- Integrates with your web design,

This Bundle relies on the use of [TinyMce](https://www.tinymce.com/), [jQuery](https://jquery.com/), [Bootstrap](http://getbootstrap.com/) and [Bootstrap DatePicker](https://github.com/uxsolutions/bootstrap-datepicker) and requires Twig/Extensions for localizing dates and time.

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

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

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
            new c975L\EventsBundle\c975LEventsBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure the Bundle
----------------------------

Then, in the `app/config.yml` file of your project, define `roleNeeded` (the user's role needed to enable access to the edition of events) and `folderPIctures` (where the events's pictures will be saved).

```yml
#app/config/config.yml

c975_l_events:
    roleNeeded: 'ROLE_ADMIN'
    folderPictures: 'events' #The full path to this folder has to be added to .gitignore if Git is used
```

** As the pictures will be saved under the `web/images/[folderPictures]`, if you use Git for version control, you need to add the full path to this folder in the `.gitignore`, otherwise all the content will be altered by Git. **

Step 4: Enable the Routes
-------------------------

Then, enable the routes by adding them to the `app/config/routing.yml` file of your project:

```yml
#app/config/routing.yml

...
c975_l_events:
    resource: "@c975LEventsBundle/Controller/"
    type:     annotation
    prefix:   /
```

Step 5: Create MySql table
--------------------------

- Use `/Resources/sql/events.sql` to create the tables `events`. The `DROP TABLE` is commented to avoid dropping by mistake.

Step 6: Link and initialization of TinyMce
------------------------------------------

It is strongly recommend to use the [Override Templates from Third-Party Bundles feature](http://symfony.com/doc/current/templating/overriding.html) to integrate fully with your site.

For this, simply, create the following structure `app/Resources/c975LEventsBundle/views/` in your app and then duplicate the file `layout.html.twig` in it, to override the existing Bundle file.

In the overridding file, you must add a link to the cloud version (recommended) `https://cloud.tinymce.com/stable/tinymce.min.js` of TinyMce. You will need a free API key (available from the download link) **OR** download and link to your project [https://www.tinymce.com/download/](https://www.tinymce.com/download/).

You also need to initialize TinyMce ([language pack](https://www.tinymce.com/download/language-packages/) via `language_url`, css used by site via `content_css`, tools, etc.).

Information about options is available at [https://www.tinymce.com/docs/get-started-cloud/editor-and-features/](https://www.tinymce.com/docs/get-started-cloud/editor-and-features/).

Example of initialization (see `layout.html.twig` file).

```javascript
    tinymce.init({
        selector: 'textarea.tinymce',
        statusbar: true,
        menubar: false,
        browser_spellcheck: true,
        contextmenu: false,
        schema: 'html5 strict',
        image_advtab: true,
        content_css : [
            'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css',
        ],
        //language_url : 'http://example.com/js/tinymce/fr_FR.js',
        plugins: [
            'advlist autolink lists link image charmap print preview hr anchor pagebreak',
            'searchreplace wordcount visualblocks visualchars code fullscreen',
            'insertdatetime media nonbreaking save table contextmenu directionality',
            'emoticons template paste textcolor colorpicker textpattern imagetools codesample toc help',
        ],
        toolbar: [
            'styleselect | removeformat bold italic strikethrough forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent',
            'undo redo | cut copy paste | insert link image imagetools emoticons table | print preview code | fullscreen help',
        ],
    });
```

Step 7: Link and initialization of Bootstrap DatePicker
-------------------------------------------------------

In the overridding file setup above, you must add a link to the cloud version (recommended) `https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.7.0/js/bootstrap-datepicker.min.js` of Bootstrap DatePicker.

You may also want to setup the specific locales from [cdnjs.com](https://cdnjs.com/libraries/bootstrap-datepicker).

Initialization is done inside the templates of the Bundle.

Step 8: How to use
------------------

The Route to display an event is `http://example.com/events/{event}`, the one to edit is `http://example.com/events/edit/{event}`.

A toolbar is displayed below the title if user is identified and has the acess rights.

Link to a page, in Twig, can be done by `<a href="{{ path('events_display', { 'page': 'slug' }) }}">Title of the event</a>`.

The different Routes (naming self-explanatory) available are:
- events_display
- events_new
- events_edit
- events_delete
- events_carousel
- events_all
- events_ical
- events_slug
- events_dashboard
- events_help

To include the carousel in a page, simply use `{{ render(controller('c975LEventsBundle:Events:carousel', {'number': 3})) }}` where you want it to appear.
