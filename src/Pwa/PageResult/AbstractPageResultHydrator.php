<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\Router;
use SwagShopwarePwa\Pwa\Controller\PageController;

abstract class AbstractPageResultHydrator
{
    /**
     * @param EntityRepository<SeoUrlCollection> $seoUrlRepository
     */
    public function __construct(
        private readonly Router $router,
        private readonly EntityRepository $seoUrlRepository,
        private readonly CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function getBreadcrumbs(CategoryEntity $category, SalesChannelContext $context): array
    {
        $breadcrumbs = [];

        $rootCategoryId = $context->getSalesChannel()->getNavigationCategoryId();

        $categoryBreadcrumbs = $this->categoryBreadcrumbBuilder->build($category, null, $rootCategoryId);

        $canonicalUrls = $this->getCanonicalUrls(array_keys($categoryBreadcrumbs), $context);

        foreach ($categoryBreadcrumbs as $id => $name) {
            $breadcrumbs[$id] = [
                'name' => $name,
                'path' => $canonicalUrls[$id] ?? $this->router->generate(PageController::NAVIGATION_PAGE_ROUTE, ['navigationId' => $id]),
            ];
        }

        return $breadcrumbs;
    }

    /**
     * @param array<string> $categoryIds
     * @return array<string, string>
     */
    private function getCanonicalUrls(array $categoryIds, SalesChannelContext $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('routeName', PageController::NAVIGATION_PAGE_ROUTE));
        $criteria->addFilter(new EqualsFilter('isCanonical', true));
        $criteria->addFilter(new EqualsAnyFilter('foreignKey', $categoryIds));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannel()->getId()));
        $criteria->addFilter(new EqualsFilter('languageId', $context->getContext()->getLanguageId()));

        $result = $this->seoUrlRepository->search($criteria, $context->getContext());

        $pathByCategoryId = [];

        /** @var SeoUrlEntity $seoUrl */
        foreach ($result as $seoUrl) {
            // Map all urls to their corresponding category
            $pathByCategoryId[$seoUrl->getForeignKey()] = '/' . ($seoUrl->getSeoPathInfo() ?? $seoUrl->getPathInfo());
        }

        return $pathByCategoryId;
    }
}
