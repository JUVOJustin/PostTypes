<?php

namespace PostTypes;

/**
 * PostType
 *
 * Create WordPress custom post types easily
 *
 * @link    https://github.com/jjgrainger/PostTypes/
 * @author  jjgrainger
 * @link    https://jjgrainger.co.uk
 * @version 2.0
 * @license https://opensource.org/licenses/mit-license.html MIT License
 */
class PostType extends Base
{

    /**
     * Taxonomies for the PostType
     * @var array
     */
    public $taxonomies = [];

    /**
     * Filters for the PostType
     * @var mixed
     */
    public $filters;

    /**
     * The menu icon for the PostType
     * @var string
     */
    public $icon;

    /**
     * Add a Taxonomy to the PostType
     * @param mixed $taxonomies The Taxonomy name(s) to add
     * @return $this
     */
    public function taxonomy($taxonomies): PostType
    {
        $taxonomies = is_string($taxonomies) ? [$taxonomies] : $taxonomies;

        foreach ($taxonomies as $taxonomy) {
            $this->taxonomies[] = $taxonomy;
        }

        return $this;
    }

    /**
     * Add filters to the PostType
     * @param array $filters An array of Taxonomy filters
     * @return $this
     */
    public function filters(array $filters): PostType
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set the menu icon for the PostType
     * @param string $icon A dashicon class for the menu icon
     * @return $this
     */
    public function icon($icon): PostType
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Flush rewrite rules
     * @link https://codex.wordpress.org/Function_Reference/flush_rewrite_rules
     * @param boolean $hard
     * @return void
     */
    public function flush(bool $hard = true)
    {
        flush_rewrite_rules($hard);
    }

    /**
     * Register the PostType to WordPress
     * @return void
     */
    public function register()
    {
        // register the PostType
        if (!post_type_exists($this->name)) {
            add_action('init', [$this, 'registerPostType']);
        } else {
            add_filter('register_post_type_args', [$this, 'modifyPostType'], 10, 2);
        }

        // Add capabilities to roles
        add_action('init', [$this, 'grantCapabilities']);

        // register Taxonomies to the PostType
        add_action('init', [$this, 'registerTaxonomies']);

        // modify filters on the admin edit screen
        add_action('restrict_manage_posts', [$this, 'modifyFilters']);

        if (isset($this->columns)) {
            // modify the admin edit columns.
            add_filter("manage_{$this->name}_posts_columns", [$this, 'modifyColumns'], 10, 1);

            // populate custom columns
            add_filter("manage_{$this->name}_posts_custom_column", [$this, 'populateColumns'], 10, 2);

            // run filter to make columns sortable.
            add_filter('manage_edit-' . $this->name . '_sortable_columns', [$this, 'setSortableColumns']);

            // run action that sorts columns on request.
            add_action('pre_get_posts', [$this, 'sortSortableColumns']);
        }
    }

    /**
     * Register the PostType
     * @return void
     */
    public function registerPostType()
    {
        // create options for the PostType
        $options = $this->createOptions();

        // check that the post type doesn't already exist
        if (!post_type_exists($this->name)) {
            // register the post type
            register_post_type($this->name, $options);
        }
    }

    /**
     * Modify the existing Post Type.
     *
     * @param array $args
     * @param string $posttype
     * @return array
     */
    public function modifyPostType(array $args, string $posttype): array
    {
        if ($posttype !== $this->name) {
            return $args;
        }

        // create options for the PostType
        $options = $this->createOptions();

        $args = array_replace_recursive($args, $options);

        return $args;
    }

    /**
     * Create the required names for the PostType
     * @return void
     */
    public function createNames(): void
    {
        // names required for the PostType
        $required = [
            'name',
            'singular',
            'plural',
            'slug',
        ];

        foreach ($required as $key) {
            $name = $this->names[$key] ?? "";

            // If attribute already set skip it
            if (!empty($name)) {
                $this->$key = $name;
                continue;
            }

            // if the key is not set and is singular or plural
            if (in_array($key, ['singular', 'plural'])) {
                // create a human friendly name
                $name = ucwords(strtolower(str_replace(['-', '_'], ' ', $this->names['name'])));
            } elseif ($key === 'slug') {
                // create a slug friendly name
                $name = strtolower(str_replace([' ', '_'], '-', $this->names['name']));
            }

            // if is plural or slug, append an 's'
            if (in_array($key, ['plural', 'slug'])) {
                if (substr($name, strlen($name) - 1, 1) == "y") {
                    $name = substr($name, 0, strlen($name) - 1) . "ies";
                } else {
                    $name .= 's';
                }
            }

            // asign the name to the PostType property
            $this->$key = $name;
        }
    }

    /**
     * Create options for PostType
     * @return array Options to pass to register_post_type
     */
    public function createOptions(): array
    {
        // default options
        $options = [
            'public'  => true,
            'rewrite' => [
                'slug' => $this->slug
            ]
        ];

        // replace defaults with the options passed
        $options = array_replace_recursive($options, $this->options);

        // create and set labels
        if (!isset($options['labels'])) {
            $options['labels'] = $this->createLabels();
        }

        // set the menu icon
        if (!isset($options['menu_icon']) && !empty($this->icon)) {
            $options['menu_icon'] = $this->icon;
        }

        // set capabilities
        if (!isset($options['capabilities']) && !empty($this->capabilities)) {
            $options['capabilities'] = $this->capabilities;
        }

        return $options;
    }

