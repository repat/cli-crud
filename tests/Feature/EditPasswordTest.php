<?php

namespace Repat\CliCrud\Tests\Feature;

use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class EditPasswordTest extends TestCase
{
    public function test_empty_password_is_preserved_during_edit(): void
    {
        // Create a user with a password
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('original_password'),
        ]);

        $originalPassword = $user->password;

        // Simulate form data with empty password
        $formData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => '',
        ];

        // Filter out empty password fields (simulating the logic in showEditForm)
        $fields = [
            Text::make('name')->required(),
            Text::make('email')->required(),
            Text::make('password')->password(),
        ];

        foreach ($fields as $field) {
            if ($field instanceof Text && $field->isPassword() && empty($formData[$field->getName()])) {
                unset($formData[$field->getName()]);
            }
        }

        // Update the user
        $user->update($formData);

        // Refresh the user from database
        $user->refresh();

        // Assert password was not changed
        $this->assertEquals($originalPassword, $user->password);
        $this->assertEquals('Jane Doe', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
    }

    public function test_non_empty_password_is_updated_during_edit(): void
    {
        // Create a user with a password
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('original_password'),
        ]);

        $originalPassword = $user->password;
        $newPassword = bcrypt('new_password');

        // Simulate form data with new password
        $formData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => $newPassword,
        ];

        // Filter out empty password fields (simulating the logic in showEditForm)
        $fields = [
            Text::make('name')->required(),
            Text::make('email')->required(),
            Text::make('password')->password(),
        ];

        foreach ($fields as $field) {
            if ($field instanceof Text && $field->isPassword() && empty($formData[$field->getName()])) {
                unset($formData[$field->getName()]);
            }
        }

        // Update the user
        $user->update($formData);

        // Refresh the user from database
        $user->refresh();

        // Assert password was changed
        $this->assertNotEquals($originalPassword, $user->password);
        $this->assertEquals($newPassword, $user->password);
    }

    public function test_other_fields_are_updated_when_password_is_empty(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        // Simulate form data with empty password
        $formData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => '',
        ];

        // Filter out empty password fields
        $fields = [
            Text::make('name')->required(),
            Text::make('email')->required(),
            Text::make('password')->password(),
        ];

        foreach ($fields as $field) {
            if ($field instanceof Text && $field->isPassword() && empty($formData[$field->getName()])) {
                unset($formData[$field->getName()]);
            }
        }

        // Update the user
        $user->update($formData);

        // Refresh the user from database
        $user->refresh();

        // Assert other fields were updated
        $this->assertEquals('Jane Doe', $user->name);
        $this->assertEquals('jane@example.com', $user->email);
    }
}
