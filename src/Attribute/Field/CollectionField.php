<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class CollectionField extends FieldAttribute
{
    /**
     * @param array<string, string|array<string, mixed>> $schema
     */
    public function __construct(
        public readonly array $schema,
        public readonly int $min = 0,
        public readonly int $max = 0,
        public readonly bool $sortable = true,
        ?string $label = null,
        ?string $group = null,
        ?string $placeholder = null,
        ?string $info = null,
        ?string $prefix = null,
        ?string $postfix = null,
        bool $separator = false,
        ?string $class = null,
        mixed $default = null,
        bool $required = false,
        bool $readonly = false,
        ?string $pattern = null,
        bool $index = false,
        bool $searchable = false,
        bool $translatable = false,
        bool $sanitise = true,
        ?bool $allowHtml = null,
    ) {
        parent::__construct(
            label: $label,
            group: $group,
            placeholder: $placeholder,
            info: $info,
            prefix: $prefix,
            postfix: $postfix,
            separator: $separator,
            class: $class,
            default: $default,
            required: $required,
            readonly: $readonly,
            pattern: $pattern,
            index: $index,
            searchable: $searchable,
            translatable: $translatable,
            sanitise: $sanitise,
            allowHtml: $allowHtml,
        );
    }

    public function getFieldType(): string
    {
        return 'collection';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = parent::toSchemaArray();

        // Normalize schema: convert simple string values to ['type' => 'text'] format
        $normalizedSchema = [];
        foreach ($this->schema as $key => $value) {
            if (is_string($value)) {
                $normalizedSchema[$key] = ['type' => $value];
            } else {
                $normalizedSchema[$key] = $value;
            }
        }
        $schema['schema'] = $normalizedSchema;

        if ($this->min > 0) {
            $schema['min'] = $this->min;
        }

        if ($this->max > 0) {
            $schema['max'] = $this->max;
        }

        if (!$this->sortable) {
            $schema['sortable'] = $this->sortable;
        }

        return $schema;
    }
}
