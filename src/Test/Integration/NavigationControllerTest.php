<?php declare(strict_types=1);

namespace SwagShopwarePwa\Test\Integration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use SwagShopwarePwa\SwagShopwarePwa;

class NavigationControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    const ENDPOINT_NAVIGATION = '/sales-channel-api/v' . PlatformRequest::API_VERSION . '/vsf/navigation';

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var string
     */
    private $rootId;

    /**
     * @var string
     */
    private $category1Id;

    /**
     * @var string
     */
    private $category2Id;

    /**
     * @var string
     */
    private $category1_1Id;

    /**
     * @var string
     */
    private $category2_1Id;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->getSalesChannelBrowser();
        $this->salesChannelId = $this->getSalesChannelApiSalesChannelId();

        $this->categoryRepository = $this->getContainer()->get('category.repository');
        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');

        $this->rootId = Uuid::randomHex();
        $this->category1Id = Uuid::randomHex();
        $this->category2Id = Uuid::randomHex();
        $this->category1_1Id = Uuid::randomHex();
        $this->category2_1Id = Uuid::randomHex();
    }

    public function testResolveOneLevel(): void
    {
        $this->createCategories();

        $content = [
            'rootNode' => $this->rootId,
            'depth' => 1
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('route', $result);
        static::assertObjectHasAttribute('resourceType', $result->route);

        static::assertObjectHasAttribute('count', $result);
        static::assertEquals(2, $result->count);

        static::assertObjectHasAttribute('children', $result);
        static::assertNull($result->children[0]->children);
    }

    public function testResolveFull(): void
    {
        $this->createCategories();

        $content = [
            'depth' => -1,
            'rootNode' => $this->rootId
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertCount(2, $result->children);
        static::assertCount(1, $result->children[0]->children);
    }

    /**
     * Needs fix, because order of children is not deterministic and fails sometimes
     */
    public function testResolveSeoUrl(): void
    {
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'depth' => 1,
            'rootNode' => $this->rootId
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertEquals('/', $result->route->path);
        static::assertEquals('Home-Shoes/Children/', $result->children[0]->route->path);
    }

    private function createCategories():void
    {
        $this->categoryRepository->upsert([
            [
                'id' => $this->rootId,
                'name' => 'root',
                'children' => [
                    [
                        'id' => $this->category1Id,
                        'name' => 'Category 1',
                        'children' => [
                            [
                                'id' => $this->category1_1Id,
                                'name' => 'Category 1.1',
                            ],
                        ],
                    ],
                    [
                        'id' => $this->category2Id,
                        'name' => 'Category 2',
                        'children' => [
                            [
                                'id' => $this->category2_1Id,
                                'name' => 'Category 2.1',
                            ],
                        ],
                    ],
                ],
            ],
        ], Context::createDefaultContext());
    }

    private function createSeoUrls(): void
    {
        $this->seoUrlRepository->create([
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/123',
                'seoPathInfo' => 'Home-Shoes/Children/',
                'foreignKey' => $this->category1Id,
                'isValid' => true,
                'isCanonical' => false,
            ]
        ], Context::createDefaultContext());
    }

}