    public function capabilities(array $capabilities = [], array $whitelisted_roles = [])
    {

        if (!empty($capabilities)) {
            $this->capabilities = $capabilities;
        } else {
            $singular = strtolower($this->singular);
            $this->capabilities = [
                "edit_post"          => "edit_$singular",
                "read_post"          => "read_$singular",
                "delete_post"        => "delete_$singular",
                "edit_posts"         => "edit_{$this->slug}",
                "edit_others_posts"  => "edit_others_{$this->slug}",
                "publish_posts"      => "publish_{$this->slug}",
                "read_private_posts" => "read_private_{$this->slug}",
                "create_posts"       => "edit_{$this->slug}",
            ];
        }

        // Set whitelisted roles to use later in init action
        $this->whitelisted_roles = $whitelisted_roles;
    }

    /**
     * Create the labels for the PostType
     * @return array
     */
    public function createLabels(): array
    {
        // default labels
        $labels = [
            'name'               => $this->plural,
            'singular_name'      => $this->singular,
            'menu_name'          => $this->plural,
            'all_items'          => $this->plural,
            'add_new'            => "Add New",
            'add_new_item'       => "Add New {$this->singular}",
            'edit_item'          => "Edit {$this->singular}",
            'new_item'           => "New {$this->singular}",
            'view_item'          => "View {$this->singular}",
            'search_items'       => "Search {$this->plural}",
            'not_found'          => "No {$this->plural} found",
            'not_found_in_trash' => "No {$this->plural} found in Trash",
            'parent_item_colon'  => "Parent {$this->singular}:",
        ];

        return array_replace_recursive($labels, $this->labels);
    }

    /**
     * Register Taxonomies to the PostType
     * @return void
     */
    public function registerTaxonomies()
    {
        if (!empty($this->taxonomies)) {
            foreach ($this->taxonomies as $taxonomy) {
                register_taxonomy_for_object_type($taxonomy, $this->name);
            }
        }
    }

    /**
     * Modify and display filters on the admin edit screen
     * @param string $posttype The current screen post type
     * @return void
     */
    public function modifyFilters(string $posttype)
    {
        // first check we are working with the this PostType
        if ($posttype === $this->name) {
            // calculate what filters to add
            $filters = $this->getFilters();

            foreach ($filters as $taxonomy) {
                // if the taxonomy doesn't exist, ignore it
                if (!taxonomy_exists($taxonomy)) {
                    continue;
                }

                // If the taxonomy is not registered to the post type, continue.
                if (!is_object_in_taxonomy($this->name, $taxonomy)) {
                    continue;
                }

                // get the taxonomy object
                $tax = get_taxonomy($taxonomy);

                // start the html for the filter dropdown
                $selected = null;

                if (isset($_GET[$taxonomy])) {
                    $selected = sanitize_title($_GET[$taxonomy]);
                }

                $dropdown_args = [
                    'name'            => $taxonomy,
                    'value_field'     => 'slug',
                    'taxonomy'        => $tax->name,
                    'show_option_all' => $tax->labels->all_items,
                    'hierarchical'    => $tax->hierarchical,
                    'selected'        => $selected,
                    'orderby'         => 'name',
                    'hide_empty'      => 0,
                    'show_count'      => 0,
                ];

                // Output screen reader label.
                echo '<label class="screen-reader-text" for="cat">' . $tax->labels->filter_by_item . '</label>';

                // Output dropdown for taxonomy.
                wp_dropdown_categories($dropdown_args);
            }
        }
    }

    /**
     * Calculate the filters for the PostType
     * @return array
     */
    public function getFilters(): array
    {
        // default filters are empty
        $filters = [];

        // if custom filters have been set, use them
        if (!is_null($this->filters)) {
            return $this->filters;
        }

        // if no custom filters have been set, and there are
        // Taxonomies assigned to the PostType
        if (!empty($this->taxonomies)) {
            // create filters for each taxonomy assigned to the PostType
            return $this->taxonomies;
        }

        return $filters;
    }

    /**
     * Populate custom columns for the PostType
     * @param string $column The column slug
     * @param int $post_id The post ID
     */
    public function populateColumns(string $column, int $post_id)
    {
        if (isset($this->columns->populate[$column])) {
            call_user_func_array($this->columns()->populate[$column], [$column, $post_id]);
        }
    }

    /**
     * Set query to sort custom columns
     * @param \WP_Query $query
     */
    public function sortSortableColumns(\WP_Query $query)
    {
        // don't modify the query if we're not in the post type admin
        if (!is_admin() || $query->get('post_type') !== $this->name) {
            return;
        }

        $orderby = $query->get('orderby');

        // if the sorting a custom column
        if ($this->columns()->isSortable($orderby)) {
            // get the custom column options
            $meta = $this->columns()->sortableMeta($orderby);

            // determine type of ordering
            if (is_string($meta) or !$meta[1]) {
                $meta_key = $meta;
                $meta_value = 'meta_value';
            } else {
                $meta_key = $meta[0];
                $meta_value = 'meta_value_num';
            }

            // set the custom order
            $query->set('meta_key', $meta_key);
            $query->set('orderby', $meta_value);
        }
    }
}
