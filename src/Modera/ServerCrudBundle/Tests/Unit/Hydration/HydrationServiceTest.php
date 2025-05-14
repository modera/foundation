<?php

namespace Modera\ServerCrudBundle\Tests\Unit\Hydration;

use Modera\ServerCrudBundle\Hydration\HydrationProfile;
use Modera\ServerCrudBundle\Hydration\HydrationService;
use Modera\ServerCrudBundle\Hydration\UnknownHydrationProfileException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Author
{
    public string $firstname = '';

    public string $lastname = '';
}

class Article
{
    public string $title = '';

    public string $body = '';

    public ?Author $author = null;

    /**
     * @var ArticleComment[]
     */
    public array $comments = [];
}

class ArticleComment
{
    public Author $author;

    public string $body;

    public \DateTime $createdAt;

    public function __construct(Author $author, string $body)
    {
        $this->author = $author;
        $this->body = $body;
        $this->createdAt = new \DateTime('now');
    }
}

class HydrationServiceTest extends \PHPUnit\Framework\TestCase
{
    private ContainerInterface $container;

    private HydrationService $service;

    private array $config;

    private Article $article;

    // override
    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);

        $this->service = new HydrationService($this->container);

        $this->config = [
            'groups' => [
                'tags' => function () {
                    return [];
                },
                'comments' => function (Article $e) {
                    $result = [];

                    foreach ($e->comments as $comment) {
                        $result[] = [
                            'body' => $comment->body,
                        ];
                    }

                    return $result;
                },
                'form' => [
                    'title', 'body',
                ],
                'author' => [
                    'firstname' => 'author.firstname',
                    'lastname' => 'author.lastname',
                ],
                'list' => function (Article $e) {
                    return [
                        'title' => substr($e->title, 0, 10),
                        'body' => substr($e->body, 0, 10),
                    ];
                },
                'show_stopper' => function () {
                    return new \stdClass();
                },
            ],
            'profiles' => [
                'list' => HydrationProfile::create(false)->useGroups(['list']),
                'form' => HydrationProfile::create()->useGroups(['form', 'comments', 'author']),
                'author',
                'preview' => HydrationProfile::create(false)->useGroups(['list', 'author']),
                'kaput' => HydrationProfile::create(false)->useGroups(['list', 'show_stopper']),
            ],
        ];

        $author = new Author();
        $author->firstname = 'Vassily';
        $author->lastname = 'Pupkin';

        $article = new Article();
        $article->author = $author;
        $article->title = 'Foo title';
        $article->body = 'Bar body';
        $article->comments = [
            new ArticleComment($author, 'Comment1'),
        ];

        $this->article = $article;
    }

    private function assertValidAuthorResult(array $result): void
    {
        $this->assertArrayHasKey('author', $result);
        $this->assertTrue(\is_array($result['author']));
        $this->assertArrayHasKey('firstname', $result['author']);
        $this->assertEquals($this->article->author->firstname, $result['author']['firstname']);
        $this->assertArrayHasKey('lastname', $result['author']);
        $this->assertEquals($this->article->author->lastname, $result['author']['lastname']);
    }

    private function assertValidFormResult(array $result): void
    {
        $this->assertArrayHasKey('form', $result);
        $this->assertTrue(\is_array($result['form']));
        $this->assertArrayHasKey('title', $result['form']);
        $this->assertEquals($this->article->title, $result['form']['title']);
        $this->assertArrayHasKey('body', $result['form']);
        $this->assertEquals($this->article->body, $result['form']['body']);
    }

    public function testHydrate(): void
    {
        $result = $this->service->hydrate($this->article, $this->config, 'form');

        $this->assertTrue(\is_array($result));

        $this->assertValidFormResult($result);

        $this->assertArrayHasKey('comments', $result);
        $this->assertTrue(\is_array($result['comments']));
        $this->assertEquals(1, \count($result['comments']));
        $this->assertArrayHasKey(0, $result['comments']);
        $this->assertTrue(\is_array($result['comments'][0]));
        $this->assertEquals($this->article->comments[0]->body, $result['comments'][0]['body']);

        $this->assertValidAuthorResult($result);
    }

    public function testHydrateWithGroup(): void
    {
        $result = $this->service->hydrate($this->article, $this->config, 'form', ['comments']);

        // when one group is specified then no grouping is used
        $this->assertTrue(\is_array($result));
        $this->assertEquals(1, count($result));
        $this->assertArrayHasKey(0, $result);
        $this->assertTrue(\is_array($result[0]));
        $this->assertArrayHasKey('body', $result[0]);
        $this->assertEquals($this->article->comments[0]->body, $result[0]['body']);

        $result = $this->service->hydrate($this->article, $this->config, 'form', ['form', 'author']);

        $this->assertTrue(\is_array($result));
        $this->assertEquals(2, \count($result));

        $this->assertArrayHasKey('form', $result);
        $this->assertValidFormResult($result);

        $this->assertArrayHasKey('author', $result);
        $this->assertValidAuthorResult($result);
    }

    public function testHydrateWithNoResultGroupingAllowed(): void
    {
        $result = $this->service->hydrate($this->article, $this->config, 'list');

        $this->assertTrue(\is_array($result));
        $this->assertEquals(2, \count($result));
        $this->assertArrayHasKey('title', $result);
        $this->assertEquals($this->article->title, $result['title']);
        $this->assertArrayHasKey('body', $result);
        $this->assertEquals($this->article->body, $result['body']);
    }

    public function testHydrateWithNoResultGroupingAllowedButGroupSpecified(): void
    {
        $result = $this->service->hydrate($this->article, $this->config, 'preview', ['list']);

        $expectedResult = [
            'title' => 'Foo title',
            'body' => 'Bar body',
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function testHydrateWithBadResult(): void
    {
        $thrownException = null;

        try {
            $this->service->hydrate($this->article, $this->config, 'kaput');
        } catch (\RuntimeException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('Invalid hydrator definition', $thrownException->getMessage());
    }

    public function testWhenUnknownHydrationProfileIsSpecified(): void
    {
        $thrownException = null;
        try {
            $this->service->hydrate($this->article, $this->config, 'blahblah');
        } catch (UnknownHydrationProfileException $e) {
            $thrownException = $e;
        }

        $this->assertNotNull($thrownException);
        $this->assertEquals('blahblah', $thrownException->getProfileName());
    }

    public function testHydrateWhenHydrationProfileSpecifiedInShortManner(): void
    {
        $result = $this->service->hydrate($this->article, $this->config, 'author');

        $this->assertTrue(\is_array($result));
        $this->assertArrayHasKey('firstname', $result);
        $this->assertArrayHasKey('lastname', $result);
    }
}
