<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\SelectField;
use ReflectionClass;

final class SelectFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsSelect(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);

        $this->assertSame('select', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectType(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertSame('select', $schema['type']);
    }

    public function testValuesWithInlineArray(): void
    {
        $attribute = new SelectField(values: ['foo', 'bar', 'baz']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('values', $schema);
        $this->assertSame(['foo', 'bar', 'baz'], $schema['values']);
    }

    public function testValuesWithKeyValueArray(): void
    {
        $attribute = new SelectField(values: ['yes' => 'Yes', 'no' => 'No']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('values', $schema);
        $this->assertSame(['yes' => 'Yes', 'no' => 'No'], $schema['values']);
    }

    public function testValuesWithContentTypeReference(): void
    {
        $attribute = new SelectField(values: 'contenttype/pages/{title}');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('values', $schema);
        $this->assertSame('contenttype/pages/{title}', $schema['values']);
    }

    public function testValuesWithPhpCallable(): void
    {
        $attribute = new SelectField(values: 'App\Books::getOptions');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('values', $schema);
        $this->assertSame('App\Books::getOptions', $schema['values']);
    }

    public function testMultipleDefaultsToFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);

        $this->assertFalse($attribute->multiple);
    }

    public function testMultipleOmittedFromSchemaWhenFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('multiple', $schema);
    }

    public function testMultipleIncludedInSchemaWhenTrue(): void
    {
        $attribute = new SelectField(values: ['a', 'b'], multiple: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('multiple', $schema);
        $this->assertTrue($schema['multiple']);
    }

    public function testSortableDefaultsToFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);

        $this->assertFalse($attribute->sortable);
    }

    public function testSortableOmittedFromSchemaWhenFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('sortable', $schema);
    }

    public function testSortableIncludedInSchemaWhenTrue(): void
    {
        $attribute = new SelectField(values: ['a', 'b'], sortable: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('sortable', $schema);
        $this->assertTrue($schema['sortable']);
    }

    public function testAutocompleteDefaultsToFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);

        $this->assertFalse($attribute->autocomplete);
    }

    public function testAutocompleteOmittedFromSchemaWhenFalse(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('autocomplete', $schema);
    }

    public function testAutocompleteIncludedInSchemaWhenTrue(): void
    {
        $attribute = new SelectField(values: ['a', 'b'], autocomplete: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('autocomplete', $schema);
        $this->assertTrue($schema['autocomplete']);
    }

    public function testLimitIncludedInSchemaWhenSet(): void
    {
        $attribute = new SelectField(values: ['a', 'b'], limit: 5);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('limit', $schema);
        $this->assertSame(5, $schema['limit']);
    }

    public function testLimitOmittedFromSchemaWhenNull(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('limit', $schema);
    }

    public function testSortIncludedInSchemaWhenSet(): void
    {
        $attribute = new SelectField(values: ['a', 'b'], sort: 'asc');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('sort', $schema);
        $this->assertSame('asc', $schema['sort']);
    }

    public function testSortOmittedFromSchemaWhenNull(): void
    {
        $attribute = new SelectField(values: ['a', 'b']);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('sort', $schema);
    }

    public function testAllOptionsIncludedInSchema(): void
    {
        $attribute = new SelectField(
            values: ['red', 'green', 'blue'],
            multiple: true,
            sortable: true,
            autocomplete: true,
            limit: 3,
            sort: 'asc',
            label: 'Colors',
            required: true,
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('select', $schema['type']);
        $this->assertSame(['red', 'green', 'blue'], $schema['values']);
        $this->assertTrue($schema['multiple']);
        $this->assertTrue($schema['sortable']);
        $this->assertTrue($schema['autocomplete']);
        $this->assertSame(3, $schema['limit']);
        $this->assertSame('asc', $schema['sort']);
        $this->assertSame('Colors', $schema['label']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(SelectField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
