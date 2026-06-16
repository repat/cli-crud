<?php

namespace Repat\CliCrud\Tests\Unit\Validation;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Validator;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Validation\MorphExists;

class MorphExistsTest extends TestCase
{
    public function test_passes_when_type_and_id_reference_existing_record(): void
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
        ]);

        $validator = Validator::make(
            [
                'commentable_type' => Post::class,
                'commentable_id' => $post->id,
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_passes_when_id_is_null_and_field_is_nullable(): void
    {
        $validator = Validator::make(
            [
                'commentable_type' => null,
                'commentable_id' => null,
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_passes_when_id_is_empty_string(): void
    {
        $validator = Validator::make(
            [
                'commentable_type' => Post::class,
                'commentable_id' => '',
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertFalse($validator->fails());
    }

    public function test_fails_when_id_does_not_exist(): void
    {
        $validator = Validator::make(
            [
                'commentable_type' => Post::class,
                'commentable_id' => 99999,
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->get('commentable_id');
        $this->assertNotEmpty($errors);
    }

    public function test_fails_when_type_references_unknown_class(): void
    {
        $validator = Validator::make(
            [
                'commentable_type' => 'NonExistent\\Class',
                'commentable_id' => 1,
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_fails_when_id_is_set_but_type_is_null(): void
    {
        $validator = Validator::make(
            [
                'commentable_type' => null,
                'commentable_id' => 1,
            ],
            [
                'commentable_id' => [new MorphExists('commentable_type', 'commentable_id')],
            ],
        );

        $this->assertTrue($validator->fails());
    }

    public function test_resolves_morphmap_alias_to_model_class(): void
    {
        Relation::morphMap(['post_alias' => Post::class]);

        $resolved = Relation::getMorphedModel('post_alias');

        Relation::$morphMap = [];

        $this->assertEquals(Post::class, $resolved);
    }
}
