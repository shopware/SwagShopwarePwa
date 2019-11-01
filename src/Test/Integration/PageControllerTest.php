<?php declare(strict_types=1);

namespace SwagVueStorefront\Test\Integration;

use PHPUnit\Framework\TestCase;
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

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->getSalesChannelBrowser();
        $this->salesChannelId = $this->getSalesChannelApiSalesChannelId();
        $this->seoUrlRepository = $this->getContainer()->get('seo_url.repository');
        $this->categoryRepository = $this->getContainer()->get('category.repository');
    }

    public function testResolveRootPage(): void
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

        $response = ($this->browser->getResponse()->getContent());

        static::assertObjectHasAttribute('resourceType', $response);
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
        $this->categoryId = $this->categoryRepository->create([
            [
                'salesChannelId' => $this->salesChannelId,
                'name' => 'My test category',
            ]
        ], Context::createDefaultContext())->getEventByEntityName('category')->getIds()[0];
    }
}
