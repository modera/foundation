# UPGRADE GUIDE, 2.x

## 2.52.0 (19.09.2016)

* In scope of MPFE-929 (see changelog file) new properties were added to StoredFile entity, database schema update is 
required - `app/console doctrine:schema:update --force`, make sure to backup database before running this command. 
* In scope of MPFE-930 updates to Composer's listeners were done, in order for the new event listener to work properly
you need to update your composer.json "scripts" section so declarations of ModeraModuleBundle's ScriptHandler would look
like this:

        "post-package-install": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",
        "post-package-update": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",
        "pre-package-uninstall": "Modera\\ModuleBundle\\Composer\\ScriptHandler::packageEventDispatcher",