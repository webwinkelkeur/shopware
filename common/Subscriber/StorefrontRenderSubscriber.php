<?php

namespace Valued\Shopware\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var SystemConfigService
     */
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $system_config_service) {
        $this->systemConfigService = $system_config_service;
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onRender',
        ];
    }

    public function onRender(StorefrontRenderEvent $event): void {
        $sales_channel_id = $event->getContext()->getSource()->getSalesChannelId();
        $webshop_id = $this->systemConfigService->get(sprintf('%s.config.webshopId', 'WebwinkelKeur'), $sales_channel_id); //TODO get name from plugin
        $sidebar_enabled = $this->systemConfigService->get(sprintf('%s.config.webwinkelKeurJavascript', 'WebwinkelKeur'), $sales_channel_id); // TODO use plugin
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
                sprintf('https://%s', 'dashboard.webwinkelkeur.nl'), //TODO add host
            );
        }
    }
}
