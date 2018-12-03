# UPGRADE

v2.0 > v2.1.x
-------------
The following parameters have been set to `c975LCommon` root, so you can delete them from your auto-generated file `config_bundles.yaml` or left them here (they won't be used'), but you have to re-set them in the `events_config` Route:
- tinymceApiKey
- tinymceLanguage

v1.x > v2.x
-----------
When upgrading from v1.x to v2.x you should(must) do the following if they apply to your case:

- The parameters entered in `config.yml` are not used anymore as they are managed by c975L/ConfigBundle, so you can delete them.
- As the parameters are not in `config.yml`, we can't access them via `$this[->container]->getParameter()`, so you have to replace `$this->getParameter('c975_l_events.XXX')` by `$configService->getParameter('c975LEvents.XXX')`, where `$configService` is the injection of `c975L\ConfigBundle\Service\ConfigServiceInterface`.
- The `tinymceApiKey` is now managed by c975L/ConfigBundle, so you can delete if from `parameters.yml` and `parameters.yml.dist`, but before that, copy/paste it in the config.
- Before the first use of parameters, you **MUST** use the console command `php bin/console config:create` to create the config files with default data.
