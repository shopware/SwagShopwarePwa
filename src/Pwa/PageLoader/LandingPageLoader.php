<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader;

use Shopware\Core\Content\LandingPage\SalesChannel\AbstractLandingPageRoute;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\Landing\LandingPageResult;
use SwagShopwarePwa\Pwa\PageResult\Landing\LandingPageResultHydrator;

/**
 * This class loads a static landing page. Landing pages behave the same way as CMS pages, but do not have a breadcrumb.
 *
 * @package SwagShopwarePwa\Pwa\PageLoader
 */
class LandingPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.landing.page';

    public function __construct(
        private readonly AbstractLandingPageRoute $landingPageRoute,
        private readonly LandingPageResultHydrator $resultHydrator
    ) {
    }

    public function getResourceType(): string
    {
        return self::RESOURCE_TYPE;
    }

    public function load(PageLoaderContext $pageLoaderContext): LandingPageResult
    {
        $landingPageResult = $this->landingPageRoute->load(
            $pageLoaderContext->getResourceIdentifier(),
            $pageLoaderContext->getRequest(),
            $pageLoaderContext->getContext()
        );

        return $this->resultHydrator->hydrate(
            $pageLoaderContext,
            $landingPageResult->getLandingPage()->getCmsPage() ?? null
        );
    }
}
