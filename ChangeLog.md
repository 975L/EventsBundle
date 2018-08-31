# Changelog

v1.17.5
-------
- Fixed Voter constants (31/08/2018)

v1.17.4.2
---------
- Used a `switch()` for the FormFactory more readable (27/08/2018)

v1.17.4.1
---------
- Removed ContainerInterface in EventFormFacory as not used (27/08/2018)

v1.17.4
-------
- Added EventFormFactory + Interface (27/08/2018)
- Added 'clone' Service (27/08/2018)

v1.17.3
-------
- Removed 'true ===' as not needed (25/08/2018)
- Added dependency on "c975l/config-bundle" and "c975l/services-bundle" (26/08/2018)
- Corrected modify method as it was creating another event due to test of slug's 'unicity (26/08/2018)
- Corrected Repository methods orderBy (26/08/2018)

v1.17.2
-------
- Replaced links in dashboard by buttons (25/08/2018)

v1.17.1.1
---------
- Corrected documentation (22/08/2018)
- Changed method to test if slug exists (23/08/2018)

v1.17.1
-------
- Re-ordered Controller > `display()` (22/08/2018)
- Added redirect to good slug on `events_ical` Route (22/08/2018)

v1.17
-----
- Added Voter for Slug Route (02/08/2018)
- Removed FQCN (21/08/2018)
- Added documentation (21/08/2018)
- Added link to BuyMeCoffee (21/08/2018)
- Added link to apidoc (21/08/2018)
- Split `EventsService` in multiple files and creation of related Interfaces (21/08/2018)
- Injected Services in place of using `container->get()` (21/08/2018)
- Made redirection slug indicated in url and the one registered don't match (21/08/2018)
- Added all events in dashboard, even those suppressed (21/08/2018)
- Added Stardt date in Dashboard (21/08/2018)
- Removed id and semantic url from Dashboard (21/08/2018)
- Corrected sorting options in Dashboard (21/08/2018)
- Removed Route `events_finished` as they are now included in Dashboard (21/08/2018)
- For `events_all`, moved "Add to calendar" button under the title of the Event (21/08/2018)

v1.16.1
-------
- Update slugify method to check unicity of slug (02/08/2018)
- Changed order (alphabetical) for `deleteImage()` method (02/08/2018)
- Renamed `findAllAvailable()` method (02/08/2018)
- Renamed things link to `add` to `create` (02/08/2018)
- Ordered in alphabetical Voters constants (02/08/2018)

v1.16
-----
- Made use of ParamConverter (30/07/2018)
- Corrected services.yml (31/07/2018)
- Made use of Voters for access rights (01/08/2018)
- Renamed `new` to `add` to avoid using php reserved word (01/08/2018)
- Added missing @Method in @Route (01/08/2018)
- Suppressed toolbar in display when user hasn't signed in (01/08/2018)
- Renamed `$event` to `$eventObject` to avoid problems with `$event` Symfony object (01/08/2018)
- Corrected toolbar (01/08/2018)

v1.15.2
-------
- Removed `SubmitType` in EventType and replaced by adding button in template as it's not a "Best Practice" (Revert of v1.13.6) (21/07/2018)

v1.15.1
-------
- Corrected TwigExtension `EventsCarousel` (21/07/2018)

v1.15
-----
- Removed required in composer.json (22/05/2018)
- Removed `Action` in controller method name as not requested anymore (21/07/2018)
- Corrected meta in `layout.html.twig` (21/07/2018)
- Injected EventService in Controller methods (21/07/2018)
- Use of Yoda notation (21/07/2018)
- Added data for Symfony 4 (21/07/2018)
- Renamed `setPicture()` method to `defineImage()` (21/07/2018)
- Removed events_carousel Route [BC-break] (21/07/2018)

v1.14.3
-------
- Corrected `tools.html.twig` (13/05/2018)

v1.14.2
-------
- Modified toolbars calls due to modification of c975LToolbarBundle (13/05/2018)

v1.14.1
-------
- Moved "add to calendar" in a row below event's data, in display all events view, to allow more space on xs-devices (28/04/2018)

v1.14
-----
- Made some fields nullable (27/04/2018)
- Made suppressed field as default false (27/04/2018)
- Added link to all events in event display (27/04/2018)
- Removed end date + time if null in display + list of events (27/04/2018)
- Removed finished events from list of events (27/04/2018)
- Added list of finished events (27/04/2018)
- Added class "text-muted" for finished events (27/04/2018)

v1.13.6
-------
- Replaced submit button by `SubmitType` (16/04/2018)

v1.13.5
-------
- Removed unused parenthesis (15/04/2018)
- Added missing dependencies on doctrine in `composer.json` (15/04/2018)

v1.13.4
-------
- Added "_locale requirement" part for multilingual prefix in `routing.yml` in `README.md` (04/03/2018)
- Removed `action` property on Entity `Event` and passed data with array `eventConfig` to the form (19/03/2018)

v1.13.3
-------
- Added Route for "/events/" to redirect to "events_all" (03/03/2018)
- Re-Added Route "events_carousel", with "E_USER_DEPRECATED", to respect SEMVER (03/03/2018)

v1.13.2
-------
- Added test to not display "Add to calendar" in list mode if the evant is finished (01/03/2018)

v1.13.1
-------
- Corrected number of events to display controls in carousel (01/03/2018)

v1.13
-----
- Removed Route `events_carousel` and replaced by a Twig_Extension, more simple [BC-Break] (28/02/2018)
- Corrected Tinymce call with apiKey (01/03/2018)
- For the carousel, nothing is displayed if there are no events (01/03/2018)
- Removed the "|raw" for `toolbar_button` call as safe html is now sent (01/03/2018)
- included title in `carousel.hhtml.twig` template (01/03/2018)

