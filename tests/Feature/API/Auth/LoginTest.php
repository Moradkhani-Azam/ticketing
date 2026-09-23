<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('user login', function () {
    it('allows a user to login with valid credentials', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSuccessful();

        $this->assertAuthenticatedAs($user);
    });

    it('rejects an invalid password', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email'
            ]);

        $this->assertGuest();
    });

    it('rejects an unknown email', function () {
        $this->postJson('/login', [
            'email' => 'unknown@example.com',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email'
            ]);

        $this->assertGuest();
    });

    it('requires an email', function () {
        $this->postJson('/login', [
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('requires a password', function () {
        $user = User::factory()->create();

        $this->postJson('/login', [
            'email' => $user->email,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('requires a valid email address', function () {
        $this->postJson('/login', [
            'email' => 'not-an-email',
            'password' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('throttles login attempts after five requests', function () {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->postJson('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])->assertUnprocessable();
        }

        $this->postJson('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    });
});
