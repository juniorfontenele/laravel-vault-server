<?php

declare(strict_types = 1);

namespace JuniorFontenele\LaravelVaultServer\Concerns;

use Illuminate\Support\Fluent;

trait HasValidation
{
    /**
     * Define the validation rules for the data.
     *
     * This method can be overridden to provide custom validation rules
     * for the data being validated.
     *
     * @return array<string, mixed> An associative array of validation rules
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Define the custom messages for validation errors.
     *
     * This method can be overridden to provide custom error messages
     * for validation rules.
     *
     * @return array<string, string> An associative array of validation messages
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Define the attributes for validation.
     *
     * This method can be overridden to provide custom attribute names
     * for validation errors.
     *
     * @return array<string, string> An associative array of attribute names
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Validate the given data against the defined rules.
     *
     * @param array<string, mixed> $data Data to validate
     * @return Fluent A Fluent instance containing the validated data
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(array $data): Fluent
    {
        return new Fluent(validator($data, $this->rules(), $this->messages(), $this->attributes())->validate());
    }
}
