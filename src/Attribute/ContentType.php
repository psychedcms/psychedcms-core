<?php

declare(strict_types=1);

namespace PsychedCms\Core\Attribute;

use Attribute;
use Symfony\Component\String\Inflector\EnglishInflector;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[Attribute(Attribute::TARGET_CLASS)]
final class ContentType implements ContentTypeAttributeInterface
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $singularName = null,
        public readonly ?string $slug = null,
        public readonly ?string $singularSlug = null,
        public readonly ?string $icon = null,
        public readonly bool $showOnDashboard = true,
        public readonly string $defaultStatus = 'draft',
        public readonly bool $searchable = true,
        public readonly bool $singleton = false,
        public readonly ?array $locales = null,
        public readonly ?string $group = null,
        public readonly ?string $aggregateRoot = null,
        public readonly int $priority = 0,
        public readonly ?string $listDefaultSort = null,
        public readonly ?int $listPerPage = null,
        public readonly bool $listBulkDelete = true,
    ) {
    }

    /**
     * @param class-string $className Used to derive defaults from class name
     * @return array<string, mixed>
     */
    public function toSchemaArray(string $className): array
    {
        $singularName = $this->singularName ?? $this->deriveShortName($className);
        $name = $this->name ?? $this->pluralize($singularName);
        $singularSlug = $this->singularSlug ?? $this->slugify($singularName);
        $slug = $this->slug ?? $this->slugify($name);

        return [
            'name' => $name,
            'singularName' => $singularName,
            'slug' => $slug,
            'singularSlug' => $singularSlug,
            'icon' => $this->icon,
            'showOnDashboard' => $this->showOnDashboard,
            'defaultStatus' => $this->defaultStatus,
            'searchable' => $this->searchable,
            'singleton' => $this->singleton,
            'locales' => $this->locales ?? ['en'],
            'group' => $this->group,
            'aggregateRoot' => $this->aggregateRoot,
            'priority' => $this->priority,
            'listDefaultSort' => $this->listDefaultSort,
            'listPerPage' => $this->listPerPage,
            'listBulkDelete' => $this->listBulkDelete ? null : false,
        ];
    }

    private function deriveShortName(string $className): string
    {
        $parts = explode('\\', $className);

        return end($parts);
    }

    private function pluralize(string $singular): string
    {
        $inflector = new EnglishInflector();
        $plurals = $inflector->pluralize($singular);

        // When multiple forms exist, prefer the last one (usually the irregular form)
        return end($plurals);
    }

    private function slugify(string $text): string
    {
        $wordsText = $this->camelCaseToWords($text);
        $slugger = new AsciiSlugger();

        return $slugger->slug($wordsText)->lower()->toString();
    }

    private function camelCaseToWords(string $text): string
    {
        return preg_replace('/(?<!^)([A-Z])/', ' $1', $text) ?? $text;
    }
}
