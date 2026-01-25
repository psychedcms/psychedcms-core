<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\NumberField;
use ReflectionClass;

final class NumberFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new NumberField();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsNumber(): void
    {
        $attribute = new NumberField();

        $this->assertSame('number', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new NumberField();
        $schema = $attribute->toSchemaArray();

        $this->assertSame('number', $schema['type']);
    }

    public function testNumberTypeDefaultsToInteger(): void
    {
        $attribute = new NumberField();

        $this->assertSame('integer', $attribute->numberType);
    }

    public function testNumberTypeOmittedFromSchemaWhenDefault(): void
    {
        $attribute = new NumberField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('numberType', $schema);
    }

    public function testNumberTypeIncludedInSchemaWhenFloat(): void
    {
        $attribute = new NumberField(numberType: 'float');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('numberType', $schema);
        $this->assertSame('float', $schema['numberType']);
    }

    public function testMinIncludedInSchemaWhenSet(): void
    {
        $attribute = new NumberField(min: 0);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('min', $schema);
        $this->assertSame(0, $schema['min']);
    }

    public function testMinOmittedFromSchemaWhenNull(): void
    {
        $attribute = new NumberField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('min', $schema);
    }

    public function testMaxIncludedInSchemaWhenSet(): void
    {
        $attribute = new NumberField(max: 100);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('max', $schema);
        $this->assertSame(100, $schema['max']);
    }

    public function testMaxOmittedFromSchemaWhenNull(): void
    {
        $attribute = new NumberField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('max', $schema);
    }

    public function testStepIncludedInSchemaWhenSet(): void
    {
        $attribute = new NumberField(step: 0.5);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('step', $schema);
        $this->assertSame(0.5, $schema['step']);
    }

    public function testStepOmittedFromSchemaWhenNull(): void
    {
        $attribute = new NumberField();
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('step', $schema);
    }

    public function testAllOptionsIncludedInSchema(): void
    {
        $attribute = new NumberField(
            numberType: 'float',
            min: 0.0,
            max: 100.0,
            step: 0.1,
            label: 'Price',
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('float', $schema['numberType']);
        $this->assertSame(0.0, $schema['min']);
        $this->assertSame(100.0, $schema['max']);
        $this->assertSame(0.1, $schema['step']);
        $this->assertSame('Price', $schema['label']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(NumberField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
