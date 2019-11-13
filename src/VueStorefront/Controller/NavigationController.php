<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelNavigation\SalesChannelNavigationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
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
     * @Route("/sales-channel-api/v{version}/vsf/navigation", name="sales-channel-api.vsf.navigation", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): JsonResponse
    {
        $rootNode = $request->get('rootNode') ?? $context->getSalesChannel()->getNavigationCategoryId();
        $depth = $request->get('depth', 0);

        if($depth === 0)
        {
            return new JsonResponse([
                'message' => 'Invalid argument of depth 0'
            ], 400);
        }

        $navigation = $this->navigationRepository->loadNavigation($rootNode, $depth, $context);

        return new JsonResponse([
            'count' => $navigation->getCount(),
            'elements' => $navigation->getChildren()
        ]);
    }
}
