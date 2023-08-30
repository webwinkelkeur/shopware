<?php

namespace WebwinkelKeur\Shopware;
use Valued\Shopware\BasePlugin;

class WebwinkelKeur extends BasePlugin {

    public function getSystemKey(): string {
        return 'webwinkelkeur';
    }

    public function getSystemName(): string {
        return 'WebwinkelKeur';
    }

    public function getDashboardDomain(): string {
        return 'dashboard.webwinkelkeur.nl';
    }
}