<?php

namespace Tests\Feature;

use App\Enums\TechnologyCategory;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TechnologyTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_paginated_list_and_selected_filters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Technology::factory()->count(15)->create([
            'category' => TechnologyCategory::BACKEND->value,
        ]);

        Technology::factory()->create([
            'name' => 'Vue.js',
            'slug' => 'vue',
            'category' => TechnologyCategory::FRONTEND->value,
        ]);

        $response = $this->get(route('technologies.index', [
            'name' => 'Vue',
            'category' => TechnologyCategory::FRONTEND->value,
            'per_page' => 10,
        ]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('technologies/Index')
                ->where('filters.name', 'Vue')
                ->where('filters.category', TechnologyCategory::FRONTEND->value)
                ->has('categories')
                ->has('technologies.data', 1)
                ->where('technologies.per_page', 10)
                ->has('technologies.links')
                ->where('technologies.data.0.name', 'Vue.js')
            );
    }

    public function test_index_uses_default_per_page_when_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Technology::factory()->count(3)->create();

        // per_page is invalid -> falls back to 10 via Request::integer('per_page', 10)
        $response = $this->get(route('technologies.index', ['per_page' => 'abc']));
        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('technologies.per_page')
                ->where('technologies.per_page', 10)
                ->has('technologies.data')
                ->has('technologies.links')
            );
    }

    public function test_displays_form_with_categories()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('technologies.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('technologies/Create')
                ->has('categories')
            );
    }

    public function test_store_persists_and_redirects_with_flash()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'name' => 'Laravel',
            'slug' => 'laravel',
            'category' => TechnologyCategory::BACKEND->value,
        ];

        $response = $this->post(route('technologies.store'), $payload);

        $response->assertRedirect(route('technologies.index'))
            ->assertSessionHas('message', 'Skill created successfully!');

        $this->assertDatabaseHas('technologies', $payload);
    }

    public function test_store_fails_validation_and_returns_errors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $payload = [
            'name' => '',
            'slug' => '',
            'category' => 'not-a-valid',
        ];

        $response = $this->from(route('technologies.create'))
            ->post(route('technologies.store'), $payload);

        $response->assertSessionHasErrors(['name', 'slug', 'category']);
        $this->assertDatabaseCount('technologies', 0);
    }

    public function test_edit_displays_form_with_technology_and_categories()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tech = Technology::factory()->create();

        $this->get(route('technologies.edit', $tech))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('technologies/Edit')
                ->has('technology', fn (Assert $t) => $t
                    ->where('id', $tech->id)
                    ->where('name', $tech->name)
                    ->etc()
                )
                ->has('categories')
            );
    }

    public function test_update_persists_changes_and_redirects_with_flash()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tech = Technology::factory()->create([
            'name' => 'Old',
            'slug' => 'old',
            'category' => TechnologyCategory::BACKEND->value,
        ]);

        $payload = [
            'name' => 'New',
            'slug' => 'new',
            'category' => TechnologyCategory::FRONTEND->value,
        ];

        $response = $this->put(route('technologies.update', $tech), $payload);

        $response->assertRedirect(route('technologies.index'))
            ->assertSessionHas('message', 'Skill updated successfully!');

        $this->assertDatabaseHas('technologies', array_merge(['id' => $tech->id], $payload));
    }

    public function test_update_fails_validation_and_returns_errors()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tech = Technology::factory()->create();

        $payload = [
            'name' => '',
            'slug' => '',
            'category' => 'nope',
        ];

        $response = $this->from(route('technologies.edit', $tech))
            ->put(route('technologies.update', $tech), $payload);

        $response->assertSessionHasErrors(['name', 'slug', 'category']);
        $this->assertDatabaseMissing('technologies', ['id' => $tech->id, 'name' => '']);
    }

    public function test_destroy_deletes_and_redirects_with_flash()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tech = Technology::factory()->create();

        $response = $this->delete(route('technologies.destroy', $tech));

        $response->assertRedirect(route('technologies.index'))
            ->assertSessionHas('message', 'Skill deleted successfully!');

        $this->assertDatabaseMissing('technologies', ['id' => $tech->id]);
    }

    public function test_filter_integration_filters_by_name_like()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Technology::factory()->create(['name' => 'Laravel Sail', 'slug' => 'sail', 'category' => TechnologyCategory::BACKEND->value]);
        Technology::factory()->create(['name' => 'Vue.js', 'slug' => 'vue', 'category' => TechnologyCategory::FRONTEND->value]);

        $this->get(route('technologies.index', ['name' => 'Vue']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('technologies.data', 1)
                ->where('technologies.data.0.name', 'Vue.js')
            );
    }
}
