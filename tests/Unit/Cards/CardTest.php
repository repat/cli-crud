<?php

namespace Repat\CliCrud\Tests\Unit\Cards;

use Repat\CliCrud\Cards\Card;
use Repat\CliCrud\Cards\ChartCard;
use Repat\CliCrud\Cards\CustomCard;
use Repat\CliCrud\Cards\MetricCard;
use Repat\CliCrud\Tests\TestCase;

class CardTest extends TestCase
{
    public function test_metric_factory_creates_metric_card(): void
    {
        $card = Card::metric('Test', fn () => 42);

        $this->assertInstanceOf(MetricCard::class, $card);
        $this->assertEquals('Test', $card->getTitle());
    }

    public function test_chart_factory_creates_chart_card(): void
    {
        $card = Card::chart('Test', fn () => ['A' => 1]);

        $this->assertInstanceOf(ChartCard::class, $card);
        $this->assertEquals('Test', $card->getTitle());
    }

    public function test_custom_factory_creates_custom_card(): void
    {
        $card = Card::custom('Test', fn () => 'content');

        $this->assertInstanceOf(CustomCard::class, $card);
        $this->assertEquals('Test', $card->getTitle());
    }

    public function test_default_position_is_after(): void
    {
        $card = Card::metric('Test', fn () => 42);

        $this->assertEquals('after', $card->getPosition());
    }

    public function test_before_sets_position_to_before(): void
    {
        $card = Card::metric('Test', fn () => 42)->before();

        $this->assertEquals('before', $card->getPosition());
    }

    public function test_after_sets_position_to_after(): void
    {
        $card = Card::metric('Test', fn () => 42)->before()->after();

        $this->assertEquals('after', $card->getPosition());
    }

    public function test_before_returns_static_for_chaining(): void
    {
        $card = Card::metric('Test', fn () => 42);
        $result = $card->before();

        $this->assertSame($card, $result);
    }

    public function test_after_returns_static_for_chaining(): void
    {
        $card = Card::metric('Test', fn () => 42);
        $result = $card->after();

        $this->assertSame($card, $result);
    }
}
