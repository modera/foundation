# CHANGELOG, 2.x

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