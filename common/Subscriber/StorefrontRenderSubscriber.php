<?php

namespace Valued\Shopware\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
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
            $event->setParameter('_system_key', $this->dashboardService->getSystemKey());
            $event->setParameter('_ask_for_consent', $this->dashboardService->getConfigValue('askForConsent', $sales_channel_id));
            $event->setParameter('_invite_delay', $this->dashboardService->getConfigValue('delay', $sales_channel_id));
        }
    }
}
