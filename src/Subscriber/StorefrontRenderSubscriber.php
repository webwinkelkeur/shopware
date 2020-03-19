<?php


namespace WebwinkelKeur\Subscriber;

use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

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
        $apiKey = $this->systemConfigService->get('WebwinkelKeur.config.apiKey');
        $webshop_id= $this->systemConfigService->get('WebwinkelKeur.config.webshopId');
        $enableSidebar = $this->systemConfigService->get('WebwinkelKeur.config.enableSidebar');
        if (empty($webshop_id)) {
            $enableSidebar = false;
        }
        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setParameter(
                '_webwinkelkeur_id',
                $webshop_id
            );
            $event->setParameter(
                'enableSidebar',
                $enableSidebar
            );
        }

    }
}

