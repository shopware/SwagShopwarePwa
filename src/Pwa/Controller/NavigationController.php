<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Entity\SalesChannelNavigation\SalesChannelNavigationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class NavigationController extends AbstractController
{
    /**
     * @var SalesChannelNavigationRepository
     */
    private $navigationRepository;

    public function __construct(SalesChannelNavigationRepository $navigationRepository)
    {
        $this->navigationRepository = $navigationRepository;
    }

    /**
     * Resolves a navigation tree for a given root node at a given depth
     *
     * @Route("/store-api/pwa/navigation", name="store-api.pwa.navigation", methods={"POST"})
     *
     * @deprecated since v0.2.0, use store-api.navigation instead
     *
     * @return JsonResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): JsonResponse
    {
        $rootNode = $request->get('rootNode') ?? $context->getSalesChannel()->getNavigationCategoryId();
        $depth = $request->get('depth');

        // 0 is invalid parameter, pre-check
        if($depth === 0)
        {
            return new JsonResponse([
                'message' => 'Invalid argument of depth 0'
            ], 400);
        }

        if($depth === null) {
            $depth = 1;
        }

        if($depth === -1) {
            $depth = PHP_INT_MAX;
        }

        $navigation = $this->navigationRepository->loadNavigation($rootNode, $depth, $context);

        return new JsonResponse(
            $navigation
        );
    }
}
