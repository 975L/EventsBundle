# Changelog

v1.6.2
------
- Update of README.md (03/07/2017)
- Move `title`value in Twig templates in place of Controller, more simple (04/07/2017)

v1.6.1
------
- Rename url parameter 'event' to 'slug' (03/07/2017)

v1.6
----
- Remove of "<div class="container">" in templates as it extends `layout.html.twig` and this kind of data has to be set site by site (03/07/2017)
- Move the call of `tinymce.js` and `datePicker.js` to `tinymceInit.html.twig` instead of `layout.html.twig` to avoid calling it for pages that don't need it.
- Group toolbars in one file
- Add of semantic url value in dashboard
- Add of link to dashboard on Events label in toolbar and remove of dashboard button
- Remove of "required" on textarea.tinymce as it won't submit for a new page, a refresh has to be done - https://github.com/tinymce/tinymce/issues/2584
- Add a Console Command to create sitemap of managed events

v1.5
----
- Change wording for validate button (30/06/2017)
- Move `tinymceInit.html.twig` to `views` folder in order to simplify it's overridding as Tinymce can be initialized only once
- Add options to Tinymce init
- Add information on help pages
- Add link to dedicated web page

v1.4
----
- Move in a separate file of the initialization of Tinymce (29/06/2017)
- Add of pagination via KnpPaginator (dashboard + list of all events)
- Remove of slugify function and replace by cocur/slugify

v.1.3.1
-----
- Remove options related to media not used by default (21/06/2017)

v1.3
----
- Add of returning a text inplace of carousel if there are no events (20/06/2017)
- Corrections in help files
- Remove of the extension of layout for Carousel in order to be included in other pages
- Add of dashboard and help link for toolbar in display mode
- Misc corrections
- Remove of require image when editing event ad that a file has already been downloaded
- Add options for datePicker

v1.2
----
- Removed from config.yml of number to set it from url or call from Twig (20/06/2017)
- Changed the query to retrieve events for Carousel to be based on enddate instead of startDate
- Added the critria not null for carousel query

v1.1
----
- Add of code files (20/06/2017)
- Update of README.md

v1.0
----
- Creation of bundle (12/06/2017)