<?php

declare(strict_types=1);

namespace t0mmy742\RespectValidationTranslation;

use function bindtextdomain;
use function textdomain;

class Loader
{
    private const DOMAIN = 'respect-validation-translation';
    private const DOMAIN_CUSTOM = 'respect-validation-translation-custom';

    public function loadTranslations(?string $customTranslationsDir = null): void
    {
        bindtextdomain(self::DOMAIN, __DIR__ . '/locale');
        textdomain(self::DOMAIN);

        if ($customTranslationsDir !== null) {
            bindtextdomain(self::DOMAIN_CUSTOM, $customTranslationsDir);
            textdomain(self::DOMAIN_CUSTOM);
        }
    }
}
