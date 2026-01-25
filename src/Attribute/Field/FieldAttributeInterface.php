<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

interface FieldAttributeInterface
{
    /**
     * Returns the field type identifier for OpenAPI schema generation.
     */
    public function getFieldType(): string;

    /**
     * Returns an array representation suitable for OpenAPI x-psychedcms extension.
     *
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array;
}
