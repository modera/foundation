# CHANGELOG, 2.x

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