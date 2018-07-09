# CHANGELOG, 2.x

## 2.57.0 (not released yet, in development)

* [MPFE-1069] Configure languages in the system through UI
* [CLS-1408] Fixed bug with duplication of translation tokens, imported from different sources. BundleName field is removed from TranslationToken.
* [MPFE-1065][ModeraMjrIntegrationBundle] Update FontAwesome to v4.7.0
* [MPFE-1064][ModeraMjrIntegrationBundle] Update Moment.js to v2.21.0
* [CLS-1104] Localization improved, added ability to translate most of UI elements
* [CLS-1014] Added security for buttons for page Security and permissions: Users. Added permission for manage user profile information; To use the new permission, need to run the console command 'modera:security:install-permissions'
* [MPFE-1043][ModeraBackendTranslationsToolBundle] UI shows correct number of elements on page with filtering
* [MPFE-1050][ModeraSecurityBundle] Add validators for username and email
* [MPFE-1049][ModeraSecurityBundle] Clean non-letter characters from First & Last name, except "space" and "-"
* [CLS-1236][ModeraFileRepositoryBundle] Update version for gaufrette library
* [MPFE-984][ModeraTranslationsBundle] Collation utf8_bin is now not necessary for TranslationToken's entity

## 2.56.0 (05.10.2017)

* [MPFE-1045] Added possibility to specify the type of URL for generation urls in FileRepositoryBundle
* [MPFE-1042][ModeraTranslationsBundle] Optimized translation token import
* [MPFE-1041] ModeraDirectBundle now when an exception is thrown will consider the environment and in PROD will not
contain exception stack trace ; EnvAwareExceptionHandler class is deprecated
* [MPFE-1027] Now it is possible to delete a StoredFile even if a physical file has already been deleted. See 
StoredFile::setIgnoreMissingFileOnDelete() method for more details.
* [MPFE-1035] Fixed: If tools section has one item, it doesn't displayed.
* [CLS-894] Protect foundation uis from xss
* [MPFE-1036][ModeraBackendToolsActivityLogBundle] Added permission for accessing `Backend/Tools/Activity log`
* [MPFE-1033] 'Generate' and 'Send password to user`s e-mail' options available only when user is changing other account password
* [CLS-915] Added permission for accessing System settings; To use the new permission, need to run the console command 'modera:security:install-permissions'
* [MPFE-1030][ModeraBackendTranslationsToolBundle] Added permission for accessing `Backend/Tools/Translations`
* [MPFE-1014] A unified password management API is added - Modera\SecurityBundle\PasswordStrength. Using this API a 
password rotation, strength validation and other operations are implemented. For more information regarding how to upgrade
your code to leverage the API see the UPGRADE-2.x.md file. Here's a short summary of deprecated things and new alternatives:

| Old                                                                                                              | New alternative                                                                                                                    |
|------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------|
| \Modera\BackendSecurityBundle\Service\MailService and \Modera\BackendSecurityBundle\Service\MailServiceInterface | \Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface, \Modera\SecurityBundle\PasswordStrength\Mail\DefaultMailService |
| Semantic config modera_backend_security's keys - "mail_service" and "mail_sender"                                | See modera_security/password_strength/mail                                                                                         |
|                                                                                                                  |                                                                                                                                    |
* [MPFE-1019][ModeraBackendTranslationsToolBundle] UI improvements
* [MPFE-1017][ModeraMjrIntegrationBundle] Moment.js downgraded to v2.17.1 && added ability to change it from config file
    ``` yaml
    modera_mjr_integration:
        moment_js_version: 2.17.1
    ```
* [MPFE-1012][ModeraFileRepositoryBundle] Whenever a new file is added to a repository, if it happens
that a user is currently authenticated (using default Symfony security component), then we will try to
use that user and update "author" property on a created StoredFile automatically. Beside that, whenever you put
a new file to the repository, you can use "context" argument of \Modera\FileRepositoryBundle\Repository\FileRepository::put
method to specify "author" and "owner" properties which, if provided, will be set to the corresponding properties on
a created StoredFile.

## 2.55.0 (18.06.2017)

* [MPFE-1005] Added user deactivate functionality in `Backend/Tools/Security permissions`
* [MPFE-992] Fixed bug what prevent to assign permissions to a freshly created group in `Backend/Tools/Security permissions` section
* [MPFE-1007] Updated Moment.js to v2.18.1
* [CLS-675] UI improvement to show user ID
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

## 2.54.0 (30.01.2017)

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

## 2.53.0 (08.12.2016)

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
