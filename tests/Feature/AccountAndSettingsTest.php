<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->staff = User::factory()->create(['role_id' => Role::where('name', Role::STAFF)->value('id')]);
    $this->admin = User::factory()->create(['role_id' => Role::where('name', Role::ADMIN)->value('id')]);
    $this->customer = User::factory()->create(['role_id' => Role::where('name', Role::CUSTOMER)->value('id')]);
});

test('staff is blocked from all fase 3 admin-only areas', function () {
    $this->actingAs($this->staff)->get('/admin/pengaturan')->assertForbidden();
    $this->actingAs($this->staff)->get('/admin/pengguna')->assertForbidden();
    $this->actingAs($this->staff)->get('/admin/staff')->assertForbidden();
    $this->actingAs($this->staff)->get('/admin/laporan')->assertForbidden();
});

test('admin can update settings and the new value is readable via Setting::get', function () {
    $this->actingAs($this->admin)->put('/admin/pengaturan', [
        'bank_name' => 'Bank Mandiri',
        'bank_account_number' => '999888777',
        'bank_account_name' => 'PT We Rent Car Batam',
        'whatsapp_number' => '6281200000001',
        'homepage_hero_title' => 'Judul Baru',
        'homepage_hero_subtitle' => 'Subjudul Baru',
        'cancellation_policy' => 'Kebijakan baru.',
    ])->assertRedirect();

    expect(Setting::get('bank_name'))->toBe('Bank Mandiri');
});

test('admin can activate and delete a customer account', function () {
    $this->actingAs($this->admin)->patch("/admin/pengguna/{$this->customer->id}/status")->assertRedirect();
    $this->customer->refresh();
    expect($this->customer->status)->toBe('inactive');

    $this->actingAs($this->admin)->delete("/admin/pengguna/{$this->customer->id}")->assertRedirect();
    $this->assertSoftDeleted('users', ['id' => $this->customer->id]);
});

test('admin can create a new staff account and that staff can log in', function () {
    $this->actingAs($this->admin)->post('/admin/staff', [
        'name' => 'Staff Baru',
        'email' => 'staffbaru@werentcar.com',
        'phone' => '0800000000',
        'password' => 'password123',
    ])->assertRedirect(route('admin.staff.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'staffbaru@werentcar.com',
        'role_id' => Role::where('name', Role::STAFF)->value('id'),
    ]);

    $this->post('/logout');

    $this->post('/login', [
        'email' => 'staffbaru@werentcar.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.dashboard'));
});

test('admin can assign an additional permission to the staff role and staff gets it immediately', function () {
    expect($this->staff->hasPermission('reports.view_full'))->toBeFalse();

    $staffRoleId = Role::where('name', Role::STAFF)->value('id');
    $currentIds = Role::find($staffRoleId)->permissions()->pluck('permissions.id')->all();
    $reportsPermissionId = Permission::where('slug', 'reports.view_full')->value('id');

    $this->actingAs($this->admin)->put('/admin/staff-permissions', [
        'permission_ids' => [...$currentIds, $reportsPermissionId],
    ])->assertRedirect();

    $this->staff->refresh();
    expect($this->staff->hasPermission('reports.view_full'))->toBeTrue();

    $this->actingAs($this->staff)->get('/admin/laporan')->assertStatus(200);
});
