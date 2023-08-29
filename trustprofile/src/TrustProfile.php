<?php

namespace TrustProfile\Shopware;

use Valued\Shopware\BasePlugin;

class TrustProfile extends BasePlugin {

    public function getModuleKey(): string {
        return 'trustprofile';
    }

    public function getDisplayName(): string {
        return 'WebwinkelKeur';
    }

    public function getDashboardDomain(): string {
        return 'dashboard.trustprofile.com';
    }
}