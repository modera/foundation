# Notes, thoughts regarding upgrade to Modera Foundation 5.x

MF 5.x will presumably require:

 * PHP >=7.4
 * Symfony >=5.4,<=6.4
 * ExtJs 4.2
 * Review existing in-house code base and if something can be done with Symfony/other trusted libraries - use them. 
 Goal: minimize code-base we need to maintain

## Current BC layer

### Blocking/non-blocking assets for backend

Before v5.x all contributed JS/CSS assets to backend are considered as blocking, when v5.x is released all assets might be
considered as non-blocking and if you still want your asset to be considered as blocking suffix it with "!". Already now you can
start marking your assets as blocking using ! if you are sure that those are needed to be loaded before backend page
has rendered.

If you still want to mark some assets as non-blocking even now, then you can use `non_blocking_assets_patterns` configuration
property. This, for instance, will mark all assets which match `^/bundles/moderabackend.*` regexp as non-blocking:

    modera_backend_on_steroids:
        non_blocking_assets_patterns:
            - ^/bundles/moderabackend.*

`non_blocking_assets_patterns` configuration property will be removed from `\Modera\BackendOnSteroidsBundle\DependencyInjection\Configuration`.

## TODOs

 * It should be possible just by changing one configuration property completely switch backend's url (so even Symfony's firewall would be automatically re-configured)
 * To keep things consistent, ModeraMjrIntegrationBundle must be renamed to ModeraMJRIntegrationBundle
 * Remove \Modera\SecurityBundle\DataInstallation\BCLayer and update PermissionAndCategoriesInstaller so it wouldn't use it.
 * All methods in \Modera\FileRepositoryBundle\Intercepting\OperationInterceptorInterface must contain last argument $context. See changelog of a commit where this piece of text is written for more details.
 * Remove deprecated \Modera\FileRepositoryBundle\StoredFile\UrlGeneratorInterface and \Modera\FileRepositoryBundle\StoredFile\UrlGenerator.
