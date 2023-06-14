<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContextBuilderInterface;
use SwagShopwarePwa\Pwa\PageLoader\PageLoaderInterface;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class PageController extends AbstractController
{
    /**
     * Placeholder, because these routes names may change during implementation
     *
     * string
     */
    const PRODUCT_PAGE_ROUTE = 'frontend.detail.page';

    const NAVIGATION_PAGE_ROUTE = 'frontend.navigation.page';

    const LANDING_PAGE_ROUTE = 'frontend.landing.page';

    /**
     * @var PageLoaderContextBuilderInterface
     */
    private $pageLoaderContextBuilder;

    /**
     * @var PageLoaderInterface[]
     */
    private $pageLoaders;

    public function __construct(PageLoaderContextBuilderInterface $pageLoaderContextBuilder, iterable $pageLoaders)
    {
        $this->pageLoaderContextBuilder = $pageLoaderContextBuilder;

        /** @var PageLoaderInterface $pageLoader */
        foreach ($pageLoaders as $pageLoader) {
            $this->pageLoaders[$pageLoader->getResourceType()] = $pageLoader;
        }
    }

    /**
     * @Route("/store-api/pwa/page", name="store-api.pwa.cms-page-resolve", methods={"POST"})
     *
     * @OA\Post(
     *      path="/pwa/page",
     *      summary="Resolves a page by its relative `path`. Additional information, like *breadcrumb*, an associated *product* or *category* and the type of resource is fetched along with it.",
     *      operationId="pwaResolvePage",
     *      tags={"Store API", "PWA"},
     *      @OA\Parameter(name="Api-Basic-Parameters"),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"path"},
     *              @OA\Property(
     *                  property="path",
     *                  description="Relative path to the page that should be resolved",
     *                  type="string"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="The resolved page including additional data.",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="resourceType",
     *                          description="Type of page that was fetched. Indicates whether it is a product page or a category page",
     *                          type="string",
     *                          enum={"frontend.detail.page", "frontend.navigation.page", "frontend.landing.page"}
     *                      ),
     *                      @OA\Property(
     *                          property="resourceIdentifier",
     *                          description="Identifier of the page that was fetched",
     *                          type="string",
     *                          pattern="^[0-9a-f]{32}$"
     *                      ),
     *                      @OA\Property(
     *                          property="canonicalPathInfo",
     *                          description="Canonical path of the resolved page",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="cmsPage",
     *                          description="Hydrated CMS layout associated with the loaded page. Value is `null`, when no layout is assigned",
     *                          ref="#/components/schemas/CmsPage"
     *                      ),
     *                      @OA\Property(
     *                          property="breadcrumb",
     *                          description="Contains information about the category path to the loaded page.
Each element has the category identifier as its key and contains a `path` as well as a `name`. Elements are ordered by descending hierarchy in the category tree",
     *                          type="object",
     *                          example={
    "2bef17ac2bb54c63a2403bdca434d0df": {
        "name": "Shoes, Baby & Health",
        "path": "/Shoes-Baby-Health/"
    },
    "5ce716877e33420cbe3794f92939de70": {
        "name": "Electronics",
        "path": "/Shoes-Baby-Health/Electronics\/"
    }
}
     *                      )
     *                  ),
     *                  @OA\Schema(
     *                      oneOf={
     *                          @OA\Schema(
     *                              description="A product result contains product information and product variant/configuration information. It corresponds with a `resourceType` of `frontend.detail.page",
     *                              @OA\Property(
     *                                  property="product",
     *                                  description="The product associated with the loaded page.",
     *                                  ref="#/components/schemas/Product"
     *                              ),
     *                              @OA\Property(
     *                                  property="configurator",
     *                                  description="List of property groups with their corresponding options and information on how to display them.",
     *                                  ref="#/components/schemas/PropertyGroup"
     *                              )
     *                          ),
     *                          @OA\Schema(
     *                              description="A category result contains category information. It corresponds with a `resourceType` of `frontend.navigation.page",
     *                              @OA\Property(
     *                                  property="category",
     *                                  description="The category associated with the loaded page.",
     *                                  ref="#/components/schemas/Category"
     *                              )
     *                          ),
     *                          @OA\Schema(
     *                              description="A landing page result contains no specific fields."
     *                          )
     *                      }
     *                  )
     *              }
     *          )
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="The resource could not be resolved or no path is provided.",
     *          ref="#/components/responses/404"
     *     ),
     * )
     *
     * Resolve a page for a given resource and resource identification or path
     * First, a PageLoaderContext object is assembled, which includes information about the resource, request and context.
     * Then, the page is loaded through the page loader only given the page loader context.
     *
     * @param Request $request
     * @return CmsPageRouteResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): CmsPageRouteResponse
    {
        $pageLoaderContext = $this->pageLoaderContextBuilder->build($request, $context);

        $pageLoader = $this->getPageLoader($pageLoaderContext);

        if (!$pageLoader) {
            throw new PageNotFoundException($pageLoaderContext->getResourceType() . $pageLoaderContext->getResourceIdentifier());
        }

        return new CmsPageRouteResponse($this->getPageResult($pageLoader, $pageLoaderContext));
    }

    /**
     * Determines the correct page loader for a given resource type
     *
     * @param PageLoaderContext $pageLoaderContext
     * @return PageLoaderInterface|null
     */
    private function getPageLoader(PageLoaderContext $pageLoaderContext): ?PageLoaderInterface
    {
        return $this->pageLoaders[$pageLoaderContext->getResourceType()] ?? null;
    }

    /**
     * Loads the page given the correct page loader and context and returns the assembled page result.
     *
     * @param PageLoaderInterface $pageLoader
     * @param PageLoaderContext $pageLoaderContext
     * @return AbstractPageResult
     */
    private function getPageResult(PageLoaderInterface $pageLoader, PageLoaderContext $pageLoaderContext): AbstractPageResult
    {
        /** @var AbstractPageResult $pageResult */
        $pageResult = $pageLoader->load($pageLoaderContext);

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());
        $pageResult->setCanonicalPathInfo($pageLoaderContext->getRoute()->getCanonicalPathInfo() ?: $pageLoaderContext->getRoute()->getPathInfo());

        return $pageResult;
    }
}
