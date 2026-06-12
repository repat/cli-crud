<?php

namespace Repat\CliCrud\Views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Repat\CliCrud\Fields\Relations\Relation;
use Repat\CliCrud\Resources\Resource;

use function Termwind\terminal;

class DetailViewRenderer
{
    protected const MAX_BOX_WIDTH = 120;

    protected const MIN_BOX_WIDTH = 60;

    protected const MAX_VALUE_LENGTH = 300;

    protected const LABEL_PADDING = 2;

    protected const BOX_PADDING = 2;

    protected const BOX = [
        'top_left' => '╭',
        'top_right' => '╮',
        'bottom_left' => '╰',
        'bottom_right' => '╯',
        'horizontal' => '─',
        'vertical' => '│',
        'divider_left' => '├',
        'divider_right' => '┤',
    ];

    protected int $terminalWidth;

    protected int $boxWidth;

    protected int $labelWidth;

    protected int $valueWidth;

    public function render(Model $model, Resource $resource): void
    {
        $this->terminalWidth = terminal()->width();

        $fields = $this->buildFieldData($model, $resource);

        $title = $resource::getSingularLabel().' #'.$model->getKey();

        $this->calculateDimensions($fields, $title);

        $this->renderDetailBox($title, $fields);

        $relations = $resource::getRelations();
        foreach ($relations as $relation) {
            $this->renderRelation($model, $relation);
        }
    }

    protected function buildFieldData(Model $model, Resource $resource): array
    {
        $fields = [];
        foreach ($resource::getFields() as $field) {
            $value = $model->{$field->getName()};
            $fields[] = [
                'label' => $field->getLabel(),
                'value' => $value,
                'formatted' => $this->formatValue($value),
            ];
        }

        return $fields;
    }

    protected function calculateDimensions(array $fields, string $title): void
    {
        $maxLabelWidth = max(array_map(fn ($f) => mb_strlen($f['label']), $fields));
        $this->labelWidth = max($maxLabelWidth, 10);

        $maxValueLength = 0;
        foreach ($fields as $field) {
            $formatted = $this->getPlainText($field['formatted']);
            $lines = explode("\n", $formatted);
            foreach ($lines as $line) {
                $maxValueLength = max($maxValueLength, mb_strlen($line));
            }
        }

        $titleLength = mb_strlen($title);
        $contentWidth = $this->labelWidth + self::LABEL_PADDING + min($maxValueLength, 50);
        $contentWidth = max($contentWidth, $titleLength);

        $this->boxWidth = min(
            max($contentWidth + (self::BOX_PADDING * 2) + 2, self::MIN_BOX_WIDTH),
            min($this->terminalWidth - 4, self::MAX_BOX_WIDTH)
        );

        $this->valueWidth = $this->boxWidth - $this->labelWidth - self::LABEL_PADDING - (self::BOX_PADDING * 2) - 2;
    }

    protected function renderDetailBox(string $title, array $fields): void
    {
        $this->renderBorder('top');
        $this->renderTitle($title);
        $this->renderBorder('divider');

        foreach ($fields as $field) {
            $this->renderField($field);
        }

        $this->renderBorder('bottom');
    }

    protected function renderBorder(string $type): void
    {
        $innerWidth = $this->boxWidth - 2;
        $horizontal = str_repeat(self::BOX['horizontal'], $innerWidth);

        match ($type) {
            'top' => $this->output(self::BOX['top_left'].$horizontal.self::BOX['top_right']),
            'divider' => $this->output(self::BOX['divider_left'].$horizontal.self::BOX['divider_right']),
            'bottom' => $this->output(self::BOX['bottom_left'].$horizontal.self::BOX['bottom_right']),
        };
    }

    protected function renderTitle(string $title): void
    {
        $paddedTitle = ' '.str_pad($title, $this->boxWidth - 4, ' ').' ';
        $this->output(self::BOX['vertical'].$paddedTitle.self::BOX['vertical']);
    }

    protected function renderField(array $field): void
    {
        $label = str_pad($field['label'], $this->labelWidth + self::LABEL_PADDING, ' ');
        $formattedValue = $field['formatted'];
        $plainValue = $this->getPlainText($formattedValue);

        $lines = $this->wrapText($plainValue, $this->valueWidth);

        if (empty($lines)) {
            $lines = [''];
        }

        foreach ($lines as $index => $line) {
            $paddedLine = str_pad($line, $this->valueWidth, ' ');

            if ($index === 0) {
                $content = ' '.$label.$paddedLine.' ';
            } else {
                $emptyLabel = str_pad('', $this->labelWidth + self::LABEL_PADDING, ' ');
                $content = ' '.$emptyLabel.$paddedLine.' ';
            }

            $content = str_pad($content, $this->boxWidth - 2, ' ');

            if ($index === 0 && $this->hasAnsiCodes($formattedValue)) {
                $ansiValue = $this->extractAnsiValue($formattedValue);
                $emptyLabel = str_pad('', $this->labelWidth + self::LABEL_PADDING, ' ');
                $content = ' '.$label.$ansiValue;
                $remaining = $this->boxWidth - 2 - mb_strlen($label) - self::LABEL_PADDING - mb_strlen($this->getPlainText($ansiValue));
                $content .= str_repeat(' ', max(0, $remaining));
                $content .= ' ';
            }

            $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
        }
    }

