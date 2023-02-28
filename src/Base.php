<?php

namespace PostTypes;

class Base
{

    /**
     * The names passed to the entity
     * @var mixed
     */
    public $names;

    /**
     * The entity name
     * @var string
     */
    public string $name;

    /**
     * The singular label for the entity
     * @var string
     */
    public string $singular;

    /**
     * The plural label for the entity
     * @var string
     */
    public string $plural;

    /**
     * The entity slug
     * @var string
     */
    public string $slug;

    /**
     * Custom options for the entity
     * @var array
     */
    public array $options;

    /**
     * Custom labels for the entity
     * @var array
     */
    public array $labels;

    /**
     * Stores user roles that should be granted capabilities of the entity
     * @var array
     */
    public array $whitelisted_roles;

    /**
     * Sets the capabilities of the entity
     * @var array
     */
    public array $capabilities = [];

    /**
     * The column manager for the Entity
     * @var Columns
     */
    public Columns $columns;

    /**
     * @param mixed $names
     * @param array $options
     * @param array $labels
     */
    public function __construct($names, array $options = [], array $labels = [])
    {
        $this->names($names);

        $this->options($options);

        $this->labels($labels);

        $this->columns = new Columns();
    }

    /**
     * Set the options for the Entity
     * @param array $options An array of options for the PostType
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set the labels for the Entity
     * @param array $labels An array of labels for the PostType
     * @return $this
     */
    public function labels(array $labels): self
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Get the Column Manager for the Entity
     * @return Columns
     */
    public function columns(): Columns
    {
        return $this->columns;
    }

    /**
     * Modify the columns for the Entity
     * @param array $columns Default WordPress columns
     * @return array            The modified columns
     */
    public function modifyColumns(array $columns): array
    {
        return $this->columns->modifyColumns($columns);
    }

    /**
     * Make custom columns sortable
     * @param array $columns Default WordPress sortable columns
     */
    public function setSortableColumns(array $columns): array
    {
        if (!empty($this->columns()->sortable)) {
            $columns = array_merge($columns, $this->columns()->sortable);
        }

        return $columns;
    }

    /**
     * Adds capabilities to user roles for entity.
     * CAUTION: If you want to have granular control over entity capabilities.
     *
     * @return void
     */
    public function grantCapabilities()
    {

        if (empty($this->whitelisted_roles)) {
            return;
        }

        foreach ($this->whitelisted_roles as $role) {
            // Check if role exists
            if (!wp_roles()->is_role($role)) {
                continue;
            }

            $role = get_role($role);

            // Check if role is in whitelist
            if (in_array($role->name, array_keys(wp_roles()->roles))) {
                foreach ($this->capabilities as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}
