<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute;

interface ContentTypeAttributeInterface
{
    /**
     * Returns an array representation suitable for OpenAPI x-psychedcms extension.
     *
     * @param class-string $className Used to derive defaults from class name
     * @return array<string, mixed>
     */
    public function toSchemaArray(string $className): array;
}
