<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

abstract class AbstractData implements JsonSerializable, Arrayable
{
    /**
     * Create a new instance from an array of data.
     *
     * @param array<string, mixed> $data
     */
    abstract public static function fromArray(array $data): static;

    /**
     * Create and validate a new instance from an array of data.
     *
     * @param array<string, mixed> $data
     * @return static
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function createAndValidate(array $data): static
    {
        $instance = static::fromArray($data);
        $instance->validate();

        return $instance;
    }

    /**
     * Returns the validated data as an array.
     * @return array<string, mixed>
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(): array
    {
        return validator($this->toArray(), $this->rules())->validate();
    }

    /**
     * Convert the data to an array.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * Convert the data to a JSON serializable format.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validation rules for the data.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
