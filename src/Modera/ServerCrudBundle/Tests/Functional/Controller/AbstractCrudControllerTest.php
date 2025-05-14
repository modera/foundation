<?php

namespace Modera\ServerCrudBundle\Tests\Functional\Controller;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Tools\SchemaTool;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Modera\ServerCrudBundle\Controller\AbstractCrudController;
use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Modera\ServerCrudBundle\Intercepting\InterceptorsManager;
use Modera\ServerCrudBundle\Service\ConfiguredServiceManager;
use Modera\ServerCrudBundle\Tests\Fixtures\Bundle\Contributions\ControllerActionInterceptorsProvider;
use Symfony\Component\Validator\Constraints as Assert;

class DummyException extends \RuntimeException
{
}

#[ORM\Entity]
#[ORM\Table(name: '_testing_article')]
#[ORM\HasLifecycleCallbacks]
class DummyArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string')]
    public string $title = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text')]
    public string $body = '';

    public static bool $suicideEngaged = false;

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    #[ORM\PreRemove]
    public function suicide(): void
    {
        if (self::$suicideEngaged) {
            self::$suicideEngaged = false;

            throw new DummyException('boom');
        }

        self::$suicideEngaged = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public static function formatNewValues(array $params, array $config, $container): array
    {
        return [
            'params' => $params,
            'config' => $config,
            'container' => $container,
        ];
    }
}

class DataController extends AbstractCrudController
{
    public function getConfig(): array
    {
        return [
            'entity' => DummyArticle::class,
            'hydration' => [
                'groups' => [
                    'form' => [
                        'id', 'title', 'body',
                    ],
                    'list' => function (DummyArticle $e) {
                        if (DummyArticle::$suicideEngaged) {
                            $e->suicide();
                        }

                        return [
                            'id' => $e->getId(),
                            'title' => \substr($e->title, 0, 10),
                            'body' => \substr($e->body, 0, 10),
                        ];
                    },
                    'suicide' => function () {
                        throw new DummyException('suicide');
                    },
                ],
                'profiles' => [
                    'new_record' => HydrationProfile::create()->useGroups(['form']),
                    'get_record' => HydrationProfile::create()->useGroups(['form']),
                    'list' => HydrationProfile::create(false)->useGroups(['list']),
                    'rotten_profile' => HydrationProfile::create()->useGroups(['suicide']),
                ],
            ],
        ];
    }
}

class AbstractCrudControllerTest extends FunctionalTestCase
{
    private static SchemaTool $st;

    private DataController $controller;

    // override
    public function doSetUp(): void
    {
        $this->controller = new DataController();
        $this->controller->setInterceptorsManager(self::getContainer()->get(InterceptorsManager::class));
        $this->controller->setConfiguredServiceManager(self::getContainer()->get(ConfiguredServiceManager::class));
        $this->controller->setContainer(self::getContainer());

        DummyArticle::$suicideEngaged = false;
    }

    // override
    public static function doSetUpBeforeClass(): void
    {
        self::$st = new SchemaTool(self::$em);
        self::$st->createSchema([
            self::$em->getClassMetadata(DummyArticle::class),
        ]);
    }

    // override
    public static function doTearDownAfterClass(): void
    {
        self::$st->dropSchema([
            self::$em->getClassMetadata(DummyArticle::class),
        ]);
    }

    private function getDummyInterceptor(): ControllerActionInterceptorsProvider
    {
        return self::getContainer()->get('modera_server_crud_dummy_bundle.contributions.controller_action_interceptors_provider');
    }

    private function assertValidInterceptorInvocation($requestParams, $type): void
    {
        $invocation = $this->getDummyInterceptor()->interceptor->invocations[$type];

        $this->assertEquals(
            1,
            \count($invocation),
            \sprintf('It is expected that interceptor for "%s" would be invoked only once!', $type)
        );
        $this->assertSame($requestParams, $invocation[0][0]);
        $this->assertSame($this->controller, $invocation[0][1]);
    }

