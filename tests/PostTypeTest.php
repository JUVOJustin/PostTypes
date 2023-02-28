<?php

use PHPUnit\Framework\TestCase;
use PostTypes\PostType;
use PostTypes\Columns;

class PostTypeTest extends TestCase
{
    
    private PostType $postType;
    
    protected function setUp(): void
    {
        // setup basic PostType
        $this->postType = new PostType('book');
    }

    /** @test */
    public function canCreatePostType()
    {
        $this->assertInstanceOf(PostType::class, $this->postType);
    }

    /** @test */
    public function hasNameOnInstantiation()
    {
        $this->assertEquals($this->postType->names['name'], 'book');
    }

    /** @test */
    public function hasNamesOnInstantiation()
    {
        $names = [
            'name'     => 'book',
            'singular' => 'Book',
            'plural'   => 'Books',
            'slug'     => 'books'
        ];

        $books = new PostType($names);

        $this->assertEquals($books->names, $names);
    }

    /** @test */
    public function hasOptionsOnInstantiation()
    {
        $this->assertEquals($this->postType->options, []);
    }

    /** @test */
    public function hasCustomOptionsOnInstantiation()
    {
        $options = [
            'public' => true
        ];

        $books = new PostType('books', $options);

        $this->assertEquals($books->options, $options);
    }

    /** @test */
    public function hasLabelsOnInstantiation()
    {
        $this->assertEquals($this->postType->labels, []);
    }

    /** @test */
    public function hasCustomLabelsOnInstantiation()
    {
        $labels = [
            'name'    => 'Books',
            'add_new' => 'Add New Book'
        ];

        $books = new PostType('books', [], $labels);

        $this->assertEquals($books->labels, $labels);
    }

    /** @test */
    public function taxonomiesEmptyOnInstantiation()
    {
        $this->assertEquals($this->postType->taxonomies, []);
    }

    /** @test */
    public function hasCustomTaxonomiesWhenPassed()
    {
        $books = $this->postType;

        $books->taxonomy('genre');

        $this->assertEquals($books->taxonomies, ['genre']);
    }

    /** @test */
    public function canAddMultipleTaxonomies()
    {
        $books = $this->postType;

        $books->taxonomy(['genre', 'publisher']);

        $this->assertEquals($books->taxonomies, ['genre', 'publisher']);
    }

    /** @test */
    public function filtersNullOnInstantiation()
    {
        $this->assertNull($this->postType->filters);
    }

    /** @test */
    public function hasFiltersWhenAdded()
    {
        $books = $this->postType;

        $books->filters(['genre']);

        $this->assertEquals($books->filters, ['genre']);
    }

    /** @test */
    public function iconNullOnInstantiation()
    {
        $this->assertNull($this->postType->icon);
    }

    /** @test */
    public function hasIconWhenSet()
    {
        $books = $this->postType;

        $books->icon('dashicon-book-alt');

        $this->assertEquals($books->icon, 'dashicon-book-alt');
    }

    /** @test */
    public function capabilitiesNullOnInstantiation()
    {
        $this->assertNull($this->postType->capabilities);
    }

    /** @test */
    public function hasCapabilitiesWhenSet()
    {

        $this->postType->capabilities();
        $singular = strtolower($this->postType->singular);
        $defaultCapabilities = [
            "edit_post"          => "edit_$singular",
            "read_post"          => "read_$singular",
            "delete_post"        => "delete_$singular",
            "edit_posts"         => "edit_{$this->postType->slug}",
            "edit_others_posts"  => "edit_others_{$this->postType->slug}",
            "publish_posts"      => "publish_{$this->postType->slug}",
            "read_private_posts" => "read_private_{$this->postType->slug}",
            "create_posts"       => "edit_{$this->postType->slug}",
        ];

        // Default version set with only
        $this->assertEquals($this->postType->capabilities, $defaultCapabilities);

        // Test if added to global options array
        $this->assertEquals($this->postType->createOptions()['capabilities'], $defaultCapabilities);

        // Test with custom capabilities passed
        $customCapabilities = [
            "edit_post"          => "edit_{$singular}123",
            "read_post"          => "read_{$singular}123",
            "delete_post"        => "delete_{$singular}123",
            "edit_posts"         => "edit_{$this->postType->slug}123",
            "edit_others_posts"  => "edit_others_{$this->postType->slug}123",
            "publish_posts"      => "publish_{$this->postType->slug}123",
            "read_private_posts" => "read_private_{$this->postType->slug}123",
            "create_posts"       => "edit_{$this->postType->slug}123",
        ];

        $this->postType->capabilities($customCapabilities);
        $this->assertEquals($this->postType->capabilities, $customCapabilities);
    }

