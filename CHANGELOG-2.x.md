# CHANGELOG, 2.x

## 2.55.0 (not released yet, in development)

* [MPFE-963] Technical names for permission categories (extension point modera_security.permission_categories) were 
  renamed as follows:
  
   | Old technical name | New technical name |
   |--------------------|--------------------|
   | user-management    | administration     |
   | site               | general            |
    
  See UPGRADE-2.x.md for instructions what you might need to do to make your code compatible with the change.

* [MPFE-984] TranslationToken's entity collation now is utf8_bin.
* [MPFE-975] Now it is possible to change landing section per user through UI. With the new requirements "dashboard" section
  now is shown in the menu only if it is marked as a landing section, otherwise it is hidden.
* bugfix [MPFE-977] A standard HTTP authentication didn't work properly with Authenticator class from ModeraSecurityBundle.
* [MPFE-980] JavaScript assets dynamically contributed to /backend section now are prefixed with last modification timestamp, 
 this is required to enable optimistic browser caching (for more details see 
 [UPGRADE.md for modera/foundation v0.11](https://github.com/modera/foundation-standard/blob/master/UPGRADE.md))
* [MPFE-975] Customize landing view per user

## 2.54.0 (30.01.17)

* bugfix [MPFE-974] TranslationsController from BackendTranslationsTooBundle could not properly render exception
 in listWithFiltersAction() method. This bug was caused by removal of "modera_server_crud.persistence.default_handler"
 in 2.53.0 (which is restored back in this release, see below MPFE-954).
* [MPFE-961] HostPanel of ModeraBackendToolsBundle now generates ID for every nested element, it is easier to write
E2E tests for it now
* bugfix [MPFE-958] When clicking on login button twice, help sign appearing twice problem fixed
* [MPFE-966] Backend / Tools / Security permissions / Permissions UI now lists all available permissions not the first
25 rows.
* [MPFE-964] A separate "Administration" permission group is added, from now on all contributions to old "user-management"
  group will automatically go to "Administration" instead
* [MPFE-959] Some permissions and permission categories labels were renamed:

    | Old name                        | New name             | Type       |
    |---------------------------------|----------------------|------------|
    | Site                            | General              | Category   |
    | User management                 | Administration       | Category   |
    | Access administration interface | Access Backend       | Permission |
    | Access Tools section            | Access Tools Section | Permission |
    | Access users and groups manager | Access Users Manager | Permission |
    | Manage user profiles            | Manage User Profiles | Permission |
    | Manage permissions              | Manage Permissions   | Permission |
    
 If the labels turn out to be viable then later in scope of MPFE-963 their ID will also be renamed to match labels,
 for now old IDs can be used to contribute new permission to categories.
    
* bugfix [CLS-534][MPFE-941] some backend classes' translation tokens now are formatted properly, they were preventing UI
from being rendered.
* [MPFE-954] "modera_server_crud.persistence.default_handler" service reverted and can be used now.  
* feature [MPFE-951] ModeraMjrIntegrationBundle now provides new extension-point "modera_mjr_integration.help_menu_items",
which adds support for contributing implementations of `\Modera\MjrIntegrationBundle\Help\HelpMenuItemInterface` to a newly
added "Help" menu (which is rendered if there's at least one HelpMenuItem available, the Help icon is rendered where
username and exit buttons are in the Backend's header). To see more information regarding how you a contribution
to this extension points could look like run 
`app/console sli:expander:explore-extension-point modera_mjr_integration.help_menu_items`, if you want to generate an 
empty contribution for then you can use `app/console sli:expander:list-extension-points` command. **This feature requires
MJR version to be at least 0.3.0.**

## 2.53.0 (08.12.16)

* feature [MPFE-852] ModeraServerCrudBundle now is able to deal with optionally custom EntityManager, to achieve
 this a new implementation of PersistenceHandlerInterface has been added - DoctrineRegistryPersistenceHandler, which
 uses an implementation of RegistryInterface to resolve entity manager which should be used for an entity. See
 UPGRADE-2.x.md for more details.
* feature [MPFE-949] now all CSS script URLs in /backend section will contain timestamp of their last modification
* feature [MPFE-950] a composer.json has been regenerated with help of modera/composer-monorepo-plugin, now it
 contains all dependencies of nested bundles and therefore can be easily installed as a standalone package
* feature [MPFE-946] Added ability to run scripts from nested JSON (usually are composer.json) files. By specifying
 this kind of "extra" in your root composer.json:
 
         "extra": {
            "modera-module": {
                "include": [
                    "src/Modera/*/composer.json"
                ]
            }
         }
         
 All composer.json files which match "include" GLOB expression would have their "scripts" tags executed as if they were
 declared in root composer.json. Here's a sample part of nested composer.json:
   
        // included
        "extra": {
           "modera-module": {
               "scripts": {
                   "post-package-install": [
                       "Modera\\ModuleBundle\\Composer\\ScriptHandler::doctrineSchemaUpdate"
                   ],
                   "post-package-update": [
                       "Modera\\ModuleBundle\\Composer\\ScriptHandler::doctrineSchemaUpdate"
                   ]
               }
           }
        }

## 2.52.2 (22.09.2016)

* bugfix [MPFE-933] Running functional tests (which use FunctionalTestCase class) outside of monolithic repository causes 
"Segmentation Fault (core dump)" error

## 2.52.1 (19.09.2016)

* bugfix [MPFE-930] Command `modera:file-repository:generate-thumbnails` now won't generate duplicate thumbnails if thumbnails
for requested sizes have already been generated before.

## 2.52.0 (19.09.2016)

 * feature [MPFE-929] Now it is possible to generate thumbnails when files are uploaded to file-repository. See ModeraFileRepository
 README.md for more details.
 * bugfix [MPFE-930] ModeraModuleBundle's ScriptHandler updated to use latest Composer's API, no deprecation notices now are
 shown when running composer install/update
 * feature [MPFE-924] Writes tests for AbstractFunctionalKernel
 
## 2.51.1

* feature; A tiny composer.json dependency tweak when referencing doctrine/orm package

## 2.51.0

* feature [MPFE-922] Ability to add custom interceptors for client-side security manager