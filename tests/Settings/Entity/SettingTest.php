<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Settings\Entity;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Settings\Entity\Setting;

final class SettingTest extends TestCase
{
    public function testConstructorSetsKeyAndValue(): void
    {
        $setting = new Setting('test_key', 'test_value');

        $this->assertSame('test_key', $setting->getKey());
        $this->assertSame('test_value', $setting->getValue());
    }

    public function testConstructorDefaultsValueToNull(): void
    {
        $setting = new Setting('test_key');

        $this->assertSame('test_key', $setting->getKey());
        $this->assertNull($setting->getValue());
    }

    public function testIdIsNullByDefault(): void
    {
        $setting = new Setting('test_key');

        $this->assertNull($setting->getId());
    }

    public function testSetValueReturnsFluent(): void
    {
        $setting = new Setting('test_key');
        $result = $setting->setValue('new_value');

        $this->assertSame($setting, $result);
        $this->assertSame('new_value', $setting->getValue());
    }

    public function testSetValueToNull(): void
    {
        $setting = new Setting('test_key', 'initial');
        $setting->setValue(null);

        $this->assertNull($setting->getValue());
    }
}
