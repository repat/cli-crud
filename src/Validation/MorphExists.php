<?php

namespace Repat\CliCrud\Validation;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Relations\Relation;

class MorphExists implements DataAwareRule, ValidationRule
{
    /** @var array<string, mixed> */
    protected array $data = [];

    public function __construct(
        protected string $typeColumn,
        protected string $idColumn,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '' || $value === false) {
            return;
        }

        $type = $this->data[$this->typeColumn] ?? null;

        if ($type === null || $type === '') {
            $fail("The :attribute cannot be set without a {$this->typeColumn}.");

            return;
        }

        $modelClass = Relation::getMorphedModel((string) $type) ?? $type;

        if (! class_exists($modelClass)) {
            $fail("The :attribute references an unknown type [{$type}].");

            return;
        }

        if (! $modelClass::where('id', $value)->exists()) {
            $fail(__('validation.exists', ['attribute' => $attribute]));
        }
    }
}
