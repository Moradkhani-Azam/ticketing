<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('user registration', function () {
    it('allows a guest to register', function () {
        $response = $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    it('hashes the password when registering', function () {
        $password = 'password';

        $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => $password,
            'password_confirmation' => $password,
        ])->assertSuccessful();

        $user = User::where('email', 'john@example.com')->first();

        expect($user)->not->toBeNull()
            ->and(Hash::check($password, $user->password))->toBeTrue()
            ->and($user->password)->not->toBe($password);
    });

    it('does not allow duplicate email addresses', function () {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $this->postJson('/register', [
            'name' => 'Another User',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable();

        expect(User::where('email', 'john@example.com')->count())
            ->toBe(1);
    });

    it('requires a name', function () {
        $this->postJson('/register', [
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    });

    it('requires a valid email address', function () {
        $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('requires a password', function () {
        $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('requires password confirmation to match', function () {
        $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('does not create a user when registration validation fails', function () {
        $this->postJson('/register', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertUnprocessable();

        expect(User::count())->toBe(0);
    });
});