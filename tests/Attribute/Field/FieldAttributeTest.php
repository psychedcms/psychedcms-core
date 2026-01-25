<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute\Field;

use Attribute;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\Field\FieldAttribute;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;

final class FieldAttributeTest extends TestCase
{
    public function testImplementsFieldAttributeInterface(): void
    {
        $attribute = new FieldAttribute();

        $this->assertInstanceOf(FieldAttributeInterface::class, $attribute);
    }

    public function testGetFieldTypeReturnsField(): void
    {
        $attribute = new FieldAttribute();

        $this->assertSame('field', $attribute->getFieldType());
    }

    public function testToSchemaArrayReturnsCorrectStructure(): void
    {
        $attribute = new FieldAttribute(
            label: 'Test Label',
            group: 'content',
            placeholder: 'Enter text',
            info: 'Help text',
            prefix: '$',
            postfix: 'USD',
            separator: true,
            class: 'custom-class',
            default: 'default value',
            required: true,
            readonly: true,
            pattern: '/^[A-Z]+$/',
            index: true,
            searchable: true,
            translatable: true,
            sanitise: true,
            allowHtml: false,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertSame('field', $schema['type']);
        $this->assertSame('Test Label', $schema['label']);
        $this->assertSame('content', $schema['group']);
        $this->assertSame('Enter text', $schema['placeholder']);
        $this->assertSame('Help text', $schema['info']);
        $this->assertSame('$', $schema['prefix']);
        $this->assertSame('USD', $schema['postfix']);
        $this->assertTrue($schema['separator']);
        $this->assertSame('custom-class', $schema['class']);
        $this->assertSame('default value', $schema['default']);
        $this->assertTrue($schema['required']);
        $this->assertTrue($schema['readonly']);
        $this->assertSame('/^[A-Z]+$/', $schema['pattern']);
        $this->assertTrue($schema['index']);
        $this->assertTrue($schema['searchable']);
        $this->assertTrue($schema['translatable']);
        $this->assertTrue($schema['sanitise']);
        $this->assertFalse($schema['allowHtml']);
    }

    public function testToSchemaArrayFiltersNullAndDefaultFalseValues(): void
    {
        $attribute = new FieldAttribute(
            label: 'Test',
            required: true,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('type', $schema);
        $this->assertArrayHasKey('label', $schema);
        $this->assertArrayHasKey('required', $schema);
        $this->assertArrayNotHasKey('group', $schema);
        $this->assertArrayNotHasKey('placeholder', $schema);
        $this->assertArrayNotHasKey('info', $schema);
        $this->assertArrayNotHasKey('prefix', $schema);
        $this->assertArrayNotHasKey('postfix', $schema);
        $this->assertArrayNotHasKey('separator', $schema);
        $this->assertArrayNotHasKey('readonly', $schema);
        $this->assertArrayNotHasKey('pattern', $schema);
        $this->assertArrayNotHasKey('index', $schema);
        $this->assertArrayNotHasKey('searchable', $schema);
        $this->assertArrayNotHasKey('translatable', $schema);
        $this->assertArrayNotHasKey('allowHtml', $schema);
    }

    public function testToSchemaArrayIncludesExplicitAllowHtmlFalse(): void
    {
        $attribute = new FieldAttribute(
            allowHtml: false,
        );

        $schema = $attribute->toSchemaArray();

        $this->assertArrayHasKey('allowHtml', $schema);
        $this->assertFalse($schema['allowHtml']);
    }

    public function testConstructorDefaultsAreAppliedCorrectly(): void
    {
        $attribute = new FieldAttribute();

        $this->assertNull($attribute->label);
        $this->assertNull($attribute->group);
        $this->assertNull($attribute->placeholder);
        $this->assertNull($attribute->info);
        $this->assertNull($attribute->prefix);
        $this->assertNull($attribute->postfix);
        $this->assertFalse($attribute->separator);
        $this->assertNull($attribute->class);
        $this->assertNull($attribute->default);
        $this->assertFalse($attribute->required);
        $this->assertFalse($attribute->readonly);
        $this->assertNull($attribute->pattern);
        $this->assertFalse($attribute->index);
        $this->assertFalse($attribute->searchable);
        $this->assertFalse($attribute->translatable);
        $this->assertTrue($attribute->sanitise);
        $this->assertNull($attribute->allowHtml);
    }

    public function testAttributeTargetsPropertyOnly(): void
    {
        $reflection = new ReflectionClass(FieldAttribute::class);
        $attributes = $reflection->getAttributes(Attribute::class);

        $this->assertCount(1, $attributes);

        $attributeInstance = $attributes[0]->newInstance();
        $this->assertSame(Attribute::TARGET_PROPERTY, $attributeInstance->flags);
    }

    public function testReadonlyPropertiesAreSetViaConstructorPromotion(): void
    {
        $reflection = new ReflectionClass(FieldAttribute::class);

        $properties = ['label', 'group', 'placeholder', 'info', 'prefix', 'postfix', 'separator', 'class', 'default', 'required', 'readonly', 'pattern', 'index', 'searchable', 'translatable', 'sanitise', 'allowHtml'];

        foreach ($properties as $propertyName) {
            $property = $reflection->getProperty($propertyName);
            $this->assertTrue(
                $property->isReadOnly(),
                "Property '{$propertyName}' should be readonly"
            );
        }
    }
}
