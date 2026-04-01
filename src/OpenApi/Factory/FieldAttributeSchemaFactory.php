<?php

declare(strict_types=1);

namespace PsychedCms\Core\OpenApi\Factory;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use PsychedCms\Core\Attribute\ContentType;
use PsychedCms\Core\Attribute\ContentTypeAttributeInterface;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use PsychedCms\Core\Content\EntityInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Serializer\Annotation\SerializedName;

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
        $shortClassName = $reflectionClass->getShortName();

        foreach ($definitions as $definitionName => $definition) {
            // Only apply metadata to definitions that belong to this exact class.
            // e.g., "Post.jsonld-..." should match Post, but "EventReport-..." must NOT match Event.
            if (!$this->definitionBelongsToClass($definitionName, $shortClassName)) {
                continue;
            }

            // Add ContentType metadata at schema root level (for all entities with ContentType attribute)
            if ($contentTypeAttribute !== null && is_a($className, EntityInterface::class, true)) {
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
                // Use SerializedName if present (matches OpenAPI property key)
                $serializedName = $this->getSerializedName($property);
                $key = $serializedName ?? $property->getName();
                $attributes[$key] = $fieldAttribute;
            }
        }

        return $attributes;
    }

    private function getSerializedName(ReflectionProperty $property): ?string
    {
        foreach ($property->getAttributes(SerializedName::class) as $attribute) {
            return $attribute->newInstance()->getSerializedName();
        }

        return null;
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

    private function definitionBelongsToClass(string $definitionName, string $shortClassName): bool
    {
        if ($definitionName === $shortClassName) {
            return true;
        }

        // Definition names follow patterns like "Post-post.read" or "Post.jsonld-post.read"
        // Ensure the character after the class name is a separator, not a letter
        // (so "Event" does not match "EventReport")
        $len = \strlen($shortClassName);
        if (\strlen($definitionName) <= $len) {
            return false;
        }

        return \str_starts_with($definitionName, $shortClassName)
            && !\ctype_alnum($definitionName[$len]);
    }

    private function getContentTypeAttribute(ReflectionClass $reflectionClass): ?ContentTypeAttributeInterface
    {
        foreach ($reflectionClass->getAttributes(ContentType::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
