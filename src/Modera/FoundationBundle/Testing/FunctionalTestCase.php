<?php

namespace Modera\FoundationBundle\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

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
    const IM_METHOD = 'method';
    const IM_CLASS = 'class';

    /* @var \Doctrine\ORM\EntityManager */
    protected static $em;
    /* @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected static $container;

    private static function rollbackTransaction()
    {
        $c = static::$em->getConnection();
        // having this check if there's an active transaction will let us
        // to use this TC as parent-class for integration test cases
        // even if they don't use EM
        if ($c->isTransactionActive()) {
            $c->rollback();
        }
    }

    private static function emExists()
    {
        return static::$container->has('doctrine.orm.entity_manager');
    }

    /**
     * {@inheritdoc}
     */
    final public static function setUpBeforeClass()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        if (self::emExists()) {
            static::$em = static::$container->get('doctrine.orm.entity_manager');
        }

        if (static::getIsolationLevel() == self::IM_CLASS && self::emExists()) {
            static::$em->getConnection()->beginTransaction();
        }

        static::doSetUpBeforeClass();
    }

    /**
     * Template method.
     */
    public static function doSetUpBeforeClass()
    {
    }

    /**
     * {@inheritdoc}
     */
    final public static function tearDownAfterClass()
    {
        if (static::getIsolationLevel() == self::IM_CLASS && self::emExists()) {
            static::rollbackTransaction();
        }

        static::doTearDownAfterClass();
    }

    /**
     * Template method.
     */
    public static function doTearDownAfterClass()
    {
    }

    /**
     * Override this method to change isolation level of the test.
     *
     * @return string
     */
    protected static function getIsolationLevel()
    {
        return self::IM_METHOD;
    }

    /**
     * {@inheritdoc}
     */
    final public function setUp()
    {
        if ($this->getIsolationLevel() == self::IM_METHOD && $this->emExists()) {
            self::$em->getConnection()->beginTransaction();
        }

        $this->doSetUp();
    }

    /**
     * Template method.
     */
    public function doSetUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    final public function tearDown()
    {
        if ($this->getIsolationLevel() == self::IM_METHOD && $this->emExists()) {
            self::rollbackTransaction();
        }

        if (static::$container->has('security.context')) {
            $this->logoutUser();
        }

        $this->doTearDown();
    }

    /**
     * Template method.
     */
    public function doTearDown()
    {
    }

    /**
     * Will logout currently authenticated user.
     */
    public function logoutUser()
    {
        /* @var SecurityContextInterface $securityContext */
        $securityContext = static::$container->get('security.context');
        $securityContext->setToken(null);
    }

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = array())
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

            return new static::$class(
                isset($options['environment']) ? $options['environment'] : 'test',
                isset($options['debug']) ? $options['debug'] : true
            );
        } else {
            // letting to use runtime-caching
            return parent::createKernel($options);
        }
    }
}
