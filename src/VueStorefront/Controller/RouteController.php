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
     * @Route("/sales-channel-api/v{version}/vsf/routes", name="sales-channel-api.vsf.route.list", methods={"GET"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     */
    public function allRoutes(Request $request, SalesChannelContext $context)
    {
        $criteria = new Criteria();

        if($request->get('resource') !== null)
        {
            switch($request->get('resource'))
            {
                case 'product': $criteria->addFilter(new ContainsFilter('pathInfo', '/detail/')); break;
                case 'navigation': $criteria->addFilter(new ContainsFilter('pathInfo', '/navigation/')); break;
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
     * @Route("/sales-channel-api/v{version}/vsf/route", name="sales-channel-api.vsf.route.detail", methods={"GET"})
     *
     * @param ParameterBag $parameterBag
     * @param SalesChannelContext $context
     */
    public function route(RequestDataBag $data, SalesChannelContext $context)
    {
        $path = $data->get('path');
        if($path === null) {
            throw new NotFoundHttpException();
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));

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
}
