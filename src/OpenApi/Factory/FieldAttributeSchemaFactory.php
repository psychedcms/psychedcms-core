<?php

declare(strict_types=1);

namespace PsychedCms\Core\OpenApi\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
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
        $fieldAttributes = $this->extractFieldAttributes($reflectionClass);

        if (empty($fieldAttributes)) {
            return $schema;
        }

        foreach ($definitions as $definitionName => $definition) {
            if (!isset($definition['properties'])) {
                continue;
            }

            foreach ($definition['properties'] as $propertyName => $propertySchema) {
                if (!isset($fieldAttributes[$propertyName])) {
                    continue;
                }

                $propertySchema['x-psychedcms'] = $fieldAttributes[$propertyName]->toSchemaArray();
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
}
