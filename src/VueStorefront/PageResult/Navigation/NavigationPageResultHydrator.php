<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Storefront\Framework\Routing\Router;
use SwagVueStorefront\VueStorefront\Controller\PageController;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;

class NavigationPageResultHydrator
{
    /**
     * @var NavigationPageResult
     */
    private $pageResult;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->pageResult = new NavigationPageResult();
    }

    public function hydrate(PageLoaderContext $pageLoaderContext, CategoryEntity $category, ?CmsPageEntity $cmsPageEntity): NavigationPageResult
    {
        $this->pageResult->setCmsPage($cmsPageEntity);

        $this->setBreadcrumbs($category, $pageLoaderContext->getContext()->getSalesChannel()->getNavigationCategoryId());

        $this->pageResult->setResourceType($pageLoaderContext->getResourceType());
        $this->pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $this->pageResult;
    }

    private function setBreadcrumbs(CategoryEntity $category, string $rootCategoryId): void
    {
        $breadcrumbs = [];

        $categoryBreadcrumbs = $category->buildSeoBreadcrumb($rootCategoryId) ?? [];

        foreach($categoryBreadcrumbs as $id => $name)
        {
            $breadcrumbs[$id] = [
                'name' => $name,
                'path' => $this->router->generate(PageController::NAVIGATION_PAGE_ROUTE, ['navigationId' => $id])
            ];
        }

        $this->pageResult->setBreadcrumb($breadcrumbs);
    }
}
