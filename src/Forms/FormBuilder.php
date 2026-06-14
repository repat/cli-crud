<?php

namespace Repat\CliCrud\Forms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Select;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Support\ColumnTypeMapper;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class FormBuilder
{
    /**
     * @param  array<Field|BelongsTo>  $fields
     */
    public function build(array $fields, ?Model $model = null, ?Resource $resource = null): array
    {
        // Always ensure we have a model instance for relationship introspection
        // For create scenarios, instantiate a new (unsaved) model from the resource
        $introspectionModel = $model ?? $this->getIntrospectionModel($resource);

        // Remove fields that should not appear in forms
        $fields = array_values(array_filter($fields, fn ($field) => ! $field instanceof Field || $field->isShownInForms()));

        // Pre-compute foreign keys for all BelongsTo fields
        $belongsToForeignKeys = [];
        foreach ($fields as $field) {
            if ($field instanceof BelongsTo) {
                $belongsToForeignKeys[$field->getName()] = $field->getForeignKey($introspectionModel);
            }
        }

        $data = [];
        $errors = [];

        do {
            $data = [];

            foreach ($fields as $field) {
                if ($field instanceof BelongsTo) {
                    // Get the foreign key name
                    $foreignKey = $belongsToForeignKeys[$field->getName()];

                    // Load current value from foreign key column
                    $currentValue = $data[$foreignKey] ?? ($model ? $model->{$foreignKey} : null);

                    // Prompt for the relationship
                    $value = $this->promptForBelongsTo($field, $currentValue, $errors[$foreignKey] ?? null);

                    // Store using foreign key name
                    $data[$foreignKey] = $value;
                } else {
                    $currentValue = $data[$field->getName()] ?? ($model ? $model->{$field->getName()} : null);
                    $value = $this->promptForField($field, $currentValue, $errors[$field->getName()] ?? null);
                    $data[$field->getName()] = $value;
                }
            }

            $validator = Validator::make($data, $this->buildValidationRules($fields, $introspectionModel));

            if ($validator->fails()) {
                $errors = $validator->errors()->toArray();
                $this->displayErrors($errors);
            }
        } while ($validator->fails());

        // Convert empty strings to null for nullable fields
        foreach ($fields as $field) {
            if ($field instanceof Field && ! $field instanceof BelongsTo && $field->isNullable()) {
                $name = $field->getName();

                if (isset($data[$name]) && $data[$name] === '') {
                    $data[$name] = null;
                }
            }
        }

        return $data;
    }

    protected function getIntrospectionModel(?Resource $resource): ?Model
    {
        // For create scenarios, we need a model instance to introspect relationships
        // Get it from the resource's model
        if ($resource) {
            return $resource::getModelInstance();
        }

        return null;
    }

    protected function promptForField(Field $field, mixed $currentValue = null, array|string|null $error = null): mixed
    {
        $label = $field->getLabel();

        if ($error) {
            $errorText = is_array($error) ? implode(', ', $error) : $error;
            $label .= " <fg=red>({$errorText})</>";
        }

        return match (true) {
            $field instanceof Boolean => $this->promptForBoolean($field, $label, $currentValue),
            $field instanceof Select => $this->promptForSelect($field, $label, $currentValue),
            $field instanceof Json => $this->promptForJson($field, $label, $currentValue),
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
            'default' => (string) (ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault() ?? ''),
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
            'default' => (string) (ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault() ?? ''),
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
            default: (bool) (ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault() ?? false)
        );
    }

    protected function promptForSelect(Select $field, string $label, mixed $currentValue): mixed
    {
        $promptOptions = $field->getPromptOptions();
        $default = ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault();

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
            'default' => (string) (ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault() ?? ''),
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
            default: (string) (ColumnTypeMapper::scalarForValue($currentValue) ?? $field->getDefault() ?? '')
        );
    }

    protected function promptForJson(Json $field, string $label, mixed $currentValue): string
    {
        $default = '';

        if ($currentValue !== null) {
            if (is_string($currentValue)) {
                $decoded = json_decode($currentValue);
                if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                    $default = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                } else {
                    $default = $currentValue;
                }
            } elseif ($currentValue instanceof \UnitEnum) {
                $default = json_encode(ColumnTypeMapper::scalarForValue($currentValue), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } elseif (is_array($currentValue) || is_object($currentValue)) {
                $default = json_encode($currentValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        } elseif ($field->getDefault() !== null) {
            $default = is_string($field->getDefault())
                ? $field->getDefault()
                : json_encode($field->getDefault(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        $promptOptions = $field->getPromptOptions();

        return textarea(
            label: $label,
            default: $default,
            validate: $promptOptions['validate'] ?? null,
        );
    }

    protected function promptForBelongsTo(BelongsTo $field, mixed $currentValue, array|string|null $error = null): mixed
    {
        $label = $field->getLabel();

        if ($error) {
            $errorText = is_array($error) ? implode(', ', $error) : $error;
            $label .= " <fg=red>({$errorText})</>";
        }

        $resource = $field->getResource();
        $relatedModel = $resource::getModel();
        $displayField = $field->getDisplayField() ?? $resource::getTitle();

        if ($currentValue) {
            $currentModel = $relatedModel::find($currentValue);
            if ($currentModel instanceof Model) {
                $label .= " <fg=gray>(currently: {$currentModel->{$displayField}})</>";
            }
        }

        return search(
            label: $label,
            options: fn (string $value) => strlen($value) > 0
                ? $relatedModel::where($displayField, 'like', "%{$value}%")
                    ->limit(10)
                    ->pluck($displayField, 'id')
                    ->toArray()
                : $relatedModel::limit(10)->pluck($displayField, 'id')->toArray()
        );
    }

    /**
     * @param  array<Field|BelongsTo>  $fields
     */
    protected function buildValidationRules(array $fields, ?Model $introspectionModel = null): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if ($field instanceof BelongsTo) {
                // Always get foreign key from relationship (no fallback)
                $foreignKey = $field->getForeignKey($introspectionModel);

                // Build validation rules
                $fieldRules = [];

                // Add 'required' only if explicitly set
                if ($field->isRequired()) {
                    $fieldRules[] = 'required';
                } else {
                    $fieldRules[] = 'nullable';
                }

                // Add exists rule
                $relatedTable = $field->getResource()::getModelInstance()->getTable();
                $fieldRules[] = 'exists:'.$relatedTable.',id';

                $rules[$foreignKey] = $fieldRules;
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
