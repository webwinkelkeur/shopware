<?php

namespace Valued\Shopware\Subscriber;

use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Valued\Shopware\Service\DashboardService;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var DashboardService
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
        $sidebar_enabled = $this->dashboardService->getConfigValue(
            sprintf('%sJavascript', lcfirst($this->dashboardService->getSystemName())),
            $sales_channel_id,
        );
        if (empty($webshop_id)) {
            $sidebar_enabled = false;
        }

        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setParameter('_system_key', $this->dashboardService->getSystemKey());
            $event->setParameter('_technical_name', $this->dashboardService->getTechnicalName());
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

}
