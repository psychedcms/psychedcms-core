<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class CollectionField extends FieldAttribute
{
    /**
     * @param array<string, string|array<string, mixed>> $schema Sub-field definitions
     */
    public function __construct(
        public readonly array $schema = [],
        ?string $label = null,
        ?string $group = null,
        ?string $info = null,
        bool $required = false,
        bool $readonly = false,
        ?bool $listColumn = null,
        ?int $listColumnOrder = null,
        ?string $listDisplayPattern = null,
        bool $listSortable = false,
        bool $listFilterable = false,
        ?string $listFilterType = null,
    ) {
        parent::__construct(
            label: $label,
            group: $group,
            info: $info,
            required: $required,
            readonly: $readonly,
            listColumn: $listColumn,
            listColumnOrder: $listColumnOrder,
            listDisplayPattern: $listDisplayPattern,
            listSortable: $listSortable,
            listFilterable: $listFilterable,
            listFilterType: $listFilterType,
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
        $schema['schema'] = $this->schema;

        return $schema;
    }
}
