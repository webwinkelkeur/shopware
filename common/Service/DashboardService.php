<?php

namespace Valued\Shopware\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class DashboardService {

    private SystemConfigService $systemConfigService;
    private string $systemKey;
    private string $systemName;
    private string $dashboardHost;

    public function __construct(
        SystemConfigService $system_config_service,
        string $system_key,
        string $system_name,
        string $dashboard_host
    ) {
        $this->systemConfigService = $system_config_service;
        $this->systemKey = $system_key;
        $this->systemName = $system_name;
        $this->dashboardHost = $dashboard_host;
    }

    public function getSystemKey(): string {
        return $this->systemKey;
    }

    public function getSystemName(): string {
        return $this->systemName;
    }

    public function getDashboardHost(): string {
        return $this->dashboardHost;
    }

    public function getConfigValue(string $key, string $sales_channel_id) {
        return $this->systemConfigService->get(sprintf(
            '%s.config.%s',
            $this->systemName,
            $key,
        ), $sales_channel_id);
    }
}