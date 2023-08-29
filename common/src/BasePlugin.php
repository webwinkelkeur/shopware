<?php

abstract class BasePlugin extends \Shopware\Core\Framework\Plugin {
    public abstract function getModuleKey(): string;

    public abstract function getDisplayName(): string;

    public abstract function getDashboardDomain(): string;
}