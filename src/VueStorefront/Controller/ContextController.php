<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 *
 * @deprecated
 */
class ContextController extends AbstractController
{
    /**
     * @Route("/sales-channel-api/v{version}/vsf/context", name="sales-channel-api.vsf.context", methods={"GET"})
     *
     * @deprecated
     *
     * @return JsonResponse
     */
    public function context(): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Method has been removed. Please use "/sales-channel-api/v1/context" instead'
        ]);
    }
}
