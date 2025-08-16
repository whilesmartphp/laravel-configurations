<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase;
use Whilesmart\ModelConfiguration\Enums\ConfigValueType;
use Workbench\App\Models\User;

use function Orchestra\Testbench\workbench_path;

#[WithMigration]
class ConfigurationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_can_get_all_configurations()
    {
        $user = $this->createUser();

        // Create some test configurations for the user
        $user->configurations()->create([
            'key' => 'theme_preference',
            'type' => 'array',
            'value' => ['theme' => 'dark', 'color' => '#333333'],
        ]);

        $user->configurations()->create([
            'key' => 'notification_settings',
            'value' => ['email' => true, 'push' => false],
        ]);

        // Make the request to get all configurations
        $response = $this->actingAs($user)->getJson('/configurations');

        // Assert the response is successful and has the correct structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'configurable_type',
                        'configurable_id',
                        'key',
                        'value',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // Assert that the response contains the created configurations
        $this->assertCount(2, $response->json('data'));
    }

    protected function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'email' => Factory::create()->unique()->safeEmail,
            'name' => Factory::create()->unique()->name,
            'password' => Hash::make('password123'),
        ], $attributes));
    }

    public function test_api_user_can_add_configuration()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/configurations', [
            'key' => 'theme_preference',
            'type' => 'array',
            'value' => ['theme' => 'dark', 'color' => '#333333'],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        // Assert the configuration was stored in the database
        $this->assertDatabaseHas('configurations', [
            'configurable_id' => $user->id,
            'key' => 'theme_preference',
        ]);
    }

    public function test_api_user_can_update_configuration()
    {
        $user = $this->createUser();

        // Create a configuration to update
        $configuration = $user->setConfigValue('theme_preference', ['theme' => 'dark', 'color' => '#333333'], ConfigValueType::Array);

        // Update the configuration
        $response = $this->actingAs($user)->putJson('/configurations/'.$configuration->key, [
            'value' => ['theme' => 'light', 'color' => '#ffffff'],
            'type' => 'array',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        // Assert the configuration was updated in the database
        $this->assertDatabaseHas('configurations', [
            'configurable_id' => $user->id,
            'key' => 'theme_preference',
        ]);

        // Refresh the configuration from the database
        $configuration->refresh();

        // Assert the value was updated
        $this->assertEquals('light', $configuration->value['theme']);
        $this->assertEquals('#ffffff', $configuration->value['color']);
    }

    public function test_api_user_can_delete_configuration()
    {
        $user = $this->createUser();

        // Create a configuration to delete
        $configuration = $user->configurations()->create([
            'key' => 'theme_preference_color',
            'type' => 'string',
            'value' => '#333333',
        ]);

        // Delete the configuration
        $response = $this->actingAs($user)->deleteJson('/configurations/'.$configuration->key);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        // Assert the configuration was deleted from the database
        $this->assertDatabaseMissing('configurations', [
            'id' => $configuration->id,
        ]);
    }

    public function test_api_user_cannot_update_nonexistent_configuration()
    {
        $user = $this->createUser();

        // Try to update a configuration that doesn't exist
        $response = $this->actingAs($user)->putJson('/configurations/999999', [
            'value' => 2,
            'type' => 'int',
        ]);

        $response->assertStatus(404);
    }

    public function test_api_user_cannot_delete_nonexistent_configuration()
    {
        $user = $this->createUser();

        // Try to delete a configuration that doesn't exist
        $response = $this->actingAs($user)->deleteJson('/configurations/999999');

        $response->assertStatus(404);
    }

    public function test_api_user_cannot_access_another_users_configurations()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();

        // Create a configuration for user1
        $configuration = $user1->configurations()->create([
            'key' => 'theme_preference',
            'type' => 'float',
            'value' => 2.45,
        ]);

        // Try to access user1's configuration as user2
        $response = $this->actingAs($user2)->getJson('/configurations');

        $response->assertStatus(200);
        $this->assertEmpty($response->json('data'));
    }

    public function unauthorized_user_cannot_access_configurations()
    {
        // Try to access configurations without authentication
        $response = $this->getJson('/configurations');
        $response->assertStatus(401);
    }

    public function test_api_user_cannot_add_configuration_with_missing_key()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/configurations', [
            'value' => ['theme' => 'dark', 'color' => '#333333'],
            'type' => 'array',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_user_cannot_add_configuration_with_missing_value()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/configurations', [
            'key' => 'theme_preference',
            'type' => 'string',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_user_cannot_update_configuration_with_missing_value()
    {
        $user = $this->createUser();

        // Create a configuration to update
        $configuration = $user->configurations()->create([
            'key' => 'theme_preference',
            'value' => ['theme' => 'dark', 'color' => '#333333'],
            'type' => 'array',
        ]);

        // Try to update without providing a value
        $response = $this->actingAs($user)->putJson('/configurations/theme_preference', []);

        $response->assertStatus(422);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            workbench_path('database/migrations')
        );
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            'Whilesmart\ModelConfiguration\ConfigurationServiceProvider',
        ];
    }
}
