<?php

namespace Valued\Shopware\Service;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class DashboardService {

    private SystemConfigService $systemConfigService;
    private string $systemKey;
    private string $technicalName;
    private string $systemName;
    private string $dashboardHost;

    public function __construct(
        SystemConfigService $system_config_service,
        string $system_key,
        string $technicalName,
        string $systemName,
        string $dashboardHost
    ) {
        $this->systemConfigService = $system_config_service;
        $this->systemKey = $system_key;
        $this->technicalName = $technicalName;
        $this->systemName = $systemName;
        $this->dashboardHost = $dashboardHost;
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

    public function getTechnicalName(): string {
        return $this->technicalName;
    }

    public function getConfigValue(string $key, ?string $sales_channel_id) {
        return $this->systemConfigService->get(sprintf(
            '%s.config.%s',
            $this->technicalName,
            $key,
        ), $sales_channel_id);
    }
}