    public function testCreateAction(): void
    {
        $requestParams = [
            'record' => [
                'body' => 'Some text goes here',
            ],
        ];

        // validation for "title" field should fail
        $result = $this->controller->createAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'create');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('field_errors', $result);
        $this->assertTrue(\is_array($result['field_errors']));
        $this->assertEquals(1, \count($result['field_errors']));
        $this->assertArrayHasKey('title', $result['field_errors']);
        $this->assertTrue(\is_array($result['field_errors']['title']));
        $this->assertEquals(1, \count($result['field_errors']['title']));

        // validation should pass and record should be saved

        $result = $this->controller->createAction([
            'hydration' => [
                'profile' => 'new_record',
            ],
            'record' => [
                'title' => 'Some title',
                'body' => 'Some text goes here',
            ],
        ]);

        $this->assertTrue(\is_array($result));

        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('created_models', $result);
        $this->assertTrue(\is_array($result['created_models']));
        $this->assertEquals(1, \count($result['created_models']));
        $this->assertFalse(isset($result['updated_models']));
        $this->assertFalse(isset($result['removed_models']));

        $this->assertArrayHasKey('result', $result);
        $this->assertTrue(\is_array($result['result']));
        $this->assertArrayHasKey('form', $result['result']);
        $this->assertTrue(\is_array($result['result']['form']));
        $this->assertArrayHasKey('id', $result['result']['form']);
        $form = $result['result']['form'];
        $this->assertNotNull($form['id']);
        $this->assertArrayHasKey('title', $form);
        $this->assertEquals('Some title', $form['title']);
        $this->assertArrayHasKey('body', $form);
        $this->assertEquals('Some text goes here', $form['body']);

