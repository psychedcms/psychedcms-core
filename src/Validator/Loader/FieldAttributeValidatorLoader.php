<?php

declare(strict_types=1);

namespace PsychedCms\Core\Validator\Loader;

use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\LoaderInterface;

final class FieldAttributeValidatorLoader implements LoaderInterface
{
    public function loadClassMetadata(ClassMetadata $metadata): bool
    {
        $className = $metadata->getClassName();

        if (!class_exists($className)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($className);
        $constraintsAdded = false;

        foreach ($reflectionClass->getProperties() as $property) {
            $fieldAttribute = $this->getFieldAttribute($property);

            if ($fieldAttribute === null) {
                continue;
            }

            if ($fieldAttribute->pattern === null) {
                continue;
            }

            $label = $fieldAttribute->label ?? $property->getName();
            $constraint = new Regex(
                pattern: $fieldAttribute->pattern,
                message: sprintf('The "%s" field does not match the required pattern.', $label)
            );

            $metadata->addPropertyConstraint($property->getName(), $constraint);
            $constraintsAdded = true;
        }

        return $constraintsAdded;
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
