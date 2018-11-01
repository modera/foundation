# UPGRADE GUIDE, 2.x

## 2.57.0 (01.11.2018)

* [MPFE-984] Collation utf8_bin is now not necessary for TranslationToken's entity, so you may set another one via running query in your MySQL database. For example: `ALTER TABLE modera_translations_translationtoken CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;`.

## 2.56.0 (05.10.2017)

* [MPFE-1014] Semantic configuration properties `modera_backend_security/mail_service` and `modera_security/password_strength/mailer`
 are no longer used, instead use `modera_security/password_strength/mail`. If you need to use custom mailer to send
 emails with a password for a user then instead of relaying on `\Modera\BackendSecurityBundle\Service\MailServiceInterface` use
 `\Modera\SecurityBundle\PasswordStrength\Mail\MailServiceInterface` (the interface used to be declared in `ModeraBackendSecurityBundle`, 
 but now it is in `ModeraSecurityBundle`), semantic configuration property `modera_security/password_strength/mailer/service` now 
 should point to an implementation of MailServerInterface implementation from `ModeraSecurityBundle`.
* [MPFE-1012][ModeraFileRepositoryBundle] Make sure that \Symfony\Bundle\SecurityBundle\SecurityBundle() is enabled in
your kernel and there's at least one firewall and user provided configured (file repository now requires
"security.token_storage" service to be present).

## 2.55.0 (18.06.2017)

* [MPFE-963] Security permission technical names were renamed, see the CHANGELOG-2.x.md for more details. The renaming
  has been done in a backward compatible manner, so you still are able to contribute to categories using their
  old technical names, but it is highly recommended though to update your permission contributions so they would
  use new technical names (in your PermissionsProvider classes, when creating instances of Permission class update its
  3rd constructor argument using mapping provided in CHANGELOG-2.x). It is also highly desirable to run 
  `modera:security:install-permission-categories` as it can automatically update your database to use proper technical 
  names for already existing data. Beside that now whenever you are adding creating a contribution using an old technical 
  names for a category a deprecation notice is created (you can see it in log files, search for php.INFO substring).
* bugfix [MPFE-984] Run query `ALTER TABLE modera_translations_translationtoken CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin;` 
  in MySQL database.
* [MPFE-975] Install version 0.5.0 of MJR ([see this for all published versions](https://mjr-archives.modera.org/)). 
  In order to do that update your composer.json's `modera/mjr` dependency so it would point to `0.5.0` and run `composer update`
  (if 0.5.0 is not released yet then use `dev-master` instead).

## 2.54.0 (30.01.2017)

* [MPFE-959] Run `app/console modera:security:install-permission-categories` and 
  `app/console modera:security:install-permissions` to have permission categories and permissions automatically renamed.
  Permission category IDs were not renamed at the moment, it might be done later in scope of MPFE-963.
    
* [MPFE-951] Make sure that you have MJR version at least 0.3.0 (in your composer.json file it is `modera/mjr` package).

## 2.53.0 (08.12.2016)

* In scope of MPFE-852 a DI service `modera_server_crud.persistence.default_handler` has been renamed to 
`modera_server_crud.persistence.doctrine_handler`, the service references now deprecated DoctrinePersistenceHandler. A new
service `modera_server_crud.persistence.doctrine_registry_handler` is added which you should start using now, the
AbstractCrudController class has also been updated to start using `doctrine_registry_handler` instead.

## 2.52.0 (19.09.2016)

* In scope of MPFE-929 (see changelog file) new properties were added to StoredFile entity, database schema update is 
required - `app/console doctrine:schema:update --force`, make sure to backup database before running this command. 
* In scope of MPFE-930 updates to Composer's listeners were done, in order for the new event listener to work properly
you need to update your composer.json "scripts" section so declarations of ModeraModuleBundle's ScriptHandler would look
like this:

        "post-package-install": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",
        "post-package-update": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",
        "pre-package-uninstall": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",
