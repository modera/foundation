# Notes, thoughts regarding upgrade to Modera Foundation 5.x

MF 5.x will presumably require:

 * PHP >=7.4
 * Symfony >=5.4,<=6.4
 * ExtJs 4.2
 * Review existing in-house code base and if something can be done with Symfony/other trusted libraries - use them.  Goal: minimize code-base we need to maintain

## TODOs

 * It should be possible just by changing one configuration property completely switch backend's url (so even Symfony's firewall would be automatically re-configured)
 * To keep things consistent, ModeraMjrIntegrationBundle must be renamed to ModeraMJRIntegrationBundle
 * Remove \Modera\SecurityBundle\DataInstallation\BCLayer and update PermissionAndCategoriesInstaller so it wouldn't use it.
 * All methods in \Modera\FileRepositoryBundle\Intercepting\OperationInterceptorInterface must contain last argument $context. See changelog of a commit where this piece of text is written for more details.
 * Remove deprecated \Modera\FileRepositoryBundle\StoredFile\UrlGeneratorInterface and \Modera\FileRepositoryBundle\StoredFile\UrlGenerator.
