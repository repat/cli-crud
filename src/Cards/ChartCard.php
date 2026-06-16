<?php

namespace Repat\CliCrud\Cards;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Support\Chart;

class ChartCard extends Card
{
    protected string $chartType = 'bar';

    protected bool $showPercentages = false;

    public function __construct(
        string $title,
        protected Closure $dataResolver
    ) {
        parent::__construct($title);
    }

    public function bar(): static
    {
        $this->chartType = 'bar';

        return $this;
    }

    public function horizontalBar(): static
    {
        $this->chartType = 'horizontalBar';

        return $this;
    }

    public function scatter(): static
    {
        $this->chartType = 'scatter';

        return $this;
    }

    /**
     * Show percentages in addition to (replacing) raw values for bar and
     * horizontalBar charts.
     */
    public function percentage(): static
    {
        $this->showPercentages = true;

        return $this;
    }

    public function getChartType(): string
    {
        return $this->chartType;
    }

    public function shouldShowPercentages(): bool
    {
        return $this->showPercentages;
    }

    public function render(Model $model, Resource $resource): string
    {
        $data = ($this->dataResolver)($model, $resource);

        $chart = match ($this->chartType) {
            'horizontalBar' => Chart::horizontalBar($data, null, 60, $this->showPercentages),
            'scatter' => Chart::scatter($data, null, 60),
            default => Chart::bar($data, null, 40, $this->showPercentages),
        };

        return $this->renderBox(rtrim($chart, "\n"));
    }
}
