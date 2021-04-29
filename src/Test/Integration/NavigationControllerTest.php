<?php declare(strict_types=1);

namespace SwagShopwarePwa\Test\Integration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Test\TestDataCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use SwagShopwarePwa\SwagShopwarePwa;

class NavigationControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    const ENDPOINT_NAVIGATION = '/store-api/pwa/navigation';

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $browser;

    /**
     * @var TestDataCollection
     */
    private $ids;

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
        $this->ids = new TestDataCollection(Context::createDefaultContext());

        $this->browser = $this->createCustomSalesChannelBrowser([
            'id' => $this->ids->create('sales-channel'),
        ]);

        $this->salesChannelId = $this->ids->get('sales-channel');

        $this->categoryRepository = $this->getContainer()->get('category.repository');
        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');

        $this->ids->create('rootId');
        $this->ids->create('category1Id');
        $this->ids->create('category2Id');
        $this->ids->create('category1_1Id');
        $this->ids->create('category2_1Id');
    }

    public function testResolveOneLevel(): void
    {
        $this->createCategories();

        $content = [
            'rootNode' => $this->ids->get('rootId'),
            'depth' => 1
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent());

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
            'rootNode' => $this->ids->get('rootId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent());

        static::assertCount(2, $result->children);
        static::assertCount(1, $result->children[0]->children);
    }

    public function testResolveSeoUrl(): void
    {
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'depth' => 1,
            'rootNode' => $this->ids->get('rootId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_NAVIGATION,
            $content
        );

        $result = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent());

        static::assertEquals('/', $result->route->path);
        static::assertCount(2, $result->children);

        $childrenPaths = array_map(function($item) {
            return $item->route->path;
        }, $result->children);

        static::assertContains('Home-Shoes/Children/', $childrenPaths);
        static::assertContains('Home-Shoes/Children/', $childrenPaths);
    }

    private function createCategories():void
    {
        $this->categoryRepository->upsert([
            [
                'id' => $this->ids->get('rootId'),
                'name' => 'root',
                'children' => [
                    [
                        'id' => $this->ids->get('category1Id'),
                        'name' => 'Category 1',
                        'children' => [
                            [
                                'id' => $this->ids->get('category1_1Id'),
                                'name' => 'Category 1.1',
                            ],
                        ],
                    ],
                    [
                        'id' => $this->ids->get('category2Id'),
                        'name' => 'Category 2',
                        'children' => [
                            [
                                'id' => $this->ids->get('category2_1Id'),
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
                'salesChannelId' => $this->ids->get('sales-channel'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/123',
                'seoPathInfo' => 'Home-Shoes/Children/',
                'foreignKey' => $this->ids->get('category1Id'),
                'isValid' => true,
                'isCanonical' => false,
            ]
        ], Context::createDefaultContext());
    }

}
