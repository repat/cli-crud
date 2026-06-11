<?php

namespace Repat\CliCrud\Tables;

use Illuminate\Support\Collection;

class TableRenderer
{
    public function render(Collection $items, array $columns, int $currentPage, int $totalPages): string
    {
        if ($items->isEmpty()) {
            return "No records found.\n";
        }

        $tableData = $this->buildTableData($items, $columns);
        $table = $this->formatTable($tableData, $columns);
        $pagination = $this->formatPagination($currentPage, $totalPages);

        return $table . "\n" . $pagination . "\n";
    }

    protected function buildTableData(Collection $items, array $columns): array
    {
        $data = [];

        foreach ($items as $item) {
            $row = [];
            foreach ($columns as $column) {
                $value = data_get($item, $column);
                $row[$column] = $this->formatValue($value);
            }
            $data[] = $row;
        }

        return $data;
    }

    protected function formatValue(mixed $value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }

    protected function formatTable(array $data, array $columns): string
    {
        $widths = $this->calculateColumnWidths($data, $columns);
        $output = '';

        $headerRow = array_combine($columns, $columns);
        $output .= $this->formatRow($headerRow, $widths, true);
        $output .= $this->formatSeparator($widths);

        foreach ($data as $row) {
            $output .= $this->formatRow($row, $widths);
        }

        return $output;
    }

    protected function calculateColumnWidths(array $data, array $columns): array
    {
        $widths = [];

        foreach ($columns as $column) {
            $widths[$column] = strlen($column);
        }

        foreach ($data as $row) {
            foreach ($columns as $column) {
                $value = $row[$column] ?? '';
                $widths[$column] = max($widths[$column], strlen($value));
            }
        }

        return $widths;
    }

    protected function formatRow(array $row, array $widths, bool $isHeader = false): string
    {
        $output = '|';

        foreach ($widths as $column => $width) {
            $value = $row[$column] ?? '';
            $output .= ' ' . str_pad($value, $width) . ' |';
        }

        return $output . "\n";
    }

    protected function formatSeparator(array $widths): string
    {
        $output = '|';

        foreach ($widths as $width) {
            $output .= str_repeat('-', $width + 2) . '|';
        }

        return $output . "\n";
    }

    protected function formatPagination(int $currentPage, int $totalPages): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $output = "Page {$currentPage} of {$totalPages}  ";

        $pages = [];
        $maxVisible = 5;
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $start + $maxVisible - 1);

        if ($end - $start < $maxVisible - 1) {
            $start = max(1, $end - $maxVisible + 1);
        }

        if ($start > 1) {
            $pages[] = '[1]';
            if ($start > 2) {
                $pages[] = '...';
            }
        }

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = "[{$i}]";
        }

        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $pages[] = '...';
            }
            $pages[] = "[{$totalPages}]";
        }

        if ($currentPage < $totalPages) {
            $pages[] = '[Next >]';
        }

        return $output . implode(' ', $pages);
    }
}
