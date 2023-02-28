<?php

use PHPUnit\Framework\TestCase;
use PostTypes\Taxonomy;
use PostTypes\Columns;

class TaxonomyTest extends TestCase
{
    
    private Taxonomy $taxonomy;
    
    protected function setUp(): void
    {
        $this->taxonomy = new Taxonomy('genre');
    }

    /** @test */
    public function canCreateTaxonomy()
    {
        $this->assertInstanceOf(Taxonomy::class, $this->taxonomy);
    }

    /** @test */
    public function hasNameOnInstantiation()
    {
        $this->assertEquals('genre', $this->taxonomy->names['name']);
    }

    /** @test */
    public function hasNamesOnInstantiation()
    {
        $names = [
            'name' => 'genre',
            'singular' => 'Genre',
            'plural' => 'Genres',
            'slug' => 'genres'
        ];

        $genres = new Taxonomy($names);

        $this->assertEquals($genres->names, $names);
    }

    /** @test */
    public function hasOptionsOnInstantiation()
    {
        $this->assertEquals($this->taxonomy->options, []);
    }

    /** @test */
    public function hasCustomOptionsOnInstantiation()
    {
        $options = [
            'public' => true,
        ];

        $genres = new Taxonomy('genre', $options);

        $this->assertEquals($genres->options, $options);
    }

    /** @test */
    public function hasLabelsOnInstatiation()
    {
        $this->assertEquals($this->taxonomy->labels, []);
    }

    /** @test */
    public function hasCustomLabelsOnInstantiation()
    {
        $labels = [
            'name' => 'Genres',
            'add_new' => 'Add New Genre'
        ];

        $genres = new Taxonomy('genre', [], $labels);

        $this->assertEquals($genres->labels, $labels);
    }

    /** @test */
    public function posttypesEmptyOnInstantiation()
    {
        $this->assertEquals($this->taxonomy->posttypes, []);
    }

    /** @test */
    public function hasCustomPosttypesWhenAssigned()
    {
        $genres = new Taxonomy('genre');

        $genres->posttype('books');

        $this->assertEquals($genres->posttypes, ['books']);
    }

    /** @test */
    public function canAddMultiplePostTypes()
    {
        $genres = new Taxonomy('genre');

        $genres->posttype(['books', 'films']);

        $this->assertEquals($genres->posttypes, ['books', 'films']);
    }

    /** @test */
    public function namesCreatedFromName()
    {
        $this->taxonomy->createNames();

        $this->assertEquals($this->taxonomy->name, 'genre');
        $this->assertEquals($this->taxonomy->singular, 'Genre');
        $this->assertEquals($this->taxonomy->plural, 'Genres');
        $this->assertEquals($this->taxonomy->slug, 'genres');
    }

    /** @test */
    public function passedNamesAreUsed()
    {
        $names = [
            'name' => 'genre',
            'singular' => 'Single Genre',
            'plural' => 'Multiple Genres',
            'slug' => 'slug-genres',
        ];

        $this->taxonomy->names($names);

        $this->taxonomy->createNames();

        $this->assertEquals($this->taxonomy->name, 'genre');
        $this->assertEquals($this->taxonomy->singular, 'Single Genre');
        $this->assertEquals($this->taxonomy->plural, 'Multiple Genres');
        $this->assertEquals($this->taxonomy->slug, 'slug-genres');
    }

    /** @test */
    public function defaultOptionsUsedIfNotSet()
    {
        // generated options
        $options = $this->taxonomy->createOptions();

        // expected options
        $defaults = [
            'hierarchical' => true,
            'show_admin_column' => true,
            'labels' => $this->taxonomy->createLabels(),
            'rewrite' => [
                'slug' => $this->taxonomy->slug,
            ],
        ];

        $this->assertEquals($options, $defaults);
    }

    /** @test */
    public function columnsIsEmptyOnInstantiation()
    {
        $this->assertInstanceOf(Columns::class, $this->taxonomy->columns);
        $this->assertEmpty($this->taxonomy->columns->items);
    }

    /** @test */
    public function columnsReturnsInstanceOfColumns()
    {
        $this->assertInstanceOf(Columns::class, $this->taxonomy->columns());
    }

    /** @test */
    public function capabilitiesEmptyOnInstantiation()
    {
        $this->assertEmpty($this->taxonomy->capabilities);
    }

    /** @test */
    public function hasCapabilitiesWhenSet()
    {

        $this->taxonomy->capabilities();
        $defaultCapabilities = [
            'manage_terms'	=>	'manage_'.$this->taxonomy->slug,
            'edit_terms'	=>	'edit_'.$this->taxonomy->slug,
            'delete_terms'	=>	'delete'.$this->taxonomy->slug,
            'assign_terms'	=>	'assign_'.$this->taxonomy->slug,
        ];

        // Default version set with only
        $this->assertEquals($this->taxonomy->capabilities, $defaultCapabilities);

        // Test if added to global options array
        $this->assertEquals($this->taxonomy->createOptions()['capabilities'], $defaultCapabilities);

        // Test with custom capabilities passed
        $customCapabilities = [
            'manage_terms'	=>	'manage_'.$this->taxonomy->slug. '-123',
            'edit_terms'	=>	'edit_'.$this->taxonomy->slug. '-123',
            'delete_terms'	=>	'delete'.$this->taxonomy->slug. '-123',
            'assign_terms'	=>	'assign_'.$this->taxonomy->slug. '-123',
        ];

        $this->taxonomy->capabilities($customCapabilities);
        $this->assertEquals($this->taxonomy->capabilities, $customCapabilities);
    }
}
