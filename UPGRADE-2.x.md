# UPGRADE GUIDE, 2.x

## 2.54.0 (not released yet, in development)

* [MPFE-951] Make sure that you have MJR version at least 0.3.0 (in your composer.json file it is `modera/mjr` package).

## 2.53.0 (08.12.16)

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