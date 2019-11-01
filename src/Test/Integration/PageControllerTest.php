<?php declare(strict_types=1);

namespace SwagVueStorefront\Test\Integration;

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
use SwagVueStorefront\SwagVueStorefront;

class PageControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $browser;

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var string
     */
    private $categoryId;

    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var string
     */
    private $cmsPageId;

    /**
     * @var EntityRepositoryInterface
     */
    private $cmsPageRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->getSalesChannelBrowser();
        $this->salesChannelId = $this->getSalesChannelApiSalesChannelId();
        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');
        $this->categoryRepository = $this->getContainer()->get('category.repository');
        $this->cmsPageRepository = $this->getContainer()->get('cms_page.repository');
    }

    public function testResolveCategoryPage(): void
    {
        $this->createCmsPage();
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-nav/bar'
        ];

        $this->browser->request(
            'POST',
            '/sales-channel-api/v' . PlatformRequest::API_VERSION . SwagVueStorefront::ENDPOINT_PATH. '/page',
            $content
        );

        $response = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    public function testResolveCategoryWithoutCmsPage(): void
    {
        $this->createCategories();
        $this->createSeoUrls();

        $content = [
            'path' => '/foo-nav/bar'
        ];

        $this->browser->request(
            'POST',
            '/sales-channel-api/v' . PlatformRequest::API_VERSION . SwagVueStorefront::ENDPOINT_PATH. '/page',
            $content
        );

        $response = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent());

        static::assertObjectHasAttribute('cmsPage', $response);
        static::assertNull($response->cmsPage);

        static::assertEquals('frontend.navigation.page', $response->resourceType);
        static::assertObjectHasAttribute('resourceIdentifier', $response);
        static::assertNotNull($response->resourceIdentifier);
    }

    private function createProduct()
    {
        $id = Uuid::randomHex();
        $data = [
            'id' => $id,
            'productNumber' => Uuid::randomHex(),
            'stock' => 10,
            'active' => true,
            'name' => 'test',
            'price' => [['currencyId' => Defaults::CURRENCY, 'gross' => 99, 'net' => 99, 'linked' => false]],
            'manufacturer' => ['name' => 'test'],
            'tax' => ['name' => 'test', 'taxRate' => 99],
            'categories' => [
                ['id' => $id, 'name' => 'sampleCategory'],
            ],
            'visibilities' => [
                [
                    'salesChannelId' => $this->salesChannelId,
                    'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                ],
            ],
        ];

        $this->getContainer()->get('product.repository')->create([$data], Context::createDefaultContext());
    }

    private function createSeoUrls()
    {
        $this->seoUrlRepository->create([
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.navigation.page',
                'pathInfo' => '/navigation/1234',
                'seoPathInfo' => '/foo-nav/bar',
                'foreignKey' => $this->categoryId,
                'isValid' => true,
                'isCanonical' => false,
            ],
            [
                'salesChannelId' => $this->salesChannelId,
                'languageId' => Defaults::LANGUAGE_SYSTEM,
                'routeName' => 'frontend.product.page',
                'pathInfo' => '/detail/1234',
                'seoPathInfo' => '/foo-bar/prod',
                'foreignKey' => $this->categoryId,
                'isValid' => true,
                'isCanonical' => null,
            ],
        ], Context::createDefaultContext());
    }

    private function createCategories()
    {
        $resultEvent = $this->categoryRepository->create([
            [
                'salesChannelId' => $this->salesChannelId,
                'name' => 'My test category',
                'cmsPageId' => $this->cmsPageId
            ]
        ], Context::createDefaultContext());

        $this->categoryId = $resultEvent->getEventByEntityName('category')->getIds()[0];
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
