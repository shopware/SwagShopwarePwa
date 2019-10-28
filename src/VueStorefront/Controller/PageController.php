<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\PageLoader\PageLoaderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 */
class PageController extends AbstractController
{
    /**
     * @var PageLoaderInterface[]
     */
    private $pageLoaders;

    public function __construct(iterable $pageLoaders)
    {
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
        /**
         * Pseduo:
         *
         * resourceType: 'detail', 'listing'
         * resourceIdentifier: 'foo'
         *
         * 1. determine required page
         * 2. fetch page data using page loaders
         * 3. grab enhanced data for potential includes
         * 4. display / convert data to fit response structure
         */

        $pageLoader = $this->getPageLoaderForRequest($request);

        if(!$pageLoader)
        {
            return new JsonResponse(['error' => 'Page not found'], 404);
        }

        $pageResult = $pageLoader->load($request, $context);

        return new JsonResponse($pageResult);
    }

    private function getPageLoaderForRequest(Request $request)
    {
        foreach($this->pageLoaders as $pageLoader)
        {
            if($pageLoader->supports($request)) {
                return $pageLoader;
            }
        }

        return null;
    }
}

