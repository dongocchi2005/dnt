<?php

use App\Models\User;

test('admin can change role from admin to user', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetUser = User::factory()->create(['is_admin' => true]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.show', $targetUser, absolute: false))
        ->put(route('admin.users.update', $targetUser, absolute: false), [
            'role' => 'user',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'is_admin' => 0,
    ]);
});

test('admin can change role from user to admin', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetUser = User::factory()->create(['is_admin' => false]);

    $response = $this
        ->actingAs($admin)
        ->from(route('admin.users.show', $targetUser, absolute: false))
        ->put(route('admin.users.update', $targetUser, absolute: false), [
            'role' => 'admin',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $targetUser->id,
        'is_admin' => 1,
    ]);
});

