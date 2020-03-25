<?php

namespace WebwinkelKeur\Subscriber;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StorefrontRenderSubscriber implements EventSubscriberInterface {
    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService) {
        $this->systemConfigService = $systemConfigService;
    }

    public static function getSubscribedEvents(): array {
        return [
            StorefrontRenderEvent::class => 'onRender',
        ];
    }

    public function onRender(StorefrontRenderEvent $event): void {
        $webshop_id= $this->systemConfigService->get('WebwinkelKeur.config.webshopId');
        $webwinkelKeur_javascript = $this->systemConfigService->get('WebwinkelKeur.config.webwinkelKeurJavascript');
        if (empty($webshop_id)) {
            $webwinkelKeur_javascript = false;
        }
        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setParameter(
                '_webwinkelkeur_id',
                $webshop_id
            );
            $event->setParameter(
                'webwinkelKeur_javascript',
                $webwinkelKeur_javascript
            );
        }
    }
}
