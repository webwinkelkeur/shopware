<?php

namespace Valued\Shopware;

use Shopware\Core\Framework\Plugin\Context\ActivateContext;

abstract class BasePlugin extends \Shopware\Core\Framework\Plugin {

    public function activate(ActivateContext $activateContext): void {
        parent::install($activateContext);

        $config = $this->container->get('Shopware\Core\System\SystemConfig\SystemConfigService');
        $config->set($this->getConfigKeyName('systemName'), $this->getSystemName());
        $config->set($this->getConfigKeyName('systemKey'), $this->getSystemKey());
        $config->set($this->getConfigKeyName('dashboardDomain'), $this->getDashboardDomain());
    }

    public abstract function getSystemKey(): string;

    public abstract function getSystemName(): string;

    public abstract function getDashboardDomain(): string;

    private function getConfigKeyName(string $key): string {
        return sprintf('%s.config.%s', $this->getSystemName(), $key);
    }
}