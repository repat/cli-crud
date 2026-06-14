<?php

namespace Repat\CliCrud\Tests\Unit\Views;

use DateTime;
use Repat\CliCrud\Cards\Card;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\FormType;
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

    public function test_it_formats_backed_enum_as_name(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_value(mixed $value): string
            {
                return $this->formatValue($value);
            }
        };

        $formatted = $renderer->test_format_value(FormType::Draft);

        $this->assertEquals('Draft', $formatted);
    }

    public function test_it_formats_enum_in_json_value(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_json_value(mixed $value, Json $field): string
            {
                return $this->formatJsonValue($value, $field);
            }
        };

        $field = Json::make('Status', 'status');
        $result = $renderer->test_format_json_value(FormType::Draft, $field);

        $this->assertStringContainsString('"Draft"', $result);
    }

    public function test_markdown_textarea_bold_renders_with_ansi(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_value(mixed $value, ?\Repat\CliCrud\Fields\Field $field = null): string
            {
                return $this->formatValue($value, $field);
            }
        };

        $field = Textarea::make('Content', 'content')->markdown();
        $result = $renderer->test_format_value('**bold text**', $field);

        $this->assertStringContainsString("\e[1m", $result);
        $this->assertStringContainsString("bold text", $result);
        $this->assertStringContainsString("\e[22m", $result);
    }

    public function test_markdown_textarea_code_renders_with_ansi(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_value(mixed $value, ?\Repat\CliCrud\Fields\Field $field = null): string
            {
                return $this->formatValue($value, $field);
            }
        };

        $field = Textarea::make('Content', 'content')->markdown();
        $result = $renderer->test_format_value('Use the `run()` method.', $field);

        $this->assertStringContainsString("\e[38;5;244m", $result);
        $this->assertStringContainsString("run()", $result);
        $this->assertStringContainsString("\e[39m", $result);
    }

    public function test_non_markdown_textarea_returns_plain_text(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_value(mixed $value, ?\Repat\CliCrud\Fields\Field $field = null): string
            {
                return $this->formatValue($value, $field);
            }
        };

        $field = Textarea::make('Content', 'content');
        $result = $renderer->test_format_value('**bold**', $field);

        $this->assertSame('**bold**', $result);
    }

    public function test_wrap_text_preserves_paragraphs(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_wrap_text(string $text, int $maxWidth): array
            {
                return $this->wrapText($text, $maxWidth);
            }
        };

        $result = $renderer->test_wrap_text("First paragraph.\n\nSecond paragraph.", 80);

        $this->assertCount(3, $result);
        $this->assertSame('First paragraph.', $result[0]);
        $this->assertSame('', $result[1]);
        $this->assertSame('Second paragraph.', $result[2]);
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

    public function test_json_field_renders_with_syntax_highlighting(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
            'metadata' => '{"key": "value", "count": 42}',
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [
                    Text::make('Title', 'title'),
                    Json::make('Metadata', 'metadata'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        ob_start();
        $this->renderer->render($post, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Metadata', $output);
        $this->assertStringContainsString("\e[36m", $output);
        $this->assertStringContainsString("\e[32m", $output);
        $this->assertStringContainsString("\e[33m", $output);
        $this->assertStringContainsString('"key"', $output);
        $this->assertStringContainsString('"value"', $output);
        $this->assertStringContainsString('42', $output);
    }

    public function test_json_field_renders_without_highlighting_when_disabled(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
            'metadata' => '{"key": "value"}',
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [
                    Text::make('Title', 'title'),
                    Json::make('Metadata', 'metadata')->highlight(false),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        ob_start();
        $this->renderer->render($post, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Metadata', $output);
        $this->assertStringContainsString('"key"', $output);
        $this->assertStringContainsString('"value"', $output);
        $this->assertStringNotContainsString("\e[36m", $output);
        $this->assertStringNotContainsString("\e[32m", $output);
    }

    public function test_json_field_shows_error_for_invalid_json(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_format_json_value(mixed $value, Json $field): string
            {
                return $this->formatJsonValue($value, $field);
            }
        };

        $field = Json::make('Metadata');
        $result = $renderer->test_format_json_value('{invalid json}', $field);

        $this->assertStringContainsString('Invalid JSON', $result);
        $this->assertStringContainsString("\e[31m", $result);
    }

    public function test_json_field_highlights_booleans_and_null(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
            'metadata' => '{"active": true, "deleted": false, "parent": null}',
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [
                    Text::make('Title', 'title'),
                    Json::make('Metadata', 'metadata'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        ob_start();
        $this->renderer->render($post, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString("\e[35mtrue\e[39m", $output);
        $this->assertStringContainsString("\e[35mfalse\e[39m", $output);
        $this->assertStringContainsString("\e[35mnull\e[39m", $output);
    }

    public function test_json_field_handles_array_values(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
            'metadata' => ['tags' => ['php', 'laravel'], 'version' => 2],
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [
                    Text::make('Title', 'title'),
                    Json::make('Metadata', 'metadata'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        ob_start();
        $this->renderer->render($post, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString('"tags"', $output);
        $this->assertStringContainsString('"php"', $output);
        $this->assertStringContainsString('"laravel"', $output);
        $this->assertStringContainsString("\e[33m2\e[39m", $output);
    }

    public function test_cards_render_after_relations_by_default(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }

            public static function cards(): array
            {
                return [
                    Card::metric('Total Users', fn () => 42),
                ];
            }
        };

        ob_start();
        $this->renderer->render($user, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Total Users', $output);
        $this->assertStringContainsString('42', $output);
    }

    public function test_cards_with_before_render_before_relations(): void
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

        $resource = new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }

            public static function cards(): array
            {
                return [
                    Card::metric('Total Users', fn () => 42)->before(),
                ];
            }
        };

        ob_start();
        $this->renderer->render($user, $resource);
        $output = ob_get_clean();

        $this->assertStringContainsString('Total Users', $output);
        $this->assertStringContainsString('42', $output);
    }

    public function test_empty_cards_does_not_break_rendering(): void
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
        $this->assertStringContainsString('╭', $output);
    }

    public function test_mb_str_pad_with_ascii_characters(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_mb_str_pad(string $input, int $length, string $pad_string = ' '): string
            {
                return $this->mb_str_pad($input, $length, $pad_string);
            }
        };

        $result = $renderer->test_mb_str_pad('Hello', 10);
        $this->assertEquals('Hello     ', $result);
        $this->assertEquals(10, mb_strlen($result));
    }

    public function test_mb_str_pad_with_multibyte_characters(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_mb_str_pad(string $input, int $length, string $pad_string = ' '): string
            {
                return $this->mb_str_pad($input, $length, $pad_string);
            }
        };

        $result = $renderer->test_mb_str_pad('Geschäftsführung', 20);
        $this->assertEquals(20, mb_strlen($result));
        $this->assertStringStartsWith('Geschäftsführung', $result);
    }

    public function test_mb_str_pad_with_string_already_at_length(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_mb_str_pad(string $input, int $length, string $pad_string = ' '): string
            {
                return $this->mb_str_pad($input, $length, $pad_string);
            }
        };

        $result = $renderer->test_mb_str_pad('Hello', 5);
        $this->assertEquals('Hello', $result);
    }

    public function test_mb_str_pad_with_string_longer_than_length(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_mb_str_pad(string $input, int $length, string $pad_string = ' '): string
            {
                return $this->mb_str_pad($input, $length, $pad_string);
            }
        };

        $result = $renderer->test_mb_str_pad('Hello World', 5);
        $this->assertEquals('Hello World', $result);
    }

    public function test_mb_str_pad_with_custom_pad_string(): void
    {
        $renderer = new class extends DetailViewRenderer
        {
            public function test_mb_str_pad(string $input, int $length, string $pad_string = ' '): string
            {
                return $this->mb_str_pad($input, $length, $pad_string);
            }
        };

        $result = $renderer->test_mb_str_pad('Hi', 10, '-');
        $this->assertEquals('Hi--------', $result);
    }

    public function test_rendering_with_multibyte_characters_aligns_borders(): void
    {
        $user = User::create([
            'name' => 'Geschäftsführung',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        ob_start();
        $this->renderer->render($user, new UserResource);
        $output = ob_get_clean();

        $lines = explode("\n", trim($output));

        $borderLines = array_filter($lines, fn ($line) => str_contains($line, '│'));

        // Strip ANSI codes before measuring length
        $lengths = array_map(fn ($line) => mb_strlen(preg_replace('/\e\[[0-9;]*m/', '', $line)), $borderLines);

        $this->assertCount(1, array_unique($lengths), 'All border lines should have the same length');
    }

    public function test_rendering_with_multibyte_characters_in_title(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Benutzer';

            protected static string $singularLabel = 'Benutzer';

            public static function fields(): array
            {
                return [
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name', 'email'];
            }
        };

        ob_start();
        $this->renderer->render($user, $resource);
        $output = ob_get_clean();

        $lines = explode("\n", trim($output));

        $borderLines = array_filter($lines, fn ($line) => str_contains($line, '│'));

        // Strip ANSI codes before measuring length
        $lengths = array_map(fn ($line) => mb_strlen(preg_replace('/\e\[[0-9;]*m/', '', $line)), $borderLines);

        $this->assertCount(1, array_unique($lengths), 'All border lines should have the same length');
    }
}
