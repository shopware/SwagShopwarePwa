<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Subscriber;

use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutFinishPageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CheckoutFinishPageLoadedEvent::class => 'finishPageRedirect'
        ];
    }

    public function finishPageRedirect(CheckoutFinishPageLoadedEvent $event)
    {
        $finishPage = $event->getPage();
        $orderId = $finishPage->getOrder()->getId();
    }
}
