# Modera Foundation [![Build Status](https://travis-ci.org/modera/foundation.svg?branch=master)](https://travis-ci.org/modera/foundation)

A monolith repository for Modera Foundation platform.

This project's composer.json is managed in semi-automatic mode by [composer-monorepo-plugin](https://github.com/modera/composer-monorepo-plugin)
plugin, please make sure that you use this plugin when you are making changes to this monolithic repository and
before making a commit verify that a proper composer.json is generated and tests pass.

For more details regarding each nested bundle refer to its README.md file.

For more general details see [http://modera.org](http://modera.org)

## Contributing to the project

* Before starting to work on your feature make sure that tests pass by running `./phpunit.sh`
* Create a "feature branch"
* Write tests/your features, make sure that no tests were broken, commit everything into the feature branch
* Update CHANGELOG to include information regarding work that has been done
* If your changes require some additional work for developers using foundation then also update UPGRADE doc
* Merge your feature branch to master / any release branches (if exist)

### Developing in foundation-standard

When you are adding a feature that contains UI code then usually you will want to have a working project where you can
see how well it plays with rest the platform, in order achieve that you use install 
[foundation-standard](https://github.com/modera/foundation-standard), update its `composer.json` so it would use
"dev-master" version of `modera/foundation` package and run `composer update`. Once development version of foundation
has been installed you can go to `vendor/modera/foundation` to start working on your feature as described in a previous
section.