        /** @var DummyArticle $article */
        $article = self::$em->getRepository(DummyArticle::class)->find($form['id']);
        $this->assertInstanceOf(DummyArticle::class, $article);
        $this->assertEquals('Some title', $article->title);
        $this->assertEquals('Some text goes here', $article->body);
    }

    public function testCreateActionWithException(): void
    {
        $this->expectException(DummyException::class);

        DummyArticle::$suicideEngaged = true;

        $result = $this->controller->createAction([
            'record' => [
                'title' => 'opa',
                'body' => 'hola',
            ],
        ]);
    }

    /**
     * @return DummyArticle[]
     */
    private function loadDummyData(): array
    {
        $result = [];

        for ($i = 0; $i < 5; ++$i) {
            $article = new DummyArticle();
            $article->title = \str_repeat('t', 15);
            $article->body = \str_repeat('b', 15);

            $result[] = $article;

            self::$em->persist($article);
        }
        self::$em->flush();

        return $result;
    }

    public function testListAction(): void
    {
        $this->assertEquals(0, \count(self::$em->getRepository(DummyArticle::class)->findAll()));

        $this->loadDummyData();

        $requestParams = [
            'limit' => 3,
            'sort' => [
                ['property' => 'id', 'direction' => 'DESC'],
            ],
            'filter' => [
                [
                    'property' => 'id',
                    'value' => 'notIn:6',
                ],
            ],
            'hydration' => [
                'profile' => 'list',
            ],
        ];

        $result = $this->controller->listAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'list');

        $assertValidItem = function ($items, $index) {
            $this->assertArrayHasKey($index, $items);

            $item = $items[$index];

            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('body', $item);
        };

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('items', $result);
        $this->assertTrue(\is_array($result['items']));
        $this->assertEquals(3, \count($result['items']));
        $assertValidItem($result['items'], 0);
        $assertValidItem($result['items'], 1);
        $assertValidItem($result['items'], 2);
    }

    public function testListActionWithException(): void
    {
        $this->loadDummyData();

        $this->expectException(DummyException::class);

        DummyArticle::$suicideEngaged = true;

        $result = $this->controller->listAction([
            'hydration' => [
                'profile' => 'list',
            ],
        ]);
    }

    public function testRemoveAction(): void
    {
        $articles = $this->loadDummyData();

        $ids = [
            $articles[0]->getId(),
            $articles[1]->getId(),
        ];

        $requestParams = [
            'filter' => [
                [
                    'property' => 'id',
                    'value' => 'in:'.\implode(', ', $ids),
                ],
            ],
        ];

        $result = $this->controller->removeAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'remove');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('removed_models', $result);
        $this->assertTrue(\is_array($result['removed_models']));
        $this->assertEquals(1, \count($result['removed_models']));

        $removedModels = $result['removed_models'];
        $modelName = \key($removedModels);

        $this->assertTrue(\is_array($removedModels[$modelName]));
        $this->assertEquals(2, \count($removedModels[$modelName]));
        $this->assertTrue(\in_array($ids[0], $removedModels[$modelName]));
        $this->assertTrue(\in_array($ids[1], $removedModels[$modelName]));

        $this->assertNull(self::$em->getRepository(DummyArticle::class)->find($ids[0]));
        $this->assertNull(self::$em->getRepository(DummyArticle::class)->find($ids[1]));
    }

    public function testRemoveActionWithException(): void
    {
        $articles = $this->loadDummyData();

        $ids = [
            $articles[0]->getId(),
            $articles[1]->getId(),
        ];

        $this->expectException(DummyException::class);

        DummyArticle::$suicideEngaged = true;

        $result = $this->controller->removeAction([
            'filter' => [
                [
                    'property' => 'id',
                    'value' => 'in:'.\implode(', ', $ids),
                ],
            ],
        ]);
    }

    public function testGetAction(): void
    {
        $articles = $this->loadDummyData();

        $requestParams = [
            'hydration' => [
                'profile' => 'get_record',
            ],
            'filter' => [
                [
                    'property' => 'id',
                    'value' => 'eq:'.$articles[0]->getId(),
                ],
            ],
        ];

        $result = $this->controller->getAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'get');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('result', $result);
        $this->assertArrayHasKey('form', $result['result']);
        $form = $result['result']['form'];
        $this->assertTrue(\is_array($form));
        $this->assertArrayHasKey('id', $form);
        $this->assertEquals($articles[0]->getId(), $form['id']);
        $this->assertArrayHasKey('title', $form);
        $this->assertArrayHasKey('body', $form);
    }

    public function testGetActionWithException(): void
    {
        $articles = $this->loadDummyData();

        $this->expectException(DummyException::class);

        DummyArticle::$suicideEngaged = true;

        $requestParams = [
            'hydration' => [
                'profile' => 'rotten_profile',
            ],
            'filter' => [
                [
                    'property' => 'id',
                    'value' => 'eq:'.$articles[0]->getId(),
                ],
            ],
        ];
        $result = $this->controller->getAction($requestParams);
    }

    public function testUpdateAction()
    {
        $article = new DummyArticle();
        $article->body = 'the body, yo';
        $article->title = 'title, yo';

        self::$em->persist($article);
        self::$em->flush();

        $requestParams = [
            'record' => [
                'id' => $article->id,
                'title' => '',
            ],
        ];
        $result = $this->controller->updateAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'update');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('field_errors', $result);
        $this->assertArrayHasKey('title', $result['field_errors']);
        $this->assertTrue(\is_array($result['field_errors']));
        $this->assertEquals(1, \count($result['field_errors']));
        $this->assertArrayHasKey('title', $result['field_errors']);
        $this->assertTrue(\is_array($result['field_errors']['title']));
        $this->assertEquals(1, \count($result['field_errors']['title']));

        // ---

        self::$em->clear();

        /** @var DummyArticle $fetchedArticle */
        $fetchedArticle = self::$em->getRepository(DummyArticle::class)->find($article->id);

        $this->assertEquals('title, yo', $fetchedArticle->title);
        $this->assertEquals($article->body, $fetchedArticle->body);

        $result = $this->controller->updateAction([
            'hydration' => [
                'profile' => 'get_record',
            ],
            'record' => [
                'id' => $fetchedArticle->id,
                'title' => 'new title',
                'body' => 'new body',
            ],
        ]);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('updated_models', $result);
        $this->assertTrue(\is_array($result['updated_models']));
        $this->assertEquals(1, \count($result['updated_models']));
        $this->assertArrayHasKey('result', $result);
        $this->assertTrue(\is_array($result['result']));
        $this->assertArrayHasKey('form', $result['result']);
        $this->assertTrue(\is_array($result['result']['form']));

        self::$em->clear();

        /** @var DummyArticle $updatedArticle */
        $updatedArticle = self::$em->getRepository(DummyArticle::class)->find($article->id);

        $this->assertNotNull($updatedArticle);
        $this->assertEquals('new title', $updatedArticle->title);
        $this->assertEquals('new body', $updatedArticle->body);
    }

    private function createDummyArticles($total): array
    {
        /** @var DummyArticle[] $entities */
        $entities = [];
        for ($i = 0; $i < $total; ++$i) {
            $article = new DummyArticle();
            $article->body = 'body'.$i;
            $article->title = 'title'.$i;

            $entities[] = $article;

            self::$em->persist($article);
        }
        self::$em->flush();

        return $entities;
    }

    public function testBatchUpdateActionWithRecords(): void
    {
        $entities = $this->createDummyArticles(2);

        $requestParams = [
            'records' => [
                [
                    'id' => $entities[0]->id,
                    'body' => 'body0_foo',
                    'title' => 'title0_foo',
                ],
                [
                    'id' => $entities[1]->id,
                    'body' => 'body1_foo',
                    'title' => 'title1_foo',
                ],
            ],
        ];
        $result = $this->controller->batchUpdateAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'batchUpdate');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('updated_models', $result);
        $this->assertEquals(1, \count($result['updated_models']));
        $updatedModels = \array_values($result['updated_models']);
        $this->assertEquals(2, \count($updatedModels[0]));
        $this->assertTrue(\in_array($entities[0]->id, $updatedModels[0]));
        $this->assertTrue(\in_array($entities[1]->id, $updatedModels[0]));

        self::$em->clear();

        $article1 = self::$em->find(DummyArticle::class, $entities[0]->id);
        $this->assertEquals('body0_foo', $article1->body);
        $this->assertEquals('title0_foo', $article1->title);

        $article2 = self::$em->find(DummyArticle::class, $entities[1]->id);
        $this->assertEquals('body1_foo', $article2->body);
        $this->assertEquals('title1_foo', $article2->title);
    }

    public function testBatchUpdateActionWithRecordsErrorHandling(): void
    {
        $entities = $this->createDummyArticles(2);

        $result = $this->controller->batchUpdateAction([
            'records' => [
                [
                    'id' => $entities[0]->id,
                    'body' => 'body0_foo',
                    'title' => '',
                ],
                [
                    'id' => $entities[1]->id,
                    'body' => 'body1_foo',
                    'title' => 'title1_foo',
                ],
            ],
        ]);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);

        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue(\is_array($result['errors']));
        $this->assertEquals(1, \count($result['errors']));
        $error = $result['errors'][0];

        $this->assertArrayHasKey('id', $error);
        $this->assertArrayHasKey('id', $error['id']);
        $this->assertEquals($entities[0]->id, $error['id']['id']);

        $this->assertArrayHasKey('errors', $error);

        self::$em->clear();

        // none of them must have been updated
        $article1 = self::$em->find(DummyArticle::class, $entities[0]->id);
        $this->assertEquals('body0', $article1->body);
        $this->assertEquals('title0', $article1->title);

        $article2 = self::$em->find(DummyArticle::class, $entities[1]->id);
        $this->assertEquals('body1', $article2->body);
        $this->assertEquals('title1', $article2->title);
    }

    public function testBatchUpdateActionWithQueriesAndRecord(): void
    {
        $entities = $this->createDummyArticles(3);

        $requestParams = [
            'queries' => [
                [
                    'filter' => [
                        [
                            'property' => 'id',
                            'value' => 'eq:'.$entities[0]->id,
                        ],
                    ],
                ],
                [
                    'filter' => [
                        [
                            'property' => 'title',
                            'value' => 'eq:'.$entities[2]->title,
                        ],
                    ],
                ],
            ],
            'record' => [
                'title' => 'hello',
            ],
        ];
        $result = $this->controller->batchUpdateAction($requestParams);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertTrue($result['success']);

        $this->assertArrayHasKey('updated_models', $result);
        $this->assertEquals(1, \count($result['updated_models']));
        $updatedModels = \array_values($result['updated_models']);
        $this->assertTrue(\is_array($updatedModels));
        $this->assertEquals(1, \count($updatedModels));
        $this->assertEquals(2, \count($updatedModels[0]));
        $this->assertTrue(\in_array($entities[0]->id, $updatedModels[0]));
        $this->assertTrue(\in_array($entities[2]->id, $updatedModels[0]));

        self::$em->clear();

        $article1 = self::$em->find(DummyArticle::class, $entities[0]->id);
        $this->assertEquals('hello', $article1->title);
        $this->assertEquals($entities[0]->body, $article1->body);

        $article3 = self::$em->find(DummyArticle::class, $entities[2]->id);
        $this->assertEquals('hello', $article3->title);
        $this->assertEquals($entities[2]->body, $article3->body);

        // should not have been updated
        $article2 = self::$em->find(DummyArticle::class, $entities[1]->id);
        $this->assertEquals('title1', $article2->title);
        $this->assertEquals($entities[1]->body, $article2->body);
    }

    public function testBatchUpdateActionWithQueriesAndRecordErrorHandling(): void
    {
        $entities = $this->createDummyArticles(3);

        $result = $this->controller->batchUpdateAction([
            'queries' => [
                [
                    'filter' => [
                        [
                            'property' => 'id',
                            'value' => 'eq:'.$entities[0]->id,
                        ],
                    ],
                ],
                [
                    'filter' => [
                        [
                            'property' => 'title',
                            'value' => 'eq:'.$entities[2]->title,
                        ],
                    ],
                ],
            ],
            'record' => [
                'title' => '',
            ],
        ]);

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue(\is_array($result['errors']));
        $this->assertEquals(2, \count($result['errors']));

        $errors = $result['errors'];

        $this->assertArrayHasKey('id', $errors[0]);
        $this->assertTrue(\is_array($errors[0]));
        $this->assertArrayHasKey('id', $errors[0]['id']);
        $this->assertEquals($entities[0]->id, $errors[0]['id']['id']);

        $this->assertArrayHasKey('id', $errors[1]);
        $this->assertTrue(\is_array($errors[1]));
        $this->assertArrayHasKey('id', $errors[1]['id']);
        $this->assertEquals($entities[2]->id, $errors[1]['id']['id']);
    }

    // this test will result in having EM closed
    public function testUpdateActionWithException(): void
    {
        $articles = $this->loadDummyData();

        $this->expectException(DummyException::class);

        DummyArticle::$suicideEngaged = true;

        $result = $this->controller->updateAction([
            'record' => [
                'id' => $articles[0]->id,
                'title' => 'yo',
                'body' => 'ogo',
            ],
        ]);
    }

    public function testGetNewRecordValuesAction(): void
    {
        $requestParams = ['params'];

        $output = $this->controller->getNewRecordValuesAction($requestParams);

        $this->assertValidInterceptorInvocation($requestParams, 'getNewRecordValues');

        $this->assertTrue(\is_array($output));
        $this->assertArrayHasKey('params', $output);
        $this->assertSame($requestParams, $output['params']);
        $this->assertArrayHasKey('config', $output);
        $this->assertTrue(\is_array($output['config']));
        // we can't do just values comparison here because it goes to some kind of recursion
        $this->assertSame(\array_keys($this->controller->getPreparedConfig()), \array_keys($output['config']));
        $this->assertArrayHasKey('container', $output);
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $output['container']);
    }
}
