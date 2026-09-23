<?php

use App\Models\User;

describe('user logout', function () {
    it('allows an authenticated user to logout', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertAuthenticatedAs($user);

        $this->postJson('/logout')
            ->assertSuccessful();

        $this->assertGuest();
    });

    it('requires authentication to logout', function () {
        $this->postJson('/logout')
            ->assertUnauthorized();
    });
});