<?php

namespace WebwinkelKeur\Shopware;
use Valued\Shopware\BasePlugin;

class WebwinkelKeur extends BasePlugin {

    public function getModuleKey(): string {
        return 'webwinkelkeur';
    }

    public function getDisplayName(): string {
        return 'WebwinkelKeur';
    }

    public function getDashboardDomain(): string {
        return 'dashboard.webwinkelkeur.nl';
    }
}