    protected function renderRelation(Model $model, Relation $relation): void
    {
        $relatedItems = $model->{$relation->getName()}()->get();

        if ($relatedItems->isEmpty()) {
            return;
        }

        $relatedResource = $relation->getResource();
        $relationLabel = $relation->getLabel();
        $columns = $relatedResource::tableColumns();

        $perPage = config('cli-crud.pagination.relation_per_page', 10);
        $total = $relatedItems->count();
        $totalPages = max(1, ceil($total / $perPage));

        $this->renderRelationBox($relationLabel, $relatedItems->take($perPage), $columns, 1, $totalPages, $total);
    }

    protected function renderRelationBox(string $title, Collection $items, array $columns, int $page, int $totalPages, int $total): void
    {
        $titleWithCount = "{$title} ({$total})";

        $this->calculateRelationDimensions($items, $columns, $titleWithCount);

        $this->renderBorder('top');
        $this->renderTitle($titleWithCount);
        $this->renderBorder('divider');

        $this->renderTableHeader($columns);
        $this->renderTableSeparator($columns);

        foreach ($items as $item) {
            $this->renderTableRow($item, $columns);
        }

        $this->renderBorder('bottom');

        if ($totalPages > 1) {
            $this->renderPagination($page, $totalPages);
        }
    }

    protected function calculateRelationDimensions(Collection $items, array $columns, string $title): void
    {
        $columnWidths = [];
        foreach ($columns as $col) {
            $columnWidths[$col] = mb_strlen(ucfirst(str_replace('_', ' ', $col)));
        }

        foreach ($items as $item) {
            foreach ($columns as $col) {
                $value = data_get($item, $col);
                $formatted = $this->formatTableValue($value);
                $columnWidths[$col] = max($columnWidths[$col], mb_strlen($formatted));
            }
        }

        $totalWidth = array_sum($columnWidths) + (count($columns) * 3) + 1;
        $titleLength = mb_strlen($title);
        $contentWidth = max($totalWidth, $titleLength);

        $this->boxWidth = min(
            max($contentWidth + 2, self::MIN_BOX_WIDTH),
            min($this->terminalWidth - 4, self::MAX_BOX_WIDTH)
        );

        $availableWidth = $this->boxWidth - (count($columns) * 3) - 3;
        $avgWidth = floor($availableWidth / count($columns));

        $this->columnWidths = [];
        $remaining = $availableWidth;
        foreach ($columns as $i => $col) {
            if ($i === count($columns) - 1) {
                $this->columnWidths[$col] = $remaining;
            } else {
                $width = min($columnWidths[$col] + 2, $avgWidth);
                $this->columnWidths[$col] = $width;
                $remaining -= $width;
            }
        }
    }

    protected array $columnWidths = [];

    protected function renderTableHeader(array $columns): void
    {
        $parts = [];
        foreach ($columns as $col) {
            $header = ucfirst(str_replace('_', ' ', $col));
            $parts[] = str_pad($header, $this->columnWidths[$col], ' ');
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = str_pad($content, $this->boxWidth - 2, ' ');

        $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
    }

    protected function renderTableSeparator(array $columns): void
    {
        $parts = [];
        foreach ($columns as $col) {
            $parts[] = str_repeat('─', $this->columnWidths[$col]);
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = str_pad($content, $this->boxWidth - 2, ' ');

        $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
    }

    protected function renderTableRow(Model $item, array $columns): void
    {
        $parts = [];
        foreach ($columns as $col) {
            $value = data_get($item, $col);
            $formatted = $this->formatTableValue($value);

            if (mb_strlen($formatted) > $this->columnWidths[$col]) {
                $formatted = mb_substr($formatted, 0, $this->columnWidths[$col] - 3).'...';
            }

            $parts[] = str_pad($formatted, $this->columnWidths[$col], ' ');
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = str_pad($content, $this->boxWidth - 2, ' ');

        $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
    }

    protected function renderPagination(int $page, int $totalPages): void
    {
        $text = "Page {$page} of {$totalPages}";
        $padding = floor(($this->boxWidth - mb_strlen($text)) / 2);
        $this->output(str_repeat(' ', $padding).$text);
    }

    protected function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return "\e[90mNULL\e[39m";
        }

        if (is_bool($value)) {
            return $value ? "\e[32m✓\e[39m" : "\e[31m✗\e[39m";
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(config('cli-crud.display.date_format', 'Y-m-d H:i:s'));
        }

        return (string) $value;
    }

    protected function formatTableValue(mixed $value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '✓' : '✗';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(config('cli-crud.display.date_format', 'Y-m-d H:i:s'));
        }

        return (string) $value;
    }

    protected function wrapText(string $text, int $maxWidth): array
    {
        if (mb_strlen($text) > self::MAX_VALUE_LENGTH) {
            $text = mb_substr($text, 0, self::MAX_VALUE_LENGTH - 3).'...';
        }

        if ($maxWidth <= 0) {
            return [$text];
        }

        $lines = [];
        $words = preg_split('/\s+/', $text);
        $currentLine = '';

        foreach ($words as $word) {
            if (mb_strlen($word) > $maxWidth) {
                $word = mb_substr($word, 0, $maxWidth - 3).'...';
            }

            if ($currentLine === '') {
                $currentLine = $word;
            } elseif (mb_strlen($currentLine.' '.$word) <= $maxWidth) {
                $currentLine .= ' '.$word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }

        return $lines ?: [''];
    }

    protected function getPlainText(string $text): string
    {
        return preg_replace('/\e\[[0-9;]*m/', '', $text);
    }

    protected function hasAnsiCodes(string $text): bool
    {
        return preg_match('/\e\[[0-9;]*m/', $text) === 1;
    }

    protected function extractAnsiValue(string $text): string
    {
        return $text;
    }

    protected function output(string $text): void
    {
        echo $text."\n";
    }
}
