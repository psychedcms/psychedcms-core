<?php

declare(strict_types=1);

namespace PsychedCms\Core\Serializer\Normalizer;

use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use PsychedCms\Core\Attribute\Field\FieldAttributeInterface;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AdminEditableGroupsDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private const ALREADY_CALLED = 'admin_editable_groups_denormalizer_already_called';
    private const ADMIN_EDITABLE_GROUPS = ['meta'];

    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::ALREADY_CALLED] = true;

        if (
            !is_array($data)
            || !class_exists($type)
            || $this->security->isGranted('PERMISSION_CONTENT_PUBLISH')
            || !$this->isWriteOperation($context)
        ) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        $metaGroupFields = $this->extractMetaGroupFields(new ReflectionClass($type));

        foreach ($data as $key => $value) {
            if (isset($metaGroupFields[$key])) {
                unset($data[$key]);
            }
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

    private function isWriteOperation(array $context): bool
    {
        $operation = $context['operation'] ?? null;

        return $operation instanceof Put || $operation instanceof Patch;
    }

    /**
     * @return array<string, true>
     */
    private function extractMetaGroupFields(ReflectionClass $reflectionClass): array
    {
        $fields = [];

        foreach ($reflectionClass->getProperties() as $property) {
            $attribute = $this->getFieldAttribute($property);
            if ($attribute !== null && in_array($attribute->group, self::ADMIN_EDITABLE_GROUPS, true)) {
                $fields[$property->getName()] = true;
            }
        }

        return $fields;
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
