# Modera Foundation [![Build Status](https://travis-ci.org/modera/foundation.svg?branch=master)](https://travis-ci.org/modera/foundation)

A monolith repository for Modera Foundation platform.

This project's composer.json is managed in semi-automatic mode by https://github.com/modera/composer-monorepo-plugin
plugin, please make sure that you use this plugin when you are making changes to this monolithic repository and
before making a commit verify that a proper composer.json is generated and tests pass.

Branches:

 * master - will eventually be compatible with Symfony 3.x
 * 2.x - currently used in production, contains Symfony 2.8.x compatible codebase

More details - [http://modera.org](http://modera.org)