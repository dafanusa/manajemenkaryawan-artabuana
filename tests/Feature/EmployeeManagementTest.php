<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_login_and_view_dashboard(): void
    {
        $user = User::where('role', 'super_admin')->first();
        $this->assertNotNull($user);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertSee('PT Artha Buana Primacoral');
        $dashboard->assertSee('Total Karyawan');
    }

    public function test_super_admin_can_view_all_pages(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();
        $employee = Employee::first();
        $this->assertNotNull($employee);

        // Employee index
        $this->actingAs($superAdmin)->get('/employees')->assertStatus(200)->assertSee('Daftar & Manajemen Data Karyawan');

        // Employee show
        $this->actingAs($superAdmin)->get('/employees/' . $employee->id)->assertStatus(200)->assertSee($employee->full_name);

        // Employee print
        $this->actingAs($superAdmin)->get('/employees/' . $employee->id . '/print')->assertStatus(200)->assertSee('FORM BIODATA KARYAWAN');

        // User management
        $this->actingAs($superAdmin)->get('/users')->assertStatus(200)->assertSee('Daftar Akun Pengguna');

        // Activity log
        $this->actingAs($superAdmin)->get('/activity-logs')->assertStatus(200)->assertSee('Jejak Audit Aktivitas');

        // Settings
        $this->actingAs($superAdmin)->get('/settings')->assertStatus(200)->assertSee('Identitas Perusahaan');
    }

    public function test_admin_cannot_access_super_admin_routes(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Cannot access users
        $this->actingAs($admin)->get('/users')->assertStatus(403);

        // Cannot access activity logs
        $this->actingAs($admin)->get('/activity-logs')->assertStatus(403);

        // Cannot access settings
        $this->actingAs($admin)->get('/settings')->assertStatus(403);

        // Cannot delete employee
        $employee = Employee::first();
        $this->actingAs($admin)->delete('/employees/' . $employee->id)->assertStatus(403);

        // But CAN view employees
        $this->actingAs($admin)->get('/employees')->assertStatus(200);
        $this->actingAs($admin)->get('/employees/' . $employee->id)->assertStatus(200);
    }

    public function test_can_create_employee(): void
    {
        $admin = User::where('role', 'admin')->first();

        $empData = [
            'full_name' => 'Karel Wenda',
            'employee_id' => 'ABP-2026-999',
            'birth_place' => 'Wamena',
            'birth_date' => '1995-03-10',
            'gender' => 'Laki-laki',
            'religion' => 'Kristen',
            'ethnicity' => 'Papua',
            'marital_status' => 'Belum Kawin',
            'nik' => '9102011003950001',
            'phone' => '081234567890',
            'email' => 'karel.wenda@primacoral.com',
            'position' => 'Heavy Equipment Operator',
            'department' => 'Mining Operations',
            'employment_status' => 'Aktif',
            'project_location' => 'Grasberg Mine',
            'work_location' => 'Highland',
            'join_date' => '2026-09-01',
            'basic_salary' => 12000000,
        ];

        $response = $this->actingAs($admin)->post('/employees', $empData);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('employees', [
            'employee_id' => 'ABP-2026-999',
            'full_name' => 'Karel Wenda',
            'ethnicity' => 'Papua',
            'work_location' => 'Highland',
        ]);
    }

    public function test_can_export_csv_and_pdf(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Export CSV
        $responseCsv = $this->actingAs($admin)->get('/employees/export?format=csv');
        $responseCsv->assertStatus(200);
        $responseCsv->assertHeader('content-type', 'text/csv; charset=UTF-8');

        // Export PDF report
        $responsePdf = $this->actingAs($admin)->get('/employees/export?format=pdf');
        $responsePdf->assertStatus(200);
        $responsePdf->assertSee('PT ARTHA BUANA PRIMACORAL');
    }
}

