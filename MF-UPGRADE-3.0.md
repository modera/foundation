# Notes, thoughts regarding upgrade to Modera Foundation 3.0

MF 3.0 will presumably require:

 * Symfony 3.x
 * ExtJs 6.x
 * PHP 5.5>=
 * Review existing in-house code base and if something can be done with Symfony/other trusted libraries - use them. Goal:
 minimize code-base we need to maintain

## API

Questions to answer - what is considered to be API of a bundle and how we will designate it ? For example,
Symfony marks those classes which are part of its public API with annotation @api. By default, all semantic configuration
exposed by its bundles are also considered as public API. We need to think about similar process for us as well.

Questions:

 * Services from DI container, how we will mark those as public/private API ?
 * Extension points are marked as part of public API by default ? How to designate them ? Some mechanism to mark
 and make it very easy to see that a given extension point is deprecated ?
 * An easy way for a developer to see a public API exposed by a bundle ? Create some piece of software that would
 automatically generate some nicely formatted document that can be used to see which public API is ?

Once this question is answered we will be more confident about what we can change it our bundles without making
developers who use our solutions to suffer (they can use our private APIs of course, but if they do they will have
to understand that this piece of API they rely on might be gone without prior notice).

## Current BC layer

### Blocking/non-blocking assets for backend

Before 3.0 all contributed JS/CSS assets to backend are considered as blocking, when 3.0 is released  all assets might be
considered as non blocking and if you still want your asset to be considered as blocking suffix it with "!". Already now you can
start marking your assets as blocking using ! if you are sure that those are needed to be loaded before backend page
has rendered.

If you still want to mark some assets as non-blocking even now, then you can use `non_blocking_assets_patterns` configuration
property. This, for instance, will mark all assets which match `^/bundles/moderabackend.*` regexp as non-blocking:

    modera_backend_on_steroids:
        non_blocking_assets_patterns:
            - ^/bundles/moderabackend.*

`non_blocking_assets_patterns` configuration property will be removed from `\Modera\BackendOnSteroidsBundle\DependencyInjection\Configuration`.

### Security permission categories

When contributing permissions (using `modera_security.permissions` extension point) you should rely on new technical
names, old ones will be removed in 3.0.

| Old technical name              | New technical name   | 
|---------------------------------|----------------------|
| site                            | general              |
| user-management                 | administration       |

## TODOs

 * Stop using [sergeil/extjsintegration-bundle](https://github.com/sergeil/SliExtJsIntegrationBundle) and instead use
 [sergeil/doctrine-array-query-builder-bundle](https://github.com/sergeil/SliDoctrineArrayQueryBuilderBundle) and
 [sergeil/doctrine-entity-data-mapper-bundle](https://github.com/sergeil/SliDoctrineEntityDataMapperBundle).
 * Consider refactoring existing [ModeraDirectBundle](https://github.com/modera/ModeraDirectBundle) or creating
 a new solution (its performance, route generation approach can be improved, security issues)
 * It should be possible just by changing one configuration property completely switch backend's url (so even Symfony's
 firewall would be automatically re-configured)
 * \Modera\MjrIntegrationBundle\Model\FontAwesome must be refactored to a service, it shouldn't be a "helper-like" class
 with static methods; its cache should be stored in app/cache/*/ directory
 * To keep things consistent, ModeraMjrIntegrationBundle must be renamed to ModeraMJRIntegrationBundle
 * \Modera\FileUploaderBundle\Controller\UniversalUploaderController - "error" response codes must be renamed to "errors".
 * \Modera\SecurityBundle\Security\Authenticator should be refactored into several classes probably - at the moment
 it is responsible for generating authentication responses as well as changing user status when it is authenticated
 for the first time. Maybe for changing user-status we should rely on [Symfony events](http://symfony.com/doc/current/components/security/authentication.html#authentication-events).
 * "security.user_provider" service should be removed from @ModeraSecurityBundle:security.yml, it could confuse
 developer that it is a service provided by Symfony (because it is declared in "security" namespace)
 * \Modera\MJRCacheAwareClassLoaderBundle\EventListener\VersionInjectorEventListener now is responsible for injecting
 "X-Modera-Version" header, but it would be more correct to have a universal "product/foundation" version number, so
  probably version resolving logic should be moved to ModeraFoundationBundle. In other words, ModeraMJRCacheAwareClassLoaderBundle
  would only contain code to update ExtJs class loader (maybe it even makes sense to get rid of
  ModeraMJRCacheAwareClassLoaderBundle bundle at all and move its code to ModeraMjrIntegrationBundle ?)
 * Remove \Modera\SecurityBundle\DataInstallation\BCLayer and update PermissionAndCategoriesInstaller so it wouldn't use
 it.
 * All methods in \Modera\FileRepositoryBundle\Intercepting\OperationInterceptorInterface must contain last argument $context. See
 changelog of a commit where this piece of text is written for more details. We coudln't change it in scope of 2.x version
 without BC break because we it is not possible to change a signature of methods defined in an interface.
 * Remove deprecated \Modera\FileRepositoryBundle\StoredFile\UrlGeneratorInterface and 
 \Modera\FileRepositoryBundle\StoredFile\UrlGenerator.
 * modera_security/password_strength must be enabled by default, remove "enabled" flag and corresponding logic
 * See \Modera\ServerCrudBundle\Controller\AbstractCrudController::batchUpdateAction FIXME section, we need to pass $recordParams
 instead of just $params
 * \Modera\BackendSecurityBundle\Service\MailService and its interface and DI service has been deprecated, classes
 from package Modera\SecurityBundle\PasswordStrength\Mail must be used instead ; Semantic config modera_backend_security/mail_service, 
 mail_sender deprecated in favor of modera_security/password_strength/mailer ; \Modera\BackendSecurityBundle\DependencyInjection\ServiceAliasCompilerPass
 should be removed