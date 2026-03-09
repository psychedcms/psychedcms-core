<?php

declare(strict_types=1);

namespace PsychedCms\Core\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

final class ValidCollection extends Constraint
{
    public string $invalidTypeMessage = 'The collection field must be an array.';
    public string $tooFewItemsMessage = 'The collection must contain at least {{ min }} item(s).';
    public string $tooManyItemsMessage = 'The collection must contain at most {{ max }} item(s).';
    public string $invalidItemMessage = 'Item #{{ index }} is not a valid associative array.';
    public string $unexpectedKeyMessage = 'Item #{{ index }} contains unexpected key "{{ key }}".';
    public string $missingKeyMessage = 'Item #{{ index }} is missing required key "{{ key }}".';
    public string $invalidFieldTypeMessage = 'Item #{{ index }}, field "{{ field }}": expected {{ expected }}, got {{ actual }}.';

    /**
     * @param array<string, string|array<string, mixed>> $schema
     */
    public function __construct(
        public readonly array $schema = [],
        public readonly int $min = 0,
        public readonly int $max = 0,
        mixed $options = null,
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
