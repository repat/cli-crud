<?php

namespace Repat\CliCrud\Views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use League\CommonMark\CommonMarkConverter;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Relations\HasOne;
use Repat\CliCrud\Fields\Relations\MorphTo;
use Repat\CliCrud\Fields\Relations\Relation;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Support\ColumnFormatter;
use Repat\CliCrud\Support\Theme;

use function Termwind\terminal;

class DetailViewRenderer
{
    protected const MAX_BOX_WIDTH = 120;

    protected const MIN_BOX_WIDTH = 60;

    protected const MAX_VALUE_LENGTH = 300;

    protected const LABEL_PADDING = 2;

    protected const BOX_PADDING = 2;

    protected const RELATION_LABEL_MARKER = '→ ';

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

        $this->renderCards($model, $resource, 'before');

        $relations = array_filter(
            $resource::getRelations(),
            fn ($r) => ! ($r instanceof BelongsTo) && ! ($r instanceof MorphTo) && ! ($r instanceof HasOne)
        );
        foreach ($relations as $relation) {
            $this->renderRelation($model, $relation);
        }

        $this->renderCards($model, $resource, 'after');
    }

    protected function renderCards(Model $model, Resource $resource, string $position): void
    {
        $cards = array_filter(
            $resource::getCards(),
            fn ($card) => $card->getPosition() === $position
        );

        foreach ($cards as $card) {
            $this->output('');
            $this->output($card->render($model, $resource));
        }
    }

    protected function buildFieldData(Model $model, Resource $resource): array
    {
        $fields = [];
        foreach ($resource::fields() as $field) {
            if ($field instanceof BelongsTo || $field instanceof MorphTo || $field instanceof HasOne) {
                $fields[] = $this->buildInlineRelationField($model, $field);
            } elseif ($field instanceof Field) {
                $value = $model->{$field->getName()};
                $fields[] = [
                    'label' => $field->getLabel(),
                    'value' => $value,
                    'formatted' => $this->formatValue($value, $field),
                ];
            }
        }

        return $fields;
    }

    /**
     * Build a single-row entry for a single-row relation (BelongsTo, HasOne,
     * MorphTo) so it renders inline in the main detail box, using the related
     * model's $title column. If the title column is not the model's primary key,
     * the key value is appended in parens (e.g. "John Doe (1)").
     *
     * @param  BelongsTo|HasOne|MorphTo  $relation
     * @return array{label: string, value: mixed, formatted: string}
     */
    protected function buildInlineRelationField(Model $model, Relation $relation): array
    {
        $label = self::RELATION_LABEL_MARKER.$relation->getLabel();
        $related = $model->{$relation->getName()};

        if (! $related instanceof Model) {
            return [
                'label' => $label,
                'value' => null,
                'formatted' => $this->formatValue(null),
            ];
        }

        $relatedResource = $this->resolveRelatedResource($relation, $related);
        $titleColumn = $relatedResource::getTitle();
        $titleValue = $related->{$titleColumn};
        $keyName = $related->getKeyName();
        $keyValue = $related->getKey();

        $display = ($titleColumn === $keyName)
            ? (string) $titleValue
            : $titleValue.' ('.$keyValue.')';

        $formatted = Theme::relationValue().$this->formatValue($display).Theme::resetFg();

        return [
            'label' => $label,
            'value' => $display,
            'formatted' => $formatted,
        ];
    }

    /**
     * For a MorphTo, return the resource whose model matches the resolved
     * related instance. For a BelongsTo or HasOne, simply return its single
     * resource.
     *
     * @param  BelongsTo|HasOne|MorphTo  $relation
     */
    protected function resolveRelatedResource(Relation $relation, Model $related): Resource
    {
        if ($relation instanceof BelongsTo || $relation instanceof HasOne) {
            return $relation->getResource();
        }

        foreach ($relation->getResources() as $resource) {
            $resourceModel = $resource::getModel();
            if ($related instanceof $resourceModel) {
                return $resource;
            }
        }

        $resources = $relation->getResources();

        return $resources[0] ?? $relation->getResource();
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
            default => null,
        };
    }

    protected function renderTitle(string $title): void
    {
        $paddedTitle = ' '.$this->mb_str_pad($title, $this->boxWidth - 4, ' ').' ';
        $this->output(self::BOX['vertical'].$paddedTitle.self::BOX['vertical']);
    }

    protected function renderField(array $field): void
    {
        $label = $this->mb_str_pad($field['label'], $this->labelWidth + self::LABEL_PADDING, ' ');
        $formattedValue = $field['formatted'];
        $plainValue = $this->getPlainText($formattedValue);

        $lines = $this->wrapText($plainValue, $this->valueWidth);

        if (empty($lines)) {
            $lines = [''];
        }

        foreach ($lines as $index => $line) {
            $paddedLine = $this->mb_str_pad($line, $this->valueWidth, ' ');

            if ($index === 0) {
                $content = ' '.$label.$paddedLine.' ';
            } else {
                $emptyLabel = $this->mb_str_pad('', $this->labelWidth + self::LABEL_PADDING, ' ');
                $content = ' '.$emptyLabel.$paddedLine.' ';
            }

            $content = $this->mb_str_pad($content, $this->boxWidth - 2, ' ');

            if ($index === 0 && $this->hasAnsiCodes($formattedValue)) {
                $ansiValue = $this->extractAnsiValue($formattedValue);
                $content = ' '.$label.$ansiValue;
                $remaining = $this->boxWidth - 4 - mb_strlen($label) - mb_strlen($this->getPlainText($ansiValue));
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
            $columnWidths[$col] = mb_strlen(ColumnFormatter::format($col));
        }

        foreach ($items as $item) {
            foreach ($columns as $col) {
                $value = data_get($item, $col);
                $formatted = $this->formatTableValue($value);
                $columnWidths[$col] = max($columnWidths[$col], mb_strlen($formatted));
            }
        }

        // Calculate actual content width
        // Structure: [space][col1][2 spaces][col2][2 spaces]...[colN][space]
        $gapsWidth = (count($columns) - 1) * 2; // 2 spaces between each column
        $totalWidth = array_sum($columnWidths) + $gapsWidth + 2; // +2 for left/right padding
        $titleLength = mb_strlen($title);
        $contentWidth = max($totalWidth, $titleLength);

        $this->boxWidth = min(
            max($contentWidth + 2, self::MIN_BOX_WIDTH),
            min($this->terminalWidth - 4, self::MAX_BOX_WIDTH)
        );

        // Calculate available width inside the box
        // Box structure: │[space][content][space]│
        $availableWidth = $this->boxWidth - 4; // -4 for borders (2) and padding (2)

        // Distribute available width among columns
        $remaining = $availableWidth - $gapsWidth;

        $this->columnWidths = [];
        foreach ($columns as $i => $col) {
            if ($i === count($columns) - 1) {
                // Last column gets remaining width
                $this->columnWidths[$col] = $remaining;
            } else {
                // Other columns get their needed width or average, whichever is smaller
                $width = min($columnWidths[$col] + 2, (int) floor($remaining / (count($columns) - $i)));
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
            $header = ColumnFormatter::format($col);
            $parts[] = $this->mb_str_pad($header, $this->columnWidths[$col], ' ');
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = $this->mb_str_pad($content, $this->boxWidth - 2, ' ');

        $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
    }

    protected function renderTableSeparator(array $columns): void
    {
        $parts = [];
        foreach ($columns as $col) {
            $parts[] = str_repeat('─', $this->columnWidths[$col]);
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = $this->mb_str_pad($content, $this->boxWidth - 2, ' ');

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

            $parts[] = $this->mb_str_pad($formatted, $this->columnWidths[$col], ' ');
        }

        $content = ' '.implode('  ', $parts).' ';
        $content = $this->mb_str_pad($content, $this->boxWidth - 2, ' ');

        $this->output(self::BOX['vertical'].$content.self::BOX['vertical']);
    }

    protected function renderPagination(int $page, int $totalPages): void
    {
        $text = "Page {$page} of {$totalPages}";
        $padding = (int) floor(($this->boxWidth - mb_strlen($text)) / 2);
        $this->output(str_repeat(' ', $padding).$text);
    }

    protected function formatValue(mixed $value, ?Field $field = null): string
    {
        if (is_null($value)) {
            return Theme::null().'NULL'.Theme::resetFg();
        }

        if (is_bool($value)) {
            return $value ? Theme::true().'✓'.Theme::resetFg() : Theme::false().'✗'.Theme::resetFg();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(config('cli-crud.display.date_format', 'Y-m-d H:i:s'));
        }

        if ($field instanceof Json) {
            return $this->formatJsonValue($value, $field);
        }

        if ($value instanceof \UnitEnum) {
            return Theme::enum().'['.$value->name.']'.Theme::resetBold();
        }

        if ($field instanceof Textarea && $field->isMarkdown()) {
            return $this->formatMarkdownValue((string) $value);
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    protected function formatMarkdownValue(string $value): string
    {
        if (! class_exists(CommonMarkConverter::class)) {
            throw new \RuntimeException(
                'Markdown rendering requires league/commonmark. Install it with: composer require league/commonmark'
            );
        }

        $converter = new CommonMarkConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $converter->convert($value)->getContent();

        $html = preg_replace('/<pre><code[^>]*>(.*?)<\/code><\/pre>/is', "\n".Theme::code().'$1'.Theme::resetFg()."\n", $html);
        $html = preg_replace('/<code[^>]*>(.*?)<\/code>/i', Theme::code().'$1'.Theme::resetFg(), $html);
        $html = preg_replace('/<strong>(.*?)<\/strong>/i', Theme::bold().'$1'.Theme::resetBold(), $html);
        $html = preg_replace('/<em>(.*?)<\/em>/i', Theme::italic().'$1'.Theme::resetItalic(), $html);
        $html = preg_replace('/<hr\s*\/?>/i', "\n".Theme::hr().str_repeat('─', 40).Theme::resetFg()."\n", $html);
        $html = preg_replace('/<a\s+[^>]*href="([^"]*)"[^>]*>(.*?)<\/a>/i', '$2 ('.Theme::linkUrl().'$1'.Theme::resetUnderline().')', $html);

        $html = preg_replace('/<h[1-6]>(.*?)<\/h[1-6]>/i', Theme::heading().'$1'.Theme::resetAll()."\n\n", $html);
        $html = preg_replace('/<li[^>]*>(.*?)<\/li>/is', "  • $1\n", $html);
        $html = preg_replace('/<blockquote[^>]*>(.*?)<\/blockquote>/is', Theme::blockquote().'$1'.Theme::resetFg()."\n\n", $html);
        $html = str_ireplace('</p>', "\n\n", $html);
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);

        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('/\n{3,}/', "\n\n", $html);

        return trim($html);
    }

    protected function formatJsonValue(mixed $value, Json $field): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return Theme::invalidJson().'[Invalid JSON: '.json_last_error_msg().']'.Theme::resetFg();
            }
            $json = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } elseif ($value instanceof \UnitEnum) {
            $json = json_encode($value->name, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } elseif (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            return (string) $value;
        }

        if (! $field->isHighlighted()) {
            return $json;
        }

        return (string) preg_replace_callback(
            '/("(?:[^"\\\\]|\\\\.)*")\s*:|("(?:[^"\\\\]|\\\\.)*")|(true|false|null)|(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)|([{}\[\],:]+)/',
            function ($matches) {
                if (! empty($matches[1])) {
                    return Theme::jsonKey().$matches[1].Theme::resetFg().':';
                }
                if (! empty($matches[2])) {
                    return Theme::jsonString().$matches[2].Theme::resetFg();
                }
                if (! empty($matches[3])) {
                    return Theme::jsonKeyword().$matches[3].Theme::resetFg();
                }
                if (! empty($matches[4])) {
                    return Theme::jsonNumber().$matches[4].Theme::resetFg();
                }

                return $matches[5];
            },
            $json
        );
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

        if ($value instanceof \UnitEnum) {
            return Theme::enum().'['.$value->name.']'.Theme::resetBold();
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    protected function wrapText(string $text, int $maxWidth): array
    {
        $paragraphs = preg_split('/\n\n+/', trim($text));

        if (count($paragraphs) > 1) {
            $lines = [];

            foreach ($paragraphs as $paragraph) {
                $wrapped = $this->wrapText($paragraph, $maxWidth);

                foreach ($wrapped as $wrappedLine) {
                    $lines[] = $wrappedLine;
                }

                $lines[] = '';
            }

            array_pop($lines);

            return $lines;
        }

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

    protected function mb_str_pad(string $input, int $length, string $pad_string = ' '): string
    {
        $pad_length = $length - mb_strlen($input);
        if ($pad_length <= 0) {
            return $input;
        }

        return $input.str_repeat($pad_string, $pad_length);
    }

    protected function output(string $text): void
    {
        echo $text."\n";
    }
}
