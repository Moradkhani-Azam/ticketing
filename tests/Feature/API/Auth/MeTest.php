<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('get authenticated user', function () {
    it('returns 401 for a guest', function () {
        $this->getJson('/api/me')
            ->assertUnauthorized();
    });

    it('returns the authenticated user with roles and permissions', function () {

        $user = User::factory()
            ->adminLevelOne()
            ->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', $user->name)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.is_admin', true)
            ->assertJsonPath('data.roles', ['admin_level_1'])
            ->assertJsonPath('data.permissions', [
                'ticket.view-all',
                'ticket.approve',
                'ticket.reject',
                'ticket.bulk-approve',
            ]);
    });

    it('returns true for is_admin for a level two admin', function () {
        $user = User::factory()
            ->adminLevelTwo()
            ->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.user.is_admin', true)
            ->assertJsonPath('data.roles', ['admin_level_2']);
    });

    it('returns empty roles and permissions for a regular user', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.is_admin', false)
            ->assertJsonPath('data.roles', [])
            ->assertJsonPath('data.permissions', []);
    });
});
