<?php

namespace Repat\CliCrud\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Select;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

abstract class Action
{
    use Conditionable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The models the action will run on. Set by the dispatcher before
     * the action is executed (sync or queued).
     */
    public Collection $models;

    /**
     * The action field values. Set by the dispatcher.
     */
    public ActionFields $fields;

    protected ?string $name = null;

    protected ?string $confirmText = null;

    protected ?string $confirmButtonText = null;

    protected ?string $cancelButtonText = null;

    protected bool $destructive = false;

    protected bool $requiresConfirmation = true;

    public static function make(): static
    {
        return new static;
    }

    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function confirmText(string $text): static
    {
        $this->confirmText = $text;

        return $this;
    }

    public function confirmButtonText(string $text): static
    {
        $this->confirmButtonText = $text;

        return $this;
    }

    public function cancelButtonText(string $text): static
    {
        $this->cancelButtonText = $text;

        return $this;
    }

    public function destructive(bool $on = true): static
    {
        $this->destructive = $on;

        return $this;
    }

    public function withoutConfirmation(): static
    {
        $this->requiresConfirmation = false;

        return $this;
    }

    public function onConnection(string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }

    public function onQueue(string $queue): static
    {
        $this->queue = $queue;

        return $this;
    }

    public function getName(): string
    {
        if ($this->name !== null) {
            return $this->name;
        }

        $basename = class_basename(static::class);

        if (Str::endsWith($basename, 'Action')) {
            $basename = Str::substr($basename, 0, -6);
        }

        return Str::headline($basename);
    }

    public function isDestructive(): bool
    {
        return $this->destructive;
    }

    public function requiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function getConfirmText(): ?string
    {
        return $this->confirmText;
    }

    /**
     * Override to declare fields that prompt the user for input before
     * the action runs. Return an array of Repat\CliCrud\Fields\Field
     * instances (Text, Number, Boolean, Select, Textarea, ...).
     *
     * @return array<int, Field>
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Override to gate the action. Default: allow.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prompt the user for values for every field declared in fields().
     * The returned ActionFields is the same wrapper passed to handle().
     */
    public function askForFields(): ActionFields
    {
        $values = [];

        foreach ($this->fields() as $field) {
            $values[$field->getName()] = $this->promptForField($field);
        }

        return new ActionFields($values);
    }

    /**
     * Run the action against the given models. Subclasses must implement
     * this and return an ActionResponse.
     */
    abstract public function handle(Collection $models, ActionFields $fields): ActionResponse;

    protected function promptForField(Field $field): mixed
    {
        $label = $field->getLabel();
        $default = $field->getDefault();

        if ($field instanceof Boolean) {
            return confirm(label: $label, default: (bool) ($default ?? false));
        }

        if ($field instanceof Select) {
            $options = $field->getPromptOptions();
            $promptOptions = ['label' => $label, 'options' => $options['options']];

            if ($default !== null) {
                $promptOptions['default'] = $default;
            }

            return select(...$promptOptions);
        }

        if ($field instanceof Number) {
            $options = $field->getPromptOptions();
            $promptOptions = [
                'label' => $label,
                'default' => (string) ($default ?? ''),
                'validate' => $options['validate'] ?? null,
            ];

            return text(...$promptOptions);
        }

        if ($field instanceof Textarea) {
            $promptOptions = [
                'label' => $label,
                'default' => (string) ($default ?? ''),
            ];

            return textarea(...$promptOptions);
        }

        if ($field instanceof Text) {
            $promptOptions = [
                'label' => $label,
                'default' => (string) ($default ?? ''),
            ];
            $extra = $field->getPromptOptions();
            if (isset($extra['validate'])) {
                $promptOptions['validate'] = $extra['validate'];
            }

            return text(...$promptOptions);
        }

        return text(label: $label, default: $default !== null ? (string) $default : '');
    }
}
