<?php declare(strict_types=1);

namespace SwagShopwarePwa\Test\Integration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Cms\CmsPageCollection;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Content\LandingPage\LandingPageCollection;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Test\TestDataCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainCollection;
use Shopware\Core\System\SalesChannel\SalesChannelCollection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class PageControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    const ENDPOINT_PAGE = '/store-api/pwa/page';

    private KernelBrowser $browser;

    private TestDataCollection $ids;

    /**
     * @var EntityRepository<SeoUrlCollection>
     */
    private EntityRepository $seoUrlRepository;

    /**
     * @var EntityRepository<CategoryCollection>
     */
    private EntityRepository $categoryRepository;

    /**
     * @var EntityRepository<CmsPageCollection>
     */
    private EntityRepository $cmsPageRepository;

    /**
     * @var EntityRepository<SalesChannelDomainCollection>
     */
    private EntityRepository $salesChannelDomainRepository;

    /**
     * @var EntityRepository<SalesChannelCollection>
     */
    private EntityRepository $salesChannelRepository;

    /**
     * @var EntityRepository<LandingPageCollection>
     */
    private EntityRepository $landingPageRepository;

    public function setUp(): void
    {
        $this->ids = new TestDataCollection();

        $this->browser = $this->createCustomSalesChannelBrowser([
            'id' => $this->ids->create('salesChannelId'),
        ]);

        $this->ids->get('salesChannelId');

        $this->ids->create('categoryId');
        $this->ids->create('cmsPageId');
        $this->ids->create('cmsProductPageId');

        $this->ids->create('productActiveId');
        $this->ids->create('productActiveWithMainCategoriesId');
        $this->ids->create('productInActiveId');

        $this->ids->create('childCategoryId');
        $this->ids->create('child2CategoryId');
        $this->ids->create('child3CategoryId');

        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');
        $this->categoryRepository = $this->getContainer()->get('category.repository');
        $this->cmsPageRepository = $this->getContainer()->get('cms_page.repository');
        $this->salesChannelDomainRepository = $this->getContainer()->get('sales_channel_domain.repository');
        $this->salesChannelRepository = $this->getContainer()->get('sales_channel.repository');
        $this->landingPageRepository = $this->getContainer()->get('landing_page.repository');
    }

    /**
     * @group pwa-page-category
     */
    public function testResolveCategoryPageRootPath(): void
    {
        $this->createCmsPage();
        $this->createCategories();

        $content = [
            'path' => ''
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.navigation.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertEquals($this->ids->get('categoryId'), $response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-category
     */
    public function testResolveCategoryPage(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('breadcrumb', $response);
        static::assertArrayHasKey('category', $response);
        static::assertArrayHasKey('media', $response['category']);
        static::assertNotNull($response['category']['media']);
        static::assertEquals('frontend.navigation.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-category
     */
    public function testResolveCategoryBreadcrumbLink(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/Children-level-2/'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('breadcrumb', $response);
        static::assertEquals('/Home-Shoes/Children-canonical/', $response['breadcrumb'][$this->ids->get('childCategoryId')]['path']);
        static::assertEquals('/Home-Shoes/Children-level-2/', $response['breadcrumb'][$this->ids->get('child2CategoryId')]['path']);
    }

    /**
     * @group pwa-page-category
     */
    public function testResolveCategoryPageTechnicalUrl(): void
    {
        $this->createCmsPage();
        $this->createCategories();

        $content = [
            'path' => '/navigation/' . $this->ids->get('categoryId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('breadcrumb', $response);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.navigation.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-category
     */
    public function testResolveCategoryWithoutCmsPage(): void
    {
        $this->createCategories(false);
        $this->createSeoUrls();

        $content = [
            'path' => 'Home-Shoes/'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.navigation.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-category
     */
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

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertNotNull($response['cmsPage']);
        static::assertArrayHasKey('sections', $response['cmsPage']);
        static::assertArrayNotHasKey('blocks', $response['cmsPage']);
        static::assertArrayNotHasKey('breadcrumb', $response);
    }

    /**
     * @group pwa-page-landing
     */
    public function testResolveLandingPage(): void
    {
        $this->createCmsPage();
        $this->createLandingPage(true);
        $this->createSeoUrls();

        $content = [
            'path' => 'my-landing-page/exists'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('name', $response['cmsPage']);
        static::assertEquals('shopware AG', $response['cmsPage']['name']);
        static::assertEquals('frontend.landing.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-landing
     */
    public function testResolveLandingPageTechnicalUrl(): void
    {
        $this->createCmsPage();
        $this->createLandingPage(true);
        $this->createSeoUrls();

        $content = [
            'path' => 'landingPage/' . $this->ids->get('landingPageId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertArrayHasKey('canonicalPathInfo', $response);
        static::assertStringContainsString('my-landing-page', $response['canonicalPathInfo']);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.landing.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-landing
     */
    public function testResolveLandingPageWihoutCmsPage(): void
    {
        $this->createLandingPage(false);
        $this->createSeoUrls();

        $content = [
            'path' => 'my-landing-page/exists'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertNull($response['cmsPage']);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.landing.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductPage(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-bar/prod'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('product', $response);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.detail.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductPageWithAssociation(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();

        $content = [
            'path' => '/detail/' . $this->ids->get('productActiveId'),
            'associations' => [
                'manufacturer' => [],
                'categories' => []
            ]
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('product', $response);
        static::assertArrayHasKey('manufacturer', $response['product']);
        static::assertNotNull($response['product']['manufacturer']);
        static::assertArrayHasKey('categories', $response['product']);
        static::assertNotNull($response['product']['categories']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductPageTechnicalUrl(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();

        $content = [
            'path' => '/detail/' . $this->ids->get('productActiveId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('product', $response);
        static::assertArrayHasKey('resourceType', $response);
        static::assertEquals('frontend.detail.page', $response['resourceType']);
        static::assertArrayHasKey('resourceIdentifier', $response);
        static::assertNotNull($response['resourceIdentifier']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductPageForInactive(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-bar/prod-inactive'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(404, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('errors', $response);
        static::assertIsArray($response['errors']);
        static::assertArrayHasKey('status', $response['errors'][0]);
        static::assertEquals(404, $response['errors'][0]['status']);
        static::assertArrayHasKey('code', $response['errors'][0]);
        static::assertEquals('CONTENT__PRODUCT_NOT_FOUND', $response['errors'][0]['code']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductPageWithCmsPage(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createProduct(true);
        $this->createSeoUrls();

        $content = [
            'path' => '/detail/' . $this->ids->get('productActiveId'),
            'includes' => [
                'pwa_page_result' => ['cmsPage']
            ]
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('cmsPage', $response);
        static::assertNotNull($response['cmsPage']);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductHasBreadcrumbsLinks(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-bar/prod-has-breadcrumb'
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);
    }

    /**
     * @group pwa-page-product
     */
    public function testResolveProductHasNoBreadcrumbsLinks(): void
    {
        $this->createCategories(false);
        $this->createProduct();
        $this->createSalesChannelDomain();
        $this->createSeoUrls();

        $content = [
            'path' => '/detail/' . $this->ids->get('productActiveId')
        ];

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);
    }

    /**
     * @group pwa-page-routing
     */
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

        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE,
            $content
        );

        static::assertEquals(200, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('canonicalPathInfo', $response);
        static::assertEquals('/Home-Shoes/canonical/', $response['canonicalPathInfo']);
    }

    /**
     * @group pwa-page-routing
     */
    public function testResolveInvalidUrl(): void
    {
        $this->browser->request(
            'POST',
            self::ENDPOINT_PAGE
        );

        static::assertEquals(404, $this->browser->getResponse()->getStatusCode());
        $content = $this->browser->getResponse()->getContent();
        static::assertNotFalse($content);
        $response = \json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertArrayHasKey('errors', $response);
    }

    private function createSalesChannelDomain()
    {
        $this->salesChannelDomainRepository->create([
            [
                'url' => '/',
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB')
            ]
        ], Context::createDefaultContext());
    }

    private function createProduct(bool $withCmsPage = false)
    {
        $categoryId = Uuid::randomHex();

        $data = [
            [
                'id' => $this->ids->get('productActiveId'),
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
                        'salesChannelId' => $this->ids->get('salesChannelId'),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
                'cmsPageId' => $withCmsPage ? $this->ids->get('cmsProductPageId') : null
            ],
            [
                'id' => $this->ids->get('productActiveWithMainCategoriesId'),
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
                'mainCategories' => [[
                    'categoryId' => $this->ids->get('child3CategoryId'),
                    'id' => Uuid::randomHex(),
                    'salesChannelId' => $this->ids->get('salesChannelId'),
                ]],
                'visibilities' => [
                    [
                        'salesChannelId' => $this->ids->get('salesChannelId'),
                        'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                    ],
                ],
            ],
            [
                'id' => $this->ids->get('productInactiveId'),
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
                        'salesChannelId' => $this->ids->get('salesChannelId'),
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
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->ids->get('categoryId'),
                'seoPathInfo' => 'Home-Shoes/',
                'foreignKey' => $this->ids->get('categoryId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->ids->get('categoryId'),
                'seoPathInfo' => 'Home-Shoes/canonical/',
                'foreignKey' => $this->ids->get('categoryId'),
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->ids->get('childCategoryId'),
                'seoPathInfo' => 'Home-Shoes/Children/',
                'foreignKey' => $this->ids->get('childCategoryId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->ids->get('childCategoryId'),
                'seoPathInfo' => 'Home-Shoes/Children-canonical/',
                'foreignKey' => $this->ids->get('childCategoryId'),
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/' . $this->ids->get('child2CategoryId'),
                'seoPathInfo' => 'Home-Shoes/Children-level-2/',
                'foreignKey' => $this->ids->get('child2CategoryId'),
                'isValid' => true,
                'isCanonical' => true,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.detail.page',
                'pathInfo' => '/detail/' . $this->ids->get('productActiveId'),
                'seoPathInfo' => 'foo-bar/prod',
                'foreignKey' => $this->ids->get('productActiveId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.detail.page',
                'pathInfo' => '/detail/' . $this->ids->get('productInactiveId'),
                'seoPathInfo' => 'foo-bar/prod-inactive',
                'foreignKey' => $this->ids->get('productInactiveId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.detail.page',
                'pathInfo' => '/detail/' . $this->ids->get('productActiveWithMainCategoriesId'),
                'seoPathInfo' => 'foo-bar/prod-has-breadcrumb',
                'foreignKey' => $this->ids->get('productActiveWithMainCategoriesId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.landing.page',
                'pathInfo' => '/landingPage/' . $this->ids->get('landingPageId'),
                'seoPathInfo' => 'my-landing-page/exists',
                'foreignKey' => $this->ids->get('landingPageId'),
                'isValid' => true,
                'isCanonical' => false,
            ],
        ], Context::createDefaultContext());
    }

    private function createCategories(bool $withCmsPage = true)
    {
        $stream = [
            'id' => $this->ids->create('stream_id_1'),
            'name' => 'test',
            'filters' => [
                [
                    'type' => 'equals',
                    'field' => 'weight',
                    'value' => '999',
                    'parameters' => [
                        'operator' => 'eq',
                    ],
                ],
            ],
        ];

        $productStreamsRepository = $this->getContainer()->get('product_stream.repository');
        $productStreamsRepository->create([$stream], Context::createDefaultContext());

        $this->categoryRepository->create([
            [
                'id' => $this->ids->get('categoryId'),
                'salesChannelId' => $this->ids->get('salesChannelId'),
                'name' => 'My test category',
                'cmsPageId' => $withCmsPage ? $this->ids->get('cmsPageId') : null,
                'productStreamId' => $this->ids->get('stream_id_1'),
                'productAssignmentType' => CategoryDefinition::PRODUCT_ASSIGNMENT_TYPE_PRODUCT_STREAM,
                'media' => [
                    'id' => $this->ids->get('categoryMediaId')
                ],
                'children' => [
                    [
                        'id' => $this->ids->get('childCategoryId'),
                        'name' => 'Child category level 1',
                        'children' => [
                            [
                                'id' => $this->ids->get('child2CategoryId'),
                                'name' => 'Child category level 2',
                                'children' => [
                                    [
                                        'id' => $this->ids->get('child3CategoryId'),
                                        'name' => 'Child category level 3 (without seoUrl)'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], Context::createDefaultContext());

        $this->salesChannelRepository->upsert([
            [
                'id' => $this->ids->get('salesChannelId'),
                'navigationCategoryId' => $this->ids->get('categoryId')
            ]
        ], Context::createDefaultContext());
    }

    private function createLandingPage(bool $withCmsPage = true) {
        $this->landingPageRepository->create([
            [
                'id' => $this->ids->get('landingPageId'),
                'salesChannels' => [
                    [
                        'id' => $this->ids->get('salesChannelId')
                    ]
                ],
                'name' => 'My test landing page',
                'cmsPageId' => $withCmsPage ? $this->ids->get('cmsPageId') : null,
                'url' => 'my-landing-page/exists'
            ]
        ], Context::createDefaultContext());
    }

    private function createCmsPage()
    {
        $landingPage = [
            'id' => $this->ids->get('cmsPageId'),
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

        $productPage = [
            'id' => $this->ids->get('cmsProductPageId'),
            'name' => 'shopware AG Detail',
            'type' => 'product_detail',
            'sections' => [
                [
                    'id' => Uuid::randomHex(),
                    'type' => 'default',
                    'position' => 0,
                    'blocks' => [
                        [
                            'position' => 1,
                            'type' => 'product-heading',
                            'slots' => [
                                ['type' => 'product-name', 'slot' => 'left', 'config' => ['content' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'Lorem ipsum dolor']]]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->cmsPageRepository->upsert([$landingPage, $productPage], Context::createDefaultContext());
    }
}
