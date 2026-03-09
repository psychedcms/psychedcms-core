<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Attribute\Field\RelationField;
use ReflectionClass;

final class RelationFieldTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new RelationField(reference: 'users');

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsRelation(): void
    {
        $attribute = new RelationField(reference: 'users');

        $this->assertSame('relation', $attribute->getFieldType());
    }

    public function testReferenceAlwaysInSchema(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertSame('users', $schema['reference']);
    }

    public function testMultipleOmittedWhenFalse(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('multiple', $schema);
    }

    public function testMultipleIncludedWhenTrue(): void
    {
        $attribute = new RelationField(reference: 'posts', multiple: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('multiple', $schema);
        $this->assertTrue($schema['multiple']);
    }

    public function testDisplayFieldOmittedWhenDefault(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('displayField', $schema);
    }

    public function testDisplayFieldIncludedWhenCustom(): void
    {
        $attribute = new RelationField(reference: 'users', displayField: 'email');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('displayField', $schema);
        $this->assertSame('email', $schema['displayField']);
    }

    public function testAutocompleteSearchOmittedWhenTrue(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('autocompleteSearch', $schema);
    }

    public function testAutocompleteSearchIncludedWhenFalse(): void
    {
        $attribute = new RelationField(reference: 'users', autocompleteSearch: false);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('autocompleteSearch', $schema);
        $this->assertFalse($schema['autocompleteSearch']);
    }

    public function testAllowCreateOmittedWhenFalse(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('allowCreate', $schema);
    }

    public function testAllowCreateIncludedWhenTrue(): void
    {
        $attribute = new RelationField(reference: 'users', allowCreate: true);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('allowCreate', $schema);
        $this->assertTrue($schema['allowCreate']);
    }

    public function testMinOmittedWhenNull(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('min', $schema);
    }

    public function testMinIncludedWhenSet(): void
    {
        $attribute = new RelationField(reference: 'users', min: 1);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('min', $schema);
        $this->assertSame(1, $schema['min']);
    }

    public function testMaxOmittedWhenNull(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('max', $schema);
    }

    public function testMaxIncludedWhenSet(): void
    {
        $attribute = new RelationField(reference: 'users', max: 5);
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('max', $schema);
        $this->assertSame(5, $schema['max']);
    }

    public function testOrderOmittedWhenNull(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('order', $schema);
    }

    public function testOrderIncludedWhenSet(): void
    {
        $attribute = new RelationField(reference: 'users', order: 'name');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('order', $schema);
        $this->assertSame('name', $schema['order']);
    }

    public function testFilterOmittedWhenNull(): void
    {
        $attribute = new RelationField(reference: 'users');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayNotHasKey('filter', $schema);
    }

    public function testFilterIncludedWhenSet(): void
    {
        $attribute = new RelationField(reference: 'users', filter: 'active');
        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('filter', $schema);
        $this->assertSame('active', $schema['filter']);
    }

    public function testAllOptionsTogetherSchema(): void
    {
        $attribute = new RelationField(
            reference: 'posts',
            multiple: true,
            displayField: 'title',
            autocompleteSearch: false,
            allowCreate: true,
            min: 1,
            max: 10,
            order: 'title',
            filter: 'published',
            label: 'Related Posts',
            group: 'metadata',
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('relation', $schema['type']);
        $this->assertSame('posts', $schema['reference']);
        $this->assertTrue($schema['multiple']);
        $this->assertSame('title', $schema['displayField']);
        $this->assertFalse($schema['autocompleteSearch']);
        $this->assertTrue($schema['allowCreate']);
        $this->assertSame(1, $schema['min']);
        $this->assertSame(10, $schema['max']);
        $this->assertSame('title', $schema['order']);
        $this->assertSame('published', $schema['filter']);
        $this->assertSame('Related Posts', $schema['label']);
        $this->assertSame('metadata', $schema['group']);
    }

    public function testParentPropertiesPassThrough(): void
    {
        $attribute = new RelationField(
            reference: 'users',
            label: 'Author',
            group: 'metadata',
            required: true,
        );
        $schema = $attribute->toSchemaArray();

        $this->assertSame('Author', $schema['label']);
        $this->assertSame('metadata', $schema['group']);
        $this->assertTrue($schema['required']);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(RelationField::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }
}
