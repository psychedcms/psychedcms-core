<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldAttribute implements FieldAttributeInterface
{
    public function __construct(
        // Display options
        public readonly ?string $label = null,
        public readonly ?string $group = null,
        public readonly ?string $placeholder = null,
        public readonly ?string $info = null,
        public readonly ?string $prefix = null,
        public readonly ?string $postfix = null,
        public readonly bool $separator = false,
        public readonly ?string $class = null,

        // Behavior options
        public readonly mixed $default = null,
        public readonly bool $required = false,
        public readonly bool $readonly = false,
        public readonly ?string $pattern = null,

        // Indexing options
        public readonly bool $index = false,
        public readonly bool $searchable = false,

        // Content options
        public readonly bool $translatable = false,
        public readonly bool $sanitise = true,
        public readonly ?bool $allowHtml = null,

        // List display options
        public readonly ?bool $listColumn = null,
        public readonly ?int $listColumnOrder = null,
        public readonly ?string $listDisplayPattern = null,
    ) {
    }

    public function getFieldType(): string
    {
        return 'field';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSchemaArray(): array
    {
        $schema = [
            'type' => $this->getFieldType(),
            'label' => $this->label,
            'group' => $this->group,
            'placeholder' => $this->placeholder,
            'info' => $this->info,
            'prefix' => $this->prefix,
            'postfix' => $this->postfix,
            'separator' => $this->separator ?: null,
            'class' => $this->class,
            'default' => $this->default,
            'required' => $this->required ?: null,
            'readonly' => $this->readonly ?: null,
            'pattern' => $this->pattern,
            'index' => $this->index ?: null,
            'searchable' => $this->searchable ?: null,
            'translatable' => $this->translatable ?: null,
            'sanitise' => $this->sanitise ?: null,
            'allowHtml' => $this->allowHtml,
            'listColumn' => $this->listColumn,
            'listColumnOrder' => $this->listColumnOrder,
            'listDisplayPattern' => $this->listDisplayPattern,
        ];

        return array_filter($schema, static fn ($value) => $value !== null);
    }
}
