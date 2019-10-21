<?php

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/sales-channel-api/v{version}/vsf/context", name="sales-channel-api.vsf.context", methods={"GET"})
     *
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function context(SalesChannelContext $context): JsonResponse
    {
        return new JsonResponse($context);
    }
}
