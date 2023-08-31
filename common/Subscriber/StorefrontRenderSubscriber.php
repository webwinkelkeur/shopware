<?php

namespace Valued\Shopware\Subscriber;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Page\Checkout\Finish\CheckoutFinishPage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Valued\Shopware\Service\DashboardService;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var SystemConfigService
     */
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService) {
        $this->dashboardService = $dashboardService;
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onRender',
        ];
    }

    public function onRender(StorefrontRenderEvent $event): void {
        $sales_channel_id = $event->getContext()->getSource()->getSalesChannelId();
        $webshop_id = $this->dashboardService->getConfigValue('webshopId', $sales_channel_id);
        $sidebar_enabled = $this->dashboardService->getConfigValue('webwinkelKeurJavascript', $sales_channel_id);
        if (empty($webshop_id)) {
            $sidebar_enabled = false;
        }
        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setParameter('_system_key', $this->dashboardService->getSystemKey());
            if ($this->isThankYouPage($event)) {
                $event->setParameter('_order_feed', $this->getOrderFeed($event->getParameters()['page']->getOrder(), $sales_channel_id));
            }
            $event->setParameter(
                '_shop_id',
                $webshop_id,
            );
            $event->setParameter(
                '_sidebar_enabled',
                $sidebar_enabled,
            );
            $event->setParameter(
                '_dashboard_url',
                sprintf('https://%s', $this->dashboardService->getDashboardHost()),
            );
        }
    }

    private function isThankYouPage(StorefrontRenderEvent $event): bool {
        return ($event->getParameters()['page'] ?? null) instanceof CheckoutFinishPage;
    }

    private function getOrderFeed(OrderEntity $orderEntity, string $sales_channel_id): ?string {
        if (!$this->dashboardService->getConfigValue('askForConsent', $sales_channel_id)) {
            return null;
        }

        $customer = $orderEntity->getOrderCustomer();
        $order_feed = [
            'webshopId' => $this->dashboardService->getConfigValue('webshopId', $sales_channel_id),
            'orderNumber' => $orderEntity->getOrderNumber(),
            'email' => $customer->getEmail(),
            'firstName' => $customer->getFirstName(),
            'inviteDelay' => $this->dashboardService->getConfigValue('delay', $sales_channel_id),
        ];

        $signature = hash_hmac(
            'sha512',
            http_build_query($order_feed),
            $this->getHashKey($sales_channel_id)
        );

        $order_feed['signature'] = $signature;

        return json_encode($order_feed, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }


    private function getHashKey(string $sales_channel_id): string {
        return sprintf(
            '%s:%s',
            $this->dashboardService->getConfigValue('webshopId', $sales_channel_id),
            $this->dashboardService->getConfigValue('apiKey', $sales_channel_id)
        );
    }

}
