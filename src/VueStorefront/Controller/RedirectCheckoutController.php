<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class RedirectCheckoutController extends StorefrontController
{
    private $appUrl = 'https://shopware-pwa-faint-money.now.sh/';

    /**
     * @Route("/checkout/finish", name="frontend.checkout.finish.page", options={"seo"="false"}, methods={"GET"})
     */
    public function finishPage(Request $request, SalesChannelContext $context): Response
    {
        $orderId = $request->get('orderId');

        $redirectUrl = $this->appUrl . '?orderId=' . $orderId;

        return new RedirectResponse($redirectUrl, 302, [
            'sw-order-id' => $orderId
        ]);
    }
}
