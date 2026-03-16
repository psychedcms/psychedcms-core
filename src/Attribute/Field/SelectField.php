<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SelectField extends FieldAttribute
{
    /**
     * @param array<int|string, string>|string $values
     */
    public function __construct(
        public readonly array|string $values,
        public readonly bool $multiple = false,
        public readonly bool $sortable = false,
        public readonly bool $autocomplete = false,
        public readonly ?int $limit = null,
        public readonly ?string $sort = null,
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
        ?bool $listColumn = null,
        ?int $listColumnOrder = null,
        ?string $listDisplayPattern = null,
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
            listColumn: $listColumn,
            listColumnOrder: $listColumnOrder,
            listDisplayPattern: $listDisplayPattern,
        );
    }

    public function getFieldType(): string
    {
        return 'select';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = parent::toSchemaArray();

        $schema['values'] = $this->values;

        if ($this->multiple) {
            $schema['multiple'] = $this->multiple;
        }

        if ($this->sortable) {
            $schema['sortable'] = $this->sortable;
        }

        if ($this->autocomplete) {
            $schema['autocomplete'] = $this->autocomplete;
        }

        if ($this->limit !== null) {
            $schema['limit'] = $this->limit;
        }

        if ($this->sort !== null) {
            $schema['sort'] = $this->sort;
        }

        return $schema;
    }
}
