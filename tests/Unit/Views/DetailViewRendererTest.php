<?php

namespace Repat\CliCrud\Tests\Unit\Views;

use DateTime;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Views\DetailViewRenderer;

class DetailViewRendererTest extends TestCase
{
    protected DetailViewRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer = new DetailViewRenderer;
    }

    public function test_it_renders_box_with_rounded_corners(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('╭', $output);
        $this->assertStringContainsString('╮', $output);
        $this->assertStringContainsString('╰', $output);
        $this->assertStringContainsString('╯', $output);
        $this->assertStringContainsString('│', $output);
    }

    public function test_it_formats_true_as_green_checkmark(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('✓', $output);
        $this->assertStringContainsString("\e[32m", $output);
    }

    public function test_it_formats_false_as_red_x(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => false,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('✗', $output);
        $this->assertStringContainsString("\e[31m", $output);
    }

    public function test_it_formats_null_as_gray_null(): void
    {
        $post = Post::create([
            'user_id' => User::create([
                'name' => 'John',
                'email' => 'john@example.com',
                'is_active' => true,
            ])->id,
            'title' => 'Test Post',
            'content' => null,
        ]);

        ob_start();
        $this->renderer->render($post, new PostResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('NULL', $output);
        $this->assertStringContainsString("\e[90m", $output);
    }

    public function test_it_formats_dates_correctly(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_value(mixed $value): string
            {
                return $this->formatValue($value);
            }
        };

        $date = new DateTime('2024-01-15 10:30:00');
        $formatted = $renderer->test_format_value($date);

        $this->assertEquals('2024-01-15 10:30:00', $formatted);
    }

    public function test_it_wraps_long_values(): void
    {
        $longContent = str_repeat('word ', 50);
        $post = Post::create([
            'user_id' => User::create([
                'name' => 'John',
                'email' => 'john@example.com',
                'is_active' => true,
            ])->id,
            'title' => 'Test Post',
            'content' => $longContent,
        ]);

        ob_start();
        $this->renderer->render($post, new PostResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);
        $this->assertGreaterThan(5, count($lines));
    }

    public function test_it_truncates_very_long_values(): void
    {
        $veryLongContent = str_repeat('a', 400);
        $post = Post::create([
            'user_id' => User::create([
                'name' => 'John',
                'email' => 'john@example.com',
                'is_active' => true,
            ])->id,
            'title' => 'Test Post',
            'content' => $veryLongContent,
        ]);

        ob_start();
        $this->renderer->render($post, new PostResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('...', $output);
        $this->assertStringNotContainsString(str_repeat('a', 400), $output);
    }

    public function test_it_renders_title_with_model_id(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString("User #{$user->id}", $output);
    }

    public function test_it_renders_field_labels(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Name', $output);
        $this->assertStringContainsString('Email', $output);
        $this->assertStringContainsString('Is Active', $output);
    }

    public function test_it_renders_field_values(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('John Doe', $output);
        $this->assertStringContainsString('john@example.com', $output);
    }

    public function test_it_renders_relation_tables(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'First Post',
            'content' => 'Content 1',
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Second Post',
            'content' => 'Content 2',
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Posts (2)', $output);
        $this->assertStringContainsString('First Post', $output);
        $this->assertStringContainsString('Second Post', $output);
    }

    public function test_it_skips_empty_relations(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Posts', $output);
    }

    public function test_it_shows_pagination_for_relations(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Post::create([
                'user_id' => $user->id,
                'title' => "Post {$i}",
                'content' => "Content {$i}",
            ]);
        }

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Posts (15)', $output);
        $this->assertStringContainsString('Page 1 of', $output);
    }

    public function test_it_renders_table_headers_for_relations(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $this->assertStringContainsString('ID', $output);
        $this->assertStringContainsString('Title', $output);
    }

    public function test_box_width_respects_minimum(): void
    {
        $user = User::create([
            'name' => 'Jo',
            'email' => 'j@e.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);
        $borderLine = $lines[0];
        $this->assertGreaterThanOrEqual(60, mb_strlen($borderLine));
    }

    public function test_box_width_respects_maximum(): void
    {
        $user = User::create([
            'name' => str_repeat('a', 200),
            'email' => str_repeat('b', 200).'@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);
        $borderLine = $lines[0];
        $this->assertLessThanOrEqual(124, mb_strlen($borderLine));
    }

    public function test_separator_aligns_with_header(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);

        // Find header and separator lines
        $headerLine = null;
        $separatorLine = null;
        $dataLine = null;
        foreach ($lines as $line) {
            if (str_contains($line, 'ID') && str_contains($line, 'Title') && str_contains($line, '│')) {
                $headerLine = $line;
            }
            // Separator line has dashes and spaces but NOT the top/bottom border chars
            if (str_contains($line, '────') && ! str_contains($line, '╭') && ! str_contains($line, '╰')) {
                $separatorLine = $line;
            }
            if (str_contains($line, 'Test Post')) {
                $dataLine = $line;
            }
        }

        // Verify both exist
        $this->assertNotNull($headerLine, 'Header line should exist');
        $this->assertNotNull($separatorLine, 'Separator line should exist');

        // Verify same length (character-by-character alignment)
        $this->assertEquals(
            mb_strlen($headerLine),
            mb_strlen($separatorLine),
            'Header and separator must have identical length'
        );

        // Verify right border position
        $this->assertEquals('│', mb_substr($headerLine, -1));
        $this->assertEquals('│', mb_substr($separatorLine, -1));
    }

    public function test_separator_has_gaps_between_columns(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);

        // Find separator line (has multiple groups of dashes separated by spaces)
        $separatorLine = null;
        foreach ($lines as $line) {
            // Look for lines with the pattern: │ ──── ───── ───── │
            // The line should have │ followed by space, then dashes, then spaces, then dashes
            if (str_contains($line, '│') && str_contains($line, '────') && preg_match('/\s{2,}/', $line)) {
                // Make sure it's not a border line
                if (! str_contains($line, '╭') && ! str_contains($line, '╰') && ! str_contains($line, '├')) {
                    $separatorLine = $line;
                    break;
                }
            }
        }

        $this->assertNotNull($separatorLine, 'Separator line should exist');

        // Verify it contains spaces (gaps) between dashes
        $this->assertMatchesRegularExpression('/─+\s+─+/', $separatorLine);
    }

    public function test_relation_table_with_many_columns(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", $output);

        // Find header line
        $headerLine = null;
        foreach ($lines as $line) {
            if (str_contains($line, 'ID') && str_contains($line, 'Title') && str_contains($line, 'Created at')) {
                $headerLine = $line;
                break;
            }
        }

        $this->assertNotNull($headerLine, 'Header line with all columns should exist');

        // Verify the line ends with the right border
        $this->assertEquals('│', mb_substr($headerLine, -1));
    }
}
