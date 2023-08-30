<?php

namespace TrustProfile\Shopware;

use Valued\Shopware\BasePlugin;

class TrustProfile extends BasePlugin {

    public function getSystemKey(): string {
        return 'trustprofile';
    }

    public function getSystemName(): string {
        return 'TrustProfile';
    }

    public function getDashboardDomain(): string {
        return 'dashboard.trustprofile.com';
    }
}