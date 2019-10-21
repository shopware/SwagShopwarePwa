<?php

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
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
     * @var int
     */
    private $maxLimit;

    public function __construct(SalesChannelRouteRepository $routeRepository, int $maxLimit)
    {
        $this->routeRepository = $routeRepository;
        $this->maxLimit = $maxLimit;
    }

    /**
     *
     *
     * @Route("/sales-channel-api/v{version}/vsf/routes", name="sales-channel-api.vsf.route.list", methods={"GET"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function routes(RequestDataBag $data, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria();

        if($data->get('resource') !== null)
        {
            switch($data->get('resource'))
            {
                case 'product': $criteria->addFilter(new ContainsFilter('routeName', 'detail')); break;
                case 'navigation': $criteria->addFilter(new ContainsFilter('routeName', 'navigation')); break;
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
     * Match and return routes for a given path
     *
     * @Route("/sales-channel-api/v{version}/vsf/match", name="sales-channel-api.vsf.route.match", methods={"GET"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function match(RequestDataBag $data, SalesChannelContext $context): JsonResponse
    {
        $criteria = new Criteria();

        $path = $data->get('path');

        if($path === null) {
            throw new NotFoundHttpException();
        }

        $criteria->addFilter(new ContainsFilter('seoPathInfo', $path));

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
     * @Route("/sales-channel-api/v{version}/vsf/resolve", name="sales-channel-api.vsf.route.resolve", methods={"GET"})
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
