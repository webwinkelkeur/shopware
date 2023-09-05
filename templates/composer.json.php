<?php

$system_key = getenv('SYSTEM_KEY');
$system_name = getenv('SYSTEM_NAME');
echo <<<JSON
{
    "name": "{$system_key}/shopware",
    "description": "{$system_name} integration for Shopware",
    "type": "shopware-platform-plugin",
    "license": "MIT",
    "authors": [
        {
            "name": "{$system_name}",
            "role": "Manufacturer"
        }
    ],
    "require": {
        "shopware/core": "^6.1.0"
    },
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^2.3"
    },
    "extra": {
        "shopware-plugin-class": "{$system_name}\\\\Shopware\\\\$system_name",
        "plugin-icon": "{$system_name}\\\\Resources\\\\config\\\\plugin.png",
        "label": {
            "en-GB": "{$system_name}",
            "nl-NL": "{$system_name}"
        },
        "description": {
            "en-GB": "Connect your store to {$system_name}.",
            "nl-NL": "Verbind je webshop met {$system_name}."
        }
    },
    "autoload": {
        "psr-4": {
            "Valued\\\\Shopware\\\\": "common",
            "{$system_name}\\\\Shopware\\\\": "src/"
        }
    }
}

JSON;