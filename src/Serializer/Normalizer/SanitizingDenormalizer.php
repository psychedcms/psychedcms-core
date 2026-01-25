<?php

declare(strict_types=1);

namespace PsychedCms\Core\Serializer\Normalizer;

use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class SanitizingDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public const ALREADY_CALLED = 'sanitizing_denormalizer_already_called';

    public function __construct(
        private readonly HtmlSanitizerInterface $sanitizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (!is_array($data) || !class_exists($type)) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        $reflectionClass = new ReflectionClass($type);
        $fieldAttributes = $this->extractFieldAttributes($reflectionClass);

        foreach ($data as $propertyName => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (!isset($fieldAttributes[$propertyName])) {
                continue;
            }

            $attribute = $fieldAttributes[$propertyName];

            if (!$attribute->sanitise) {
                continue;
            }

            $data[$propertyName] = $this->sanitizeValue($value, $attribute);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return class_exists($type);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => false,
        ];
    }

    private function sanitizeValue(string $value, FieldAttributeInterface $attribute): string
    {
        if ($attribute->allowHtml === true) {
            return $this->sanitizer->sanitize($value);
        }

        return strip_tags($value);
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
