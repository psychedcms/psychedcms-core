<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class RelationField extends FieldAttribute
{
    public function __construct(
        public readonly string $reference,
        public readonly bool $multiple = false,
        public readonly string $displayField = 'name',
        public readonly bool $autocompleteSearch = true,
        public readonly bool $allowCreate = false,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?string $order = null,
        public readonly ?string $filter = null,
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
        return 'relation';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = parent::toSchemaArray();

        $schema['reference'] = $this->reference;

        if ($this->multiple) {
            $schema['multiple'] = $this->multiple;
        }

        if ($this->displayField !== 'name') {
            $schema['displayField'] = $this->displayField;
        }

        if (!$this->autocompleteSearch) {
            $schema['autocompleteSearch'] = $this->autocompleteSearch;
        }

        if ($this->allowCreate) {
            $schema['allowCreate'] = $this->allowCreate;
        }

        if ($this->min !== null) {
            $schema['min'] = $this->min;
        }

        if ($this->max !== null) {
            $schema['max'] = $this->max;
        }

        if ($this->order !== null) {
            $schema['order'] = $this->order;
        }

        if ($this->filter !== null) {
            $schema['filter'] = $this->filter;
        }

        return $schema;
    }
}