v1.12.1
-------
- Corrected call for bootstrap in `tinymceInit.html.twig` (28/02/2018)

v1.12
-----
- Added c957L/IncludeLibrary to include libraries in `layout.html.twig` (27/02/2018)
- Changed `tinymceInit.html.twig` for include of Tinymce via c975L/IncludeLibrary (27/02/2018)

v1.11
-----
- Added 'Command' part auto-wire to `services.yml` (20/02/2018)
- Abandoned Glyphicon and replaced by fontawesome (22/02/2018)

v1.10.2
-------
- Corrected help pages (19/02/2018)

v1.10.1
-------
- Corrected default call for help page (18/02/2018)
- Corrected english help pages (19/02/2018)
- Added duplicate option in help pages (19/02/2018)

v1.10
-----
- Adjusted comments in `EventsService.php` (05/02/2018)
- Update `README.md` (07/02/2018)
- Renamed templates for forms (17/02/2018)
- Corrected title in delete template form (17/02/2018)
- Added `cancel` action to toolbar and removed from bottom of forms (17/02/2018)
- Put `New` button before `edit` one in toolbar (17/02/2018)
- Changed wording for submit button for forms (17/02/2018)
- Added possibility to duplicate an event (17/02/2018)
- Corrected @return value in Entity (18/02/2018)
- Renamed `edit` to `modify` (18/02/2018)
- Removed translations taken from `c975L/ToolbarBundle` (18/02/2018)

v1.9
----
- Changed forgotten toolbars... (05/02/2018)
- Converted functions in Controller to Service (05/02/2018)
- Updated `services.yml` (05/02/2018)
- Moved link to display event under the name of the event and suppression of the link "display" in the dashboard page (05/02/2018)
- Added icon for sortable inks in dashboard (05/02/2018)
- Added "No events" information in dashboard (05/02/2018)
- Renamed `eventDisplay.html.twig` to `display.html.twig` (05/02/2018)

v1.8.3
------
- Updated ToolbarBundle product -> dashboard (05/02/2018)

v1.8.2
------
- Corrections in `README.md` (04/02/2018)

v1.8.1
------
- Correction in the `README.md` file (04/02/2018)
- Add dates in the `ChangeLog.md` file (04/02/2018)

v1.8
----
- Correction in `SitemapCreateCommand.php` (17/08/2017)
- Change about composer download in `README.md` (04/02/2018)
- Typo in `README.md` (04/02/2018)
- Add support in `composer.json`+ use of ^ for versions request (04/02/2018)
- Replace toolbar by use of c975LToolbarBundle (04/02/2018)

v1.7.2
------
- Separation of information about parameters.yml in `README.md` (16/08/2017)
- Direct call of Tinymce API key instead of repeating it in `config.yml` (16/08/2017)
- Changes in the `README.md` file (16/08/2017)

v1.7.1
------
- Add "Best practice" for tinymceApiKey (08/07/2017)
- Run PHP CS-Fixer (18/07/2017)
- Remove of .travis.yml as tests have to be defined before (18/07/2017)

v1.7
----
- Update of README.md (06/07/2017)
- Move of translated help pages to sub-folder `langugages` (07/07/2017)
- Make `tinymceInit.html.twig` re-usable by setting config keys `tinymceApiKey` and `tinymceLanguage` (07/07/2017)
- Redirection to dashboard in case of delete an event, in place of redirecting to the deleted event (07/07/2017)
- Add of signout button on toolbar + config signoutRoute (07/07/2017)
- Add of main dashboard button on toolbar + config dashboardRoute (07/07/2017)

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
- Move the call of `tinymce.js` and `datePicker.js` to `tinymceInit.html.twig` instead of `layout.html.twig` to avoid calling it for pages that don't need it (03/07/2017)
- Group toolbars in one file (03/07/2017)
- Add of semantic url value in dashboard (03/07/2017)
- Add of link to dashboard on Events label in toolbar and remove of dashboard button (03/07/2017)
- Remove of "required" on textarea.tinymce as it won't submit for a new page, a refresh has to be done - https://github.com/tinymce/tinymce/issues/2584 (03/07/2017)
- Add a Console Command to create sitemap of managed events (03/07/2017)

v1.5
----
- Change wording for validate button (30/06/2017)
- Move `tinymceInit.html.twig` to `views` folder in order to simplify it's overridding as Tinymce can be initialized only once (30/06/2017)
- Add options to Tinymce init (30/06/2017)
- Add information on help pages (30/06/2017)
- Add link to dedicated web page (30/06/2017)

v1.4
----
- Move in a separate file of the initialization of Tinymce (29/06/2017)
- Add of pagination via KnpPaginator (dashboard + list of all events) (29/06/2017)
- Remove of slugify function and replace by cocur/slugify (29/06/2017)

v.1.3.1
-----
- Remove options related to media not used by default (21/06/2017)

v1.3
----
- Add of returning a text inplace of carousel if there are no events (20/06/2017)
- Corrections in help files (20/06/2017)
- Remove of the extension of layout for Carousel in order to be included in other pages (20/06/2017)
- Add of dashboard and help link for toolbar in display mode (20/06/2017)
- Misc corrections (20/06/2017)
- Remove of require image when editing event ad that a file has already been downloaded (20/06/2017)
- Add options for datePicker (20/06/2017)

v1.2
----
- Removed from config.yml of number to set it from url or call from Twig (20/06/2017)
- Changed the query to retrieve events for Carousel to be based on enddate instead of startDate (20/06/2017)
- Added the critria not null for carousel query (20/06/2017)

v1.1
----
- Add of code files (20/06/2017)
- Update of README.md (20/06/2017)

v1.0
----
- Creation of bundle (12/06/2017)
