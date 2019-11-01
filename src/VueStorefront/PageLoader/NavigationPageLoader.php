<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoader;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use SwagVueStorefront\VueStorefront\PageResult\Navigation\NavigationPageResult;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NavigationPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.navigation.page';

    /**
     * @var SalesChannelCmsPageLoader
     */
    private $cmsPageLoader;

    /**
     * @var SalesChannelRepositoryInterface
     */
    private $categoryRepository;

    public function __construct(SalesChannelRepositoryInterface $categoryRepository, SalesChannelCmsPageLoader $cmsPageLaoder)
    {
        $this->cmsPageLoader = $cmsPageLaoder;
        $this->categoryRepository = $categoryRepository;
    }

    public function supports(string $resourceType): bool
    {
        return $resourceType === self::RESOURCE_TYPE;
    }

    public function load(PageLoaderContext $pageLoaderContext)
    {
        $pageResult = new NavigationPageResult();

        $categoryResult = $this->categoryRepository->search(new Criteria([$pageLoaderContext->getResourceIdentifier()]), $pageLoaderContext->getContext());

        if(!$categoryResult->has($pageLoaderContext->getResourceIdentifier()))
        {
            throw new NotFoundHttpException(sprintf('Category %s not found.', $pageLoaderContext->getResourceIdentifier()));
        }

        /** @var $category CategoryEntity */
        $category = $categoryResult->get($pageLoaderContext->getResourceIdentifier());

        $cmsPage = $this->cmsPageLoader->load($pageLoaderContext->getRequest(), new Criteria([$category->getCmsPageId()]), $pageLoaderContext->getContext());

        $pageResult->setCmsPage($cmsPage->get($category->getCmsPageId()));

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
