<?php

declare(strict_types=1);

namespace PsychedCms\Core\OpenApi\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use PsychedCms\Core\Attribute\ContentType;
use PsychedCms\Core\Attribute\ContentTypeAttributeInterface;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Content\ContentInterface;
use ReflectionClass;
use ReflectionProperty;

final class FieldAttributeSchemaFactory implements SchemaFactoryInterface
{
    public function __construct(
        private readonly SchemaFactoryInterface $decorated,
    ) {
    }

    public function buildSchema(
        string $className,
        string $format = 'json',
        string $type = Schema::TYPE_OUTPUT,
        ?Operation $operation = null,
        ?Schema $schema = null,
        ?array $serializerContext = null,
        bool $forceCollection = false,
    ): Schema {
        $schema = $this->decorated->buildSchema(
            $className,
            $format,
            $type,
            $operation,
            $schema,
            $serializerContext,
            $forceCollection
        );

        if (!class_exists($className)) {
            return $schema;
        }

        $definitions = $schema->getDefinitions();
        if ($definitions->count() === 0) {
            return $schema;
        }

        $reflectionClass = new ReflectionClass($className);
        $contentTypeAttribute = $this->getContentTypeAttribute($reflectionClass);
        $fieldAttributes = $this->extractFieldAttributes($reflectionClass);

        foreach ($definitions as $definitionName => $definition) {
            // Add ContentType metadata at schema root level (only for ContentInterface implementations)
            if ($contentTypeAttribute !== null && is_a($className, ContentInterface::class, true)) {
                $definition['x-psychedcms'] = $contentTypeAttribute->toSchemaArray($className);
            }

            // Add field attributes to properties
            if (isset($definition['properties'])) {
                foreach ($definition['properties'] as $propertyName => $propertySchema) {
                    if (!isset($fieldAttributes[$propertyName])) {
                        continue;
                    }

                    $propertySchema['x-psychedcms'] = $fieldAttributes[$propertyName]->toSchemaArray();
                }
            }
        }

        return $schema;
    }

    /**
     * @return array<string, FieldAttributeInterface>
     */
    private function extractFieldAttributes(ReflectionClass $reflectionClass): array
    {
        $attributes = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $fieldAttribute = $this->getFieldAttribute($property);
            if ($fieldAttribute !== null) {
                $attributes[$property->getName()] = $fieldAttribute;
            }
        }

        return $attributes;
    }

    private function getFieldAttribute(ReflectionProperty $property): ?FieldAttributeInterface
    {
        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof FieldAttributeInterface) {
                return $instance;
            }
        }

        return null;
    }

    private function getContentTypeAttribute(ReflectionClass $reflectionClass): ?ContentTypeAttributeInterface
    {
        foreach ($reflectionClass->getAttributes(ContentType::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