    /** @test */
    public function columnsIsNullOnInstantiation()
    {
        $this->assertEquals($this->postType->columns, null);
    }

    /** @test */
    public function columnsReturnsInstanceOfColumns()
    {
        $this->assertInstanceOf(Columns::class, $this->postType->columns());
    }

    /** @test */
    public function namesCreatedFromName()
    {
        $this->postType->createNames();

        $this->assertEquals($this->postType->name, 'book');
        $this->assertEquals($this->postType->singular, 'Book');
        $this->assertEquals($this->postType->plural, 'Books');
        $this->assertEquals($this->postType->slug, 'books');
    }

    /** @test */
    public function passedNamesAreUsed()
    {
        $names = [
            'name'     => 'book',
            'singular' => 'Single Book',
            'plural'   => 'Multiple Books',
            'slug'     => 'slug_books',
        ];

        $this->postType->names($names);

        $this->postType->createNames();

        $this->assertEquals($this->postType->name, 'book');
        $this->assertEquals($this->postType->singular, 'Single Book');
        $this->assertEquals($this->postType->plural, 'Multiple Books');
        $this->assertEquals($this->postType->slug, 'slug_books');
    }

    /** @test */
    public function defaultOptionsUsedIfNotSet()
    {
        // generated options
        $options = $this->postType->createOptions();

        // expected options
        $defaults = [
            'public'  => true,
            'labels'  => $this->postType->createLabels(),
            'rewrite' => [
                'slug' => $this->postType->slug
            ]
        ];

        $this->assertEquals($options, $defaults);
    }

    /** @test */
    public function defaultLabelsAreGenerated()
    {
        $labels = $this->postType->createLabels();

        $defaults = [
            'name'               => $this->postType->plural,
            'singular_name'      => $this->postType->singular,
            'menu_name'          => $this->postType->plural,
            'all_items'          => $this->postType->plural,
            'add_new'            => "Add New",
            'add_new_item'       => "Add New {$this->postType->singular}",
            'edit_item'          => "Edit {$this->postType->singular}",
            'new_item'           => "New {$this->postType->singular}",
            'view_item'          => "View {$this->postType->singular}",
            'search_items'       => "Search {$this->postType->plural}",
            'not_found'          => "No {$this->postType->plural} found",
            'not_found_in_trash' => "No {$this->postType->plural} found in Trash",
            'parent_item_colon'  => "Parent {$this->postType->singular}:",
        ];

        $this->assertEquals($labels, $defaults);
    }

    /** @test */
    public function filtersAreEmptyIfNotSetAndNoTaxonomies()
    {
        $filters = $this->postType->getFilters();

        $this->assertEquals($filters, []);
    }

    /** @test */
    public function filtersAreSameAsTaxonomyIfNotSet()
    {
        $this->postType->taxonomy('genre');

        $filters = $this->postType->getFilters();

        $this->assertEquals($filters, ['genre']);
    }

    /** @test */
    public function filtersAreWhatAssignedIfPassed()
    {
        $this->postType->filters(['genre', 'published']);

        $this->postType->taxonomy('genre');

        $filters = $this->postType->getFilters();

        $this->assertEquals($filters, ['genre', 'published']);
    }

    /** @test */
    public function filtersAreEmptyIfSetWithEmptyArray()
    {
        $this->postType->filters([]);

        $this->postType->taxonomy('genre');

        $filters = $this->postType->getFilters();

        $this->assertEquals($filters, []);
    }
}
