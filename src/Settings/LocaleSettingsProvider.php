<?php

declare(strict_types=1);

namespace PsychedCms\Core\Settings;

use Doctrine\DBAL\Connection;

/**
 * Provides locale settings.
 * Supported locales come from env config (immutable).
 * Default locale is read from DB with env var fallback.
 */
class LocaleSettingsProvider
{
    /** @var list<string> */
    private readonly array $supportedLocales;

    private ?string $cachedDefaultLocale = null;

    public function __construct(
        private readonly Connection $connection,
        string $appLocales,
        private readonly string $envDefaultLocale,
    ) {
        $this->supportedLocales = explode('|', $appLocales);
    }

    public function getDefaultLocale(): string
    {
        if ($this->cachedDefaultLocale !== null) {
            return $this->cachedDefaultLocale;
        }

        $this->cachedDefaultLocale = $this->fetchSetting('default_locale') ?? $this->envDefaultLocale;

        return $this->cachedDefaultLocale;
    }

    /**
     * @return list<string>
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Clear cached default locale (called after settings are updated).
     */
    public function clearCache(): void
    {
        $this->cachedDefaultLocale = null;
    }

    private function fetchSetting(string $key): ?string
    {
        try {
            $result = $this->connection->fetchOne(
                'SELECT setting_value FROM settings WHERE setting_key = ?',
                [$key],
            );

            return $result !== false ? $result : null;
        } catch (\Throwable) {
            // Table may not exist yet (before migration)
            return null;
        }
    }
}
