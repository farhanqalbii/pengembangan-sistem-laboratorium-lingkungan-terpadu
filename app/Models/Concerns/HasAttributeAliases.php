<?php

namespace App\Models\Concerns;

trait HasAttributeAliases
{
    public function fill(array $attributes)
    {
        if (! empty($this->attributeAliases)) {
            $mapped = [];
            foreach ($attributes as $key => $value) {
                $actualKey = $this->attributeAliases[$key] ?? $key;
                $mapped[$actualKey] = $value;
            }
            $attributes = $mapped;
        }

        return parent::fill($attributes);
    }

    public function getFillable()
    {
        $fillable = parent::getFillable();
        if (! empty($this->attributeAliases)) {
            return array_values(array_unique(array_merge($fillable, array_keys($this->attributeAliases))));
        }

        return $fillable;
    }

    public function getAttribute($key)
    {
        if (isset($this->attributeAliases[$key])) {
            $mapped = $this->attributeAliases[$key];

            return parent::getAttribute($mapped);
        }

        return parent::getAttribute($key);
    }

    public function setAttribute($key, $value)
    {
        if (isset($this->attributeAliases[$key])) {
            $mapped = $this->attributeAliases[$key];

            return parent::setAttribute($mapped, $value);
        }

        return parent::setAttribute($key, $value);
    }

    public function isFillable($key)
    {
        if (isset($this->attributeAliases[$key])) {
            $key = $this->attributeAliases[$key];
        }

        return parent::isFillable($key);
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (! empty($this->attributeAliases)) {
            foreach ($this->attributeAliases as $alias => $column) {
                if (array_key_exists($column, $attributes) && ! array_key_exists($alias, $attributes)) {
                    $attributes[$alias] = $attributes[$column];
                }
            }
        }

        return $attributes;
    }
}
