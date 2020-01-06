<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\SalesChannel\OrderService;
use Shopware\Core\Checkout\Payment\PaymentService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\CheckoutController;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoader;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoader;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoader;
use Shopware\Storefront\Page\Checkout\Offcanvas\OffcanvasCartPageLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class DecoratedCheckoutController extends CheckoutController
{
    private $appUrl = 'https://shopware-pwa-faint-money.now.sh/';

    public function __construct(CartService $cartService, CheckoutCartPageLoader $cartPageLoader, CheckoutConfirmPageLoader $confirmPageLoader, CheckoutFinishPageLoader $finishPageLoader, OrderService $orderService, PaymentService $paymentService, OffcanvasCartPageLoader $offcanvasCartPageLoader, EntityRepositoryInterface $orderRepository)
    {
        parent::__construct($cartService, $cartPageLoader, $confirmPageLoader, $finishPageLoader, $orderService, $paymentService, $offcanvasCartPageLoader, $orderRepository);
    }

    /**
     * @Route("/checkout/finish", name="frontend.checkout.finish.page", options={"seo"="false"}, methods={"GET"})
     */
    public function finishPage(Request $request, SalesChannelContext $context): Response
    {
        $orderId = $request->get('orderId');

        $response = $this->redirect($this->appUrl . '?orderId=' . $orderId);
        $response->headers->add([
            'sw-order-id' => $orderId
        ]);

        return $response;
    }
}
