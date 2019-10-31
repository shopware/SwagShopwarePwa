<?php declare(strict_types=1);

namespace SwagVueStorefront\Test\Integration;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use SwagVueStorefront\SwagVueStorefront;

class ContextControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    private $browser;

    private $salesChannelId;

    public function setUp(): void
    {
        parent::setUp();

        $this->browser = $this->getSalesChannelBrowser();
        $this->salesChannelId = $this->getSalesChannelApiSalesChannelId();
    }

    public function testResolveProductPage(): void
    {
        $this->browser->request(
            'GET',
            '/sales-channel-api/v' . PlatformRequest::API_VERSION . SwagVueStorefront::ENDPOINT_PATH. '/context',
            []
        );

        $response = \GuzzleHttp\json_decode($this->browser->getResponse()->getContent(), false);

        static::assertObjectHasAttribute('token', $response);
        static::assertObjectHasAttribute('currentCustomerGroup', $response);
        static::assertObjectHasAttribute('salesChannel', $response);
        static::assertObjectHasAttribute('taxRules', $response);
        static::assertObjectHasAttribute('paymentMethod', $response);
        static::assertObjectHasAttribute('shippingMethod', $response);
        static::assertObjectHasAttribute('shippingLocation', $response);
        static::assertObjectHasAttribute('rulesIds', $response);
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
}
