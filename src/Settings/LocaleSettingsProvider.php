<?php

declare(strict_types=1);

namespace PsychedCms\Core\Settings;

use PsychedCms\Core\Settings\Repository\SettingRepository;

class LocaleSettingsProvider
{
    private const SETTING_LOCALES = 'app_locales';
    private const SETTING_DEFAULT_LOCALE = 'default_locale';

    /** @var string[] */
    private readonly array $appLocales;

    public function __construct(
        private readonly SettingRepository $settingRepository,
        string $appLocales,
        private readonly string $envDefaultLocale,
    ) {
        $this->appLocales = array_filter(array_map('trim', explode('|', $appLocales)));
    }

    /**
     * @return string[]
     */
    public function getSupportedLocales(): array
    {
        $dbValue = $this->settingRepository->get(self::SETTING_LOCALES);

        if (null !== $dbValue && '' !== $dbValue) {
            $locales = array_filter(array_map('trim', explode(',', $dbValue)));
            if ([] !== $locales) {
                return $locales;
            }
        }

        return $this->appLocales;
    }

    public function getDefaultLocale(): string
    {
        return $this->settingRepository->get(self::SETTING_DEFAULT_LOCALE)
            ?? $this->envDefaultLocale;
    }
}
