<?php

namespace Tests\Unit;

use App\Models\Employee;
use PHPUnit\Framework\TestCase;

class TransjakartaEmployeeTest extends TestCase
{
    public function test_transjakarta_regions_definition(): void
    {
        $this->assertArrayHasKey('jakarta_timur', Employee::REGIONS);
        $this->assertArrayHasKey('jakarta_barat', Employee::REGIONS);
        $this->assertArrayHasKey('jakarta_pusat', Employee::REGIONS);
        $this->assertArrayHasKey('jakarta_utara', Employee::REGIONS);
        $this->assertArrayHasKey('jakarta_selatan', Employee::REGIONS);
        $this->assertCount(5, Employee::REGIONS);
    }

    public function test_transjakarta_employment_statuses(): void
    {
        $this->assertArrayHasKey('tetap', Employee::EMPLOYMENT_STATUSES);
        $this->assertArrayHasKey('vendor', Employee::EMPLOYMENT_STATUSES);
        $this->assertArrayHasKey('magang', Employee::EMPLOYMENT_STATUSES);
        $this->assertEquals('Karyawan Tetap', Employee::EMPLOYMENT_STATUSES['tetap']);
        $this->assertEquals('Vendor / Mitra', Employee::EMPLOYMENT_STATUSES['vendor']);
        $this->assertEquals('Karyawan Magang', Employee::EMPLOYMENT_STATUSES['magang']);
    }

    public function test_transjakarta_positions_structure(): void
    {
        $this->assertArrayHasKey('Pramudi', Employee::POSITIONS);
        $this->assertArrayHasKey('Pramusapa', Employee::POSITIONS);
        $this->assertArrayHasKey('Pramujaga', Employee::POSITIONS);
        $this->assertArrayHasKey('Karyawan Kantor', Employee::POSITIONS);
    }

    public function test_transjakarta_pools_exist_for_all_regions(): void
    {
        foreach (array_keys(Employee::REGIONS) as $regionKey) {
            $this->assertArrayHasKey($regionKey, Employee::POOLS);
            $this->assertNotEmpty(Employee::POOLS[$regionKey]);
        }
    }

    public function test_employee_label_accessors(): void
    {
        $employee = new Employee;
        $employee->region = 'jakarta_timur';
        $employee->employment_status = 'tetap';

        $this->assertEquals('Jakarta Timur', $employee->region_label);
        $this->assertEquals('Karyawan Tetap', $employee->employment_status_label);
    }

    public function test_employee_nik_accessor_returns_employee_code(): void
    {
        $employee = new Employee;
        $employee->employee_code = 'TJT1001';

        $this->assertEquals('TJT1001', $employee->nik);
    }
}
