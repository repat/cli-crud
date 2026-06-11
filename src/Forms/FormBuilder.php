<?php

namespace Repat\CliCrud\Forms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Select;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class FormBuilder
{
    /**
     * @param array<Field|BelongsTo> $fields
     */
    public function build(array $fields, ?Model $model = null): array
    {
        $data = [];
        $errors = [];

        do {
            $data = [];

            foreach ($fields as $field) {
                $value = $this->promptForField($field, $data[$field->getName()] ?? null, $errors[$field->getName()] ?? null);
                $data[$field->getName()] = $value;
            }

            $validator = Validator::make($data, $this->buildValidationRules($fields));

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $this->displayErrors($errors);
            }
        } while ($validator->fails());

        return $data;
    }

    protected function promptForField(Field|BelongsTo $field, mixed $currentValue = null, ?string $error = null): mixed
    {
        $label = $field->getLabel();

        if ($error) {
            $label .= " <fg=red>({$error})</>";
        }

        if ($field instanceof BelongsTo) {
            return $this->promptForBelongsTo($field, $label, $currentValue);
        }

        return match (true) {
            $field instanceof Boolean => $this->promptForBoolean($field, $label, $currentValue),
            $field instanceof Select => $this->promptForSelect($field, $label, $currentValue),
            $field instanceof Text => $this->promptForText($field, $label, $currentValue),
            $field instanceof Number => $this->promptForNumber($field, $label, $currentValue),
            $field instanceof DateTime => $this->promptForDateTime($field, $label, $currentValue),
            $field instanceof Textarea => $this->promptForTextarea($field, $label, $currentValue),
            default => text(label: $label, default: $currentValue ?? $field->getDefault()),
        };
    }

    protected function promptForText(Text $field, string $label, mixed $currentValue): string
    {
        if ($field->isPassword()) {
            $options = [
                'label' => $label,
            ];

            $promptOptions = $field->getPromptOptions();
            if (isset($promptOptions['validate'])) {
                $options['validate'] = $promptOptions['validate'];
            }

            return password(...$options);
        }

        $options = [
            'label' => $label,
            'default' => (string) ($currentValue ?? $field->getDefault() ?? ''),
        ];

        $promptOptions = $field->getPromptOptions();
        if (isset($promptOptions['validate'])) {
            $options['validate'] = $promptOptions['validate'];
        }

        return text(...$options);
    }

    protected function promptForNumber(Number $field, string $label, mixed $currentValue): string
    {
        $options = [
            'label' => $label,
            'default' => (string) ($currentValue ?? $field->getDefault() ?? ''),
        ];

        $promptOptions = $field->getPromptOptions();
        if (isset($promptOptions['validate'])) {
            $options['validate'] = $promptOptions['validate'];
        }

        return text(...$options);
    }

    protected function promptForBoolean(Boolean $field, string $label, mixed $currentValue): bool
    {
        return confirm(
            label: $label,
            default: (bool) ($currentValue ?? $field->getDefault() ?? false)
        );
    }

    protected function promptForSelect(Select $field, string $label, mixed $currentValue): mixed
    {
        $promptOptions = $field->getPromptOptions();
        $default = $currentValue ?? $field->getDefault();
        
        $options = [
            'label' => $label,
            'options' => $promptOptions['options'],
        ];
        
        if ($default !== null) {
            $options['default'] = $default;
        }

        return select(...$options);
    }

    protected function promptForDateTime(DateTime $field, string $label, mixed $currentValue): string
    {
        $options = [
            'label' => $label,
            'default' => (string) ($currentValue ?? $field->getDefault() ?? ''),
        ];

        $promptOptions = $field->getPromptOptions();
        if (isset($promptOptions['placeholder'])) {
            $options['placeholder'] = $promptOptions['placeholder'];
        }
        if (isset($promptOptions['validate'])) {
            $options['validate'] = $promptOptions['validate'];
        }

        return text(...$options);
    }

    protected function promptForTextarea(Textarea $field, string $label, mixed $currentValue): string
    {
        return textarea(
            label: $label,
            default: (string) ($currentValue ?? $field->getDefault() ?? '')
        );
    }

    protected function promptForBelongsTo(BelongsTo $field, string $label, mixed $currentValue): mixed
    {
        $resource = $field->getResource();
        $relatedModel = $resource::getModel();
        $displayField = $field->getDisplayField() ?? $this->guessDisplayField($relatedModel);

        return search(
            label: $label,
            options: fn(string $value) => strlen($value) > 0
                ? $relatedModel::where($displayField, 'like', "%{$value}%")
                    ->limit(10)
                    ->pluck($displayField, 'id')
                    ->toArray()
                : $relatedModel::limit(10)->pluck($displayField, 'id')->toArray()
        );
    }

    protected function guessDisplayField(string $modelClass): string
    {
        $model = new $modelClass();
        $fillable = $model->getFillable();

        foreach (['name', 'title', 'label', 'email'] as $field) {
            if (in_array($field, $fillable)) {
                return $field;
            }
        }

        return $model->getKeyName();
    }

    /**
     * @param array<Field|BelongsTo> $fields
     */
    protected function buildValidationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if ($field instanceof BelongsTo) {
                $rules[$field->getName()] = ['required', 'exists:' . $field->getResource()::getModelInstance()->getTable() . ',id'];
            } else {
                $rules[$field->getName()] = $field->getRules();
            }
        }

        return $rules;
    }

    protected function displayErrors(array $errors): void
    {
        echo "\n<fg=red>Validation errors:</>\n";
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                echo "  - {$field}: {$error}\n";
            }
        }
        echo "\nPlease try again:\n\n";
    }
}
