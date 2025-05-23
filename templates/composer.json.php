<?php

$system_key = getenv('SYSTEM_KEY');
$system_name = getenv('SYSTEM_NAME');
$technical_name_class = getenv('TECHNICAL_NAME');
$technical_name = strtolower($technical_name_class);
echo <<<JSON
{
    "name": "{$technical_name}/shopware",
    "description": "{$system_name} integration for Shopware",
    "type": "shopware-platform-plugin",
    "license": "proprietary",
    "authors": [
        {
            "name": "{$system_name}",
            "role": "Manufacturer"
        }
    ],
    "require": {
        "shopware/core": "^6.6.0"
    },
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^2.3"
    },
    "extra": {
        "shopware-plugin-class": "{$system_name}\\\\Shopware\\\\$technical_name_class",
        "label": {
            "en-GB": "{$system_name}",
            "de-DE": "{$system_name}",
            "nl-NL": "{$system_name}"
        },
        "description": {
            "en-GB": "Connect your store to {$system_name}.",
            "nl-NL": "Verbind je webshop met {$system_name}.",
            "de-DE": "Verbinden Sie Ihren Shop mit {$system_name}."
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
