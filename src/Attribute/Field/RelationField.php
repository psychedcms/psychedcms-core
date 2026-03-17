<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class RelationField extends FieldAttribute
{
    public function __construct(
        public readonly ?string $reference = null,
        public readonly ?string $displayField = null,
        public readonly bool $multiple = false,
        public readonly string $display = 'autocomplete',
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
        return 'relation';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = parent::toSchemaArray();

        if ($this->reference !== null) {
            $schema['reference'] = $this->reference;
        }
        if ($this->displayField !== null) {
            $schema['displayField'] = $this->displayField;
        }
        if ($this->multiple) {
            $schema['multiple'] = true;
        }
        if ($this->display !== 'autocomplete') {
            $schema['display'] = $this->display;
        }

        return $schema;
    }
}
