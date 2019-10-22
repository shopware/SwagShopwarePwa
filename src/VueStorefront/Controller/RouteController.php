<?php

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Seo\SeoUrl\SeoUrlDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 */
class RouteController extends AbstractController
{
    /**
     * @var SalesChannelRouteRepository
     */
    private $routeRepository;

    /**
     * @var RequestCriteriaBuilder
     */
    private $criteriaBuilder;

    public function __construct(SalesChannelRouteRepository $routeRepository, RequestCriteriaBuilder $criteriaBuilder)
    {
        $this->routeRepository = $routeRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * Fetches a list of routes for a given sales channel and optionally a given resource type
     *
     * @Route("/sales-channel-api/v{version}/vsf/routes", name="sales-channel-api.vsf.route.list", methods={"POST"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function routes(Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = $this->criteriaBuilder->handleRequest($request, new Criteria(), new SeoUrlDefinition(), $context->getContext());

        if($request->get('resource') !== null)
        {
            switch($request->get('resource'))
            {
                case 'product': $criteria->addFilter(new EqualsFilter('routeName', 'frontend.detail.page')); break;
                case 'navigation': $criteria->addFilter(new EqualsFilter('routeName', 'frontend.navigation.page')); break;
            }
        }

        $start = microtime(true);

        /** @var EntityCollection $seoUrlCollection */
        $seoUrlCollection = $this->routeRepository->search($criteria, $context->getContext());

        $end = microtime(true);

        return new JsonResponse([
            'duration' => round(($end - $start) * 1000, 2) . 'ms',
            'count' => count($seoUrlCollection),
            'data' => $seoUrlCollection
        ]);
    }

    /**
     * Match and return routes for a given path. Non-fuzzy by default.
     *
     * @Route("/sales-channel-api/v{version}/vsf/routes/match", name="sales-channel-api.vsf.route.match", methods={"POST"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function match(Request $request, SalesChannelContext $context): JsonResponse
    {
        $criteria = $this->criteriaBuilder->handleRequest($request, new Criteria(), new SeoUrlDefinition(), $context->getContext());

        $path = $request->get('path');

        if($path === null) {
            throw new NotFoundHttpException('Please provide a path which the routes should be matched against.');
        }

        // Fuzzy matching (way slower!)
        if($request->get('fuzzy') === true)
        {
            $criteria->addFilter(new ContainsFilter('seoPathInfo', $path));
        } else {
            $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));
        }

        $start = microtime(true);

        /** @var EntityCollection $seoUrlCollection */
        $seoUrlCollection = $this->routeRepository->search($criteria, $context->getContext());

        $end = microtime(true);

        return new JsonResponse([
            'duration' => round(($end - $start) * 1000, 2) . 'ms',
            'count' => count($seoUrlCollection),
            'data' => $seoUrlCollection
        ]);
    }

    /**
     * Resolve a route and hydrate the result if possible
     *
     * @Route("/sales-channel-api/v{version}/vsf/routes/resolve", name="sales-channel-api.vsf.route.resolve", methods={"POST"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function resolve(): JsonResponse
    {
        return new JsonResponse();
    }
}
