<?php

declare(strict_types=1);

namespace PsychedCms\Core\Tests\Attribute;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Attribute\ContentType;
use PsychedCms\Core\Attribute\ContentTypeAttributeInterface;

final class ContentTypeTest extends TestCase
{
    public function testImplementsContentTypeAttributeInterface(): void
    {
        $attribute = new ContentType();

        $this->assertInstanceOf(ContentTypeAttributeInterface::class, $attribute);
    }

    public function testDefaultValuesAreDerivesFromClassName(): void
    {
        $attribute = new ContentType();
        $result = $attribute->toSchemaArray(Article::class);

        $this->assertSame('Articles', $result['name']);
        $this->assertSame('Article', $result['singularName']);
        $this->assertSame('articles', $result['slug']);
        $this->assertSame('article', $result['singularSlug']);
    }

    public function testIrregularPluralCategory(): void
    {
        $attribute = new ContentType();
        $result = $attribute->toSchemaArray(Category::class);

        $this->assertSame('Categories', $result['name']);
        $this->assertSame('Category', $result['singularName']);
        $this->assertSame('categories', $result['slug']);
        $this->assertSame('category', $result['singularSlug']);
    }

    public function testIrregularPluralPerson(): void
    {
        $attribute = new ContentType();
        $result = $attribute->toSchemaArray(Person::class);

        $this->assertSame('People', $result['name']);
        $this->assertSame('Person', $result['singularName']);
        $this->assertSame('people', $result['slug']);
        $this->assertSame('person', $result['singularSlug']);
    }

    public function testCamelCaseSlugGeneration(): void
    {
        $attribute = new ContentType();
        $result = $attribute->toSchemaArray(BlogPost::class);

        $this->assertSame('BlogPosts', $result['name']);
        $this->assertSame('BlogPost', $result['singularName']);
        $this->assertSame('blog-posts', $result['slug']);
        $this->assertSame('blog-post', $result['singularSlug']);
    }

    public function testExplicitValuesOverrideDefaults(): void
    {
        $attribute = new ContentType(
            name: 'Custom Articles',
            singularName: 'Custom Article',
            slug: 'custom-articles',
            singularSlug: 'custom-article',
            icon: 'fa-newspaper',
            showOnDashboard: false,
            defaultStatus: 'published',
            searchable: false,
            singleton: true,
            locales: ['en', 'fr'],
        );
        $result = $attribute->toSchemaArray(Article::class);

        $this->assertSame('Custom Articles', $result['name']);
        $this->assertSame('Custom Article', $result['singularName']);
        $this->assertSame('custom-articles', $result['slug']);
        $this->assertSame('custom-article', $result['singularSlug']);
        $this->assertSame('fa-newspaper', $result['icon']);
        $this->assertFalse($result['showOnDashboard']);
        $this->assertSame('published', $result['defaultStatus']);
        $this->assertFalse($result['searchable']);
        $this->assertTrue($result['singleton']);
        $this->assertSame(['en', 'fr'], $result['locales']);
    }

    public function testSchemaArrayStructure(): void
    {
        $attribute = new ContentType(icon: 'fa-file');
        $result = $attribute->toSchemaArray(Article::class);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('singularName', $result);
        $this->assertArrayHasKey('slug', $result);
        $this->assertArrayHasKey('singularSlug', $result);
        $this->assertArrayHasKey('icon', $result);
        $this->assertArrayHasKey('showOnDashboard', $result);
        $this->assertArrayHasKey('defaultStatus', $result);
        $this->assertArrayHasKey('searchable', $result);
        $this->assertArrayHasKey('singleton', $result);
        $this->assertArrayHasKey('locales', $result);

        $this->assertSame('fa-file', $result['icon']);
        $this->assertTrue($result['showOnDashboard']);
        $this->assertSame('draft', $result['defaultStatus']);
        $this->assertTrue($result['searchable']);
        $this->assertFalse($result['singleton']);
        $this->assertSame(['en'], $result['locales']);
    }
}

class Article
{
}

class Category
{
}

class Person
{
}

class BlogPost
{
}
