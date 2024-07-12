<?php

namespace Modera\FoundationBundle\Testing;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * A base test case that you may extend when writing your functional tests, it allows you
 * to configure so-called isolation level of your test methods ( override getIsolationLevel() method ). Isolation level controls:
 * - at which point database transaction is discarded
 * - if your test has authenticated a user, then at which moment it will be automatically logged out
 * Two isolation levels are available:
 * - method -- After every test method transaction is discarded and user is logged out. This option is used by default.
 * - class -- Transaction will be discarded and user logged out only when last test method has finished its execution.
 *
 * This class has marked methods "setUp", "tearDown", "setUpBeforeClass", "tearDownAfterClass" as final and if you still
 * need to use them then you need to use "template methods" instead, just add "do" prefix to a method you need,
 * for instance, if you want to override "setUp" method then use "doSetUp" method instead.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2013 Modera Foundation
 */
class FunctionalTestCase extends WebTestCase
{
    public const IM_METHOD = 'method';
    public const IM_CLASS = 'class';

    /**
     * @var EntityManagerInterface
     */
    protected static $em;

    protected static function getContainer(): ContainerInterface
    {
        $container = static::$kernel->getContainer();
        if ($container->has('test.service_container')) {
            $container = $container->get('test.service_container');
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Test\TestContainer $container */
        $container = $container;

        return $container;
    }

    private static function rollbackTransaction(): void
    {
        $c = static::$em->getConnection();
        // having this check if there's an active transaction will let us
        // use this TC as parent-class for integration test cases
        // even if they don't use EM
        if ($c->isTransactionActive()) {
            $c->rollback();
        }
    }

    private static function emExists(): bool
    {
        return static::getContainer()->has('doctrine.orm.entity_manager');
    }

    final public static function setUpBeforeClass(): void
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        if (self::emExists()) {
            /** @var EntityManagerInterface $em */
            $em = static::getContainer()->get('doctrine.orm.entity_manager');
            static::$em = $em;
        }

        if (self::IM_CLASS === static::getIsolationLevel() && self::emExists()) {
            static::$em->getConnection()->beginTransaction();
        }

        static::doSetUpBeforeClass();
    }

    /**
     * Template method.
     */
    public static function doSetUpBeforeClass(): void
    {
    }

    final public static function tearDownAfterClass(): void
    {
        if (self::IM_CLASS === static::getIsolationLevel() && self::emExists()) {
            self::rollbackTransaction();
        }

        static::doTearDownAfterClass();
    }

    /**
     * Template method.
     */
    public static function doTearDownAfterClass(): void
    {
    }

    /**
     * Override this method to change isolation level of the test.
     */
    protected static function getIsolationLevel(): string
    {
        return self::IM_METHOD;
    }

    final public function setUp(): void
    {
        if (self::IM_METHOD === $this->getIsolationLevel() && $this->emExists()) {
            self::$em->getConnection()->beginTransaction();
        }

        $this->doSetUp();
    }

    /**
     * Template method.
     */
    public function doSetUp(): void
    {
    }

    final public function tearDown(): void
    {
        if (self::IM_METHOD === $this->getIsolationLevel() && $this->emExists()) {
            self::rollbackTransaction();
        }

        if (static::getContainer()->has('security.token_storage')) {
            $this->logoutUser();
        }

        $this->doTearDown();
    }

    /**
     * Template method.
     */
    public function doTearDown(): void
    {
    }

    /**
     * Will log out currently authenticated user.
     */
    public function logoutUser(): void
    {
        /** @var TokenStorageInterface $ts */
        $ts = static::getContainer()->get('security.token_storage');
        $ts->setToken(null);
    }

    /**
     * @param array{'environment'?: string, 'debug'?: bool} $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        global $_SERVER;

        // "MONOLITH_TEST_SUITE" variable can be set by a script which initiates running tests to signal
        // that this is a monolith repository, that is - the repository contains functional tests
        // for different bundles and in scope of one test run there's a chance that many app kernel instances
        // will be instantiated and as result we want to avoid in-memory caching (this is what original
        // "createClient" method actually does). In-memory caching speeds up test run but at the same
        // time might in case of monolithic repositories will lead to re-using of wrong app kernels
        $isMonolithTestSuite = isset($_SERVER['MONOLITH_TEST_SUITE']) && $_SERVER['MONOLITH_TEST_SUITE'];
        if ($isMonolithTestSuite) {
            static::$class = static::getKernelClass();

            $kernel = new static::$class(
                isset($options['environment']) ? $options['environment'] : 'test',
                isset($options['debug']) ? $options['debug'] : true
            );
        } else {
            // letting to use runtime-caching
            $kernel = parent::createKernel($options);
        }

        /** @var KernelInterface $kernel */
        $kernel = $kernel;

        return $kernel;
    }
}
