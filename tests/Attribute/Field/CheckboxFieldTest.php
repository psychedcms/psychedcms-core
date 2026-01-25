<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\CheckboxField;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;

final class CheckboxFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new CheckboxField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsCheckbox(): void
    {
        $attribute = new CheckboxField();

        $this->assertSame('checkbox', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new CheckboxField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('checkbox', $schema['type']);
    }

    public function testVariantDefaultsToInline(): void
    {
        $attribute = new CheckboxField();

        $this->assertSame('inline', $attribute->variant);
    }

    public function testVariantOmittedFromSchemaWhenDefault(): void
    {
        $attribute = new CheckboxField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('variant', $schema);
    }

    public function testVariantIncludedInSchemaWhenNonDefault(): void
    {
        $attribute = new CheckboxField(variant: 'switch');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('variant', $schema);
        $this->assertSame('switch', $schema['variant']);
    }

    public function testInheritsAllBaseOptions(): void
    {
        $attribute = new CheckboxField(
            label: 'Active',
            group: 'settings',
            info: 'Enable this feature',
            default: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('Active', $schema['label']);
        $this->assertSame('settings', $schema['group']);
        $this->assertSame('Enable this feature', $schema['info']);
        $this->assertTrue($schema['default']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(CheckboxField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
