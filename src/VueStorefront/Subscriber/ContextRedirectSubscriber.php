<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Subscriber;

use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\KernelEvents;

class ContextRedirectSubscriber implements EventSubscriberInterface
{
    /** @var string identifier of the GET parameter to be used for the context token */
    const CONTEXT_TOKEN_PARAM = 'contextToken';

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['rewriteContextHeader', 35] // Execute before platform/src/Storefront/Framework/Routing/StorefrontSubscriber.php:77
        ];
    }

    public function rewriteContextHeader()
    {
        $master = $this->requestStack->getMasterRequest();

        if (!$master) {
            return;
        }
        if (!$master->attributes->get(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        // Header is already present
        if($master->headers->has(PlatformRequest::HEADER_CONTEXT_TOKEN))
        {
            return;
        }

        // No contextToken set, we don't care, otherwise use contextToken to
        if(!$contextToken = $master->get(self::CONTEXT_TOKEN_PARAM)) {
            return;
        }

        // Relying on merge_requests/1258
        $master->headers->add([
            PlatformRequest::HEADER_CONTEXT_TOKEN => $contextToken
        ]);

        return;

    }
}
