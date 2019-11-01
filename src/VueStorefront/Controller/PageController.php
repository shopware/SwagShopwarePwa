<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteRepository;
use SwagVueStorefront\VueStorefront\PageLoader\PageLoaderContext;
use SwagVueStorefront\VueStorefront\PageLoader\PageLoaderInterface;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 */
class PageController extends AbstractController
{
    /**
     * @var SalesChannelRouteRepository
     */
    private $routeRepository;

    /**
     * @var PageLoaderInterface[]
     */
    private $pageLoaders;

    public function __construct(SalesChannelRouteRepository $routeRepository, iterable $pageLoaders)
    {
        $this->routeRepository = $routeRepository;
        $this->pageLoaders = $pageLoaders;
    }

    /**
     * @Route("/sales-channel-api/v{version}/vsf/page", name="sales-channel-api.vsf.page", methods={"POST"})
     *
     * Resolve a page for a given resource and resource identification or path
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): JsonResponse
    {
        $pageLoaderContext = $this->getPageLoaderContextByRequest($request, $context);

        $pageLoader = $this->getPageLoader($pageLoaderContext);

        if(!$pageLoader)
        {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        /** @var AbstractPageResult $pageResult */
        $pageResult = $pageLoader->load($pageLoaderContext);
        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return new JsonResponse($pageResult);
    }

    /**
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return PageLoaderContext
     */
    private function getPageLoaderContextByRequest(Request $request, SalesChannelContext $context): PageLoaderContext
    {
        $path = $request->get('path');

        if($path === null) {
            throw new NotFoundHttpException('Please provide a path to be resolved.');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));

        /**
         * @var $routes SalesChannelRouteEntity[]
         */
        $routes = $this->routeRepository->search($criteria, $context->getContext());

        if(count($routes) === 0)
        {
            throw new NotFoundHttpException(sprintf('Path "%s" could not be resolved', $path));
        }

        $pageLoaderContext = new PageLoaderContext();
        $pageLoaderContext->setResourceType($routes[0]->getRouteName());
        $pageLoaderContext->setResourceIdentifier($routes[0]->getResourceIdentifier());
        $pageLoaderContext->setContext($context);
        $pageLoaderContext->setRequest($request);

        return $pageLoaderContext;
    }

    private function getPageLoader(PageLoaderContext $pageLoaderContext)
    {
        foreach($this->pageLoaders as $pageLoader)
        {
            if($pageLoader->supports($pageLoaderContext->getResourceType())) {
                return $pageLoader;
            }
        }

        return null;
    }
}
