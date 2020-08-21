<?php declare(strict_types=1);

namespace SwagShopwarePwa\Test\Integration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;

class PageControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    const ENDPOINT_PAGE = '/store-api/v' . PlatformRequest::API_VERSION . '/pwa/page';

    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $cmsPageRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelDomainRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var string
     */
    private $categoryId;

    /**
     * @var string
     */
    private $cmsPageId;

    /**
     * @var string
     */
    private $productActiveId;

    /**
     * @var string
     */
    private $productInactiveId;

    /**
     * @var string
     */
    private $childCategoryId = '4cb685159a9748289cbd6a36f6b33acb';

    /**
     * @var string
     */
    private $child2CategoryId = '4cb685159a9748289cbd6aacbdef3acb';

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->getSalesChannelBrowser();
        $this->salesChannelId = $this->getSalesChannelApiSalesChannelId();

        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');
        $this->categoryRepository = $this->getContainer()->get('category.repository');
        $this->cmsPageRepository = $this->getContainer()->get('cms_page.repository');
        $this->salesChannelDomainRepository = $this->getContainer()->get('sales_channel_domain.repository');
        $this->salesChannelRepository = $this->getContainer()->get('sales_channel.repository');
    }

    public function testResolveCategoryPageRootPath(): void
    {
        $this->createCmsPage();
        $this->createCategories();

        $content = [
            'path' => ''
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertEquals($this->categoryId, $response->resourceIdentifier);
    }

    public function testResolveCategoryPage(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/'
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());


        static::assertObjectHasAttribute('cmsPage', $response);
        static::assertObjectHasAttribute('breadcrumb', $response);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testResolveCategoryBreadcrumbLink(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/Children-level-2/'
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent(), true);

        static::assertArrayHasKey('breadcrumb', $response);

        static::assertEquals('/Home-Shoes/Children-canonical/', $response['breadcrumb'][$this->childCategoryId]['path']);
        static::assertEquals('/Home-Shoes/Children-level-2/', $response['breadcrumb'][$this->child2CategoryId]['path']);
    }

    public function testResolveCategoryPageTechnicalUrl(): void
    {
        $this->createCmsPage();
        $this->createCategories();

        $content = [
            'path' => '/navigation/' . $this->categoryId
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);
        static::assertObjectHasAttribute('breadcrumb', $response);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testResolveCategoryWithoutCmsPage(): void
    {
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/'
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);
        static::assertNull($response->cmsPage);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testProductPage(): void
    {
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-bar/prod'
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('product', $response);

        static::assertEquals('frontend.detail.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testProductPageWithAssociation(): void
    {
        $this->createProduct();
        $this->createSalesChannelDomain();

        $content = [
            'path' => '/detail/' . $this->productActiveId,
            'associations' => [
                'manufacturer' => [],
                'categories' => []
            ]
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent(), true);

        static::assertArrayHasKey('product', $response);
        static::assertArrayHasKey('manufacturer', $response['product']);
        static::assertNotNull($response['product']['manufacturer']);
        static::assertArrayHasKey('categories', $response['product']);
        static::assertNotNull($response['product']['categories']);
    }

    public function testProductPageTechnicalUrl(): void
    {
        $this->createProduct();
        $this->createSalesChannelDomain();

        $content = [
            'path' => '/detail/' . $this->productActiveId
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('product', $response);

        static::assertEquals('frontend.detail.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testProductPageForInactive(): void
    {
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-bar/prod-inactive'
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('errors', $response);
        static::assertIsArray($response->errors);

        static::assertEquals(404, $response->errors[0]->status);
        static::assertEquals('CONTENT__PRODUCT_NOT_FOUND', $response->errors[0]->code);

    }

    public function testResolveCategoryPageWithIncludes(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/',
            'includes' => [
                'pwa_page_result' => ['cmsPage'],
                'section' => ['id']
            ]
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);
        static::assertNotNull($response->cmsPage);
        static::assertObjectHasAttribute('sections', $response->cmsPage);
        static::assertObjectNotHasAttribute('blocks', $response->cmsPage);
        static::assertObjectNotHasAttribute('breadcrumb', $response);
    }

    public function testResolveCanonicalUrl(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/',
            'includes' => [
                'pwa_page_result' => ['canonicalPathInfo']
            ]
        ];

        $this->salesChannelApiBrowser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        $response = json_decode($this->salesChannelApiBrowser->getResponse()->getContent());

        static::assertObjectHasAttribute('canonicalPathInfo', $response);
        static::assertEquals('/Home-Shoes/canonical/', $response->canonicalPathInfo);
    }

    private function createSalesChannelDomain()
    {
        $this->salesChannelDomainRepository->create([
            [
                'url' => '/',
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB')
            ]
        ], Context::createDefaultContext());
    }

    private function createProduct()
    {
        $this->productActiveId = Uuid::randomHex();
        $this->productInactiveId = Uuid::randomHex();
        $categoryId = Uuid::randomHex();
        $data = [
            [
                'id' => $this->productActiveId,
                'productNumber' => Uuid::randomHex(),
                'stock' => 10,
                'active' => true,
                'name' => 'test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 99, 'net' => 99, 'linked' => false]],
                'manufacturer' => ['name' => 'test'],
                'tax' => ['name' => 'test', 'taxRate' => 99],
                'categories' => [
                    ['id' => $categoryId, 'name' => 'sampleCategory'],
                ],
                'visibilities' => [
                    [
                        'salesChannelId' => $this->salesChannelId,
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ],
            [
                'id' => $this->productInactiveId,
                'productNumber' => Uuid::randomHex(),
                'stock' => 10,
                'active' => false,
                'name' => 'test',
                'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 99, 'net' => 99, 'linked' => false]],
                'manufacturer' => ['name' => 'test'],
                'tax' => ['name' => 'test', 'taxRate' => 99],
                'categories' => [
                    ['id' => $categoryId, 'name' => 'sampleCategory'],
                ],
                'visibilities' => [
                    [
                        'salesChannelId' => $this->salesChannelId,
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ]
            ]
        ];

        $this->getContainer()->get('product.repository')->create($data, Context::createDefaultContext());
    }

    private function createSeoUrls()
    {
        $this->seoUrlRepository->create([
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->categoryId,
                'seoPathInfo' => 'Home-Shoes/',
                'foreignKey' => $this->categoryId,
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->categoryId,
                'seoPathInfo' => 'Home-Shoes/canonical/',
                'foreignKey' => $this->categoryId,
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->childCategoryId,
                'seoPathInfo' => 'Home-Shoes/Children/',
                'foreignKey' => $this->childCategoryId,
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->childCategoryId,
                'seoPathInfo' => 'Home-Shoes/Children-canonical/',
                'foreignKey' => $this->childCategoryId,
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->child2CategoryId,
                'seoPathInfo' => 'Home-Shoes/Children-level-2/',
                'foreignKey' => $this->child2CategoryId,
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.detail.page',
                'pathInfo' => '/detail/' . $this->productActiveId,
                'seoPathInfo' => 'foo-bar/prod',
                'foreignKey' => $this->productActiveId,
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.detail.page',
                'pathInfo' => '/detail/' . $this->productInactiveId,
                'seoPathInfo' => 'foo-bar/prod-inactive',
                'foreignKey' => $this->productInactiveId,
                'isValid' => true,
                'isCanonical' => false,
            ],
        ], Context::createDefaultContext());
    }

    private function createCategories()
    {
        $this->categoryId = UUid::randomHex();

        $resultEvent = $this->categoryRepository->create([
            [
                'id' => $this->categoryId,
                'salesChannelId' => $this->salesChannelId,
                'name' => 'My test category',
                'cmsPageId' => $this->cmsPageId,
                'children' => [
                    [
                        'id' => $this->childCategoryId,
                        'name' => 'Child category level 1',
                        'children' => [
                            [
                                'id' => $this->child2CategoryId,
                                'name' => 'Child category level 2'
                            ]
                        ]
                    ]
                ]
            ]
        ], Context::createDefaultContext());

        $this->salesChannelRepository->upsert([
            [
                'id' => $this->salesChannelId,
                'navigationCategoryId' => $this->categoryId
            ]
        ], Context::createDefaultContext());
    }

    private function createCmsPage()
    {
        $page = [
            'id' => Uuid::randomHex(),
            'name' => 'shopware AG',
            'type' => 'landing_page',
            'sections' => [
                [
                    'id' => Uuid::randomHex(),
                    'type' => 'default',
                    'position' => 0,
                    'blocks' => [
                        [
                            'position' => 1,
                            'type' => 'image-text',
                            'slots' => [
                                ['type' => 'text', 'slot' => 'left', 'config' => ['content' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'Lorem ipsum dolor']]],
                                ['type' => 'image', 'slot' => 'right', 'config' => ['url' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'http://shopware.com/image.jpg']]],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $resultEvent = $this->cmsPageRepository->create([$page], Context::createDefaultContext());

        $this->cmsPageId = $resultEvent->getEventByEntityName('cms_page')->getIds()[0];
    }
}
