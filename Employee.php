<?php
class Employee {
    public $empID;
    public $fullName;
    public $email;
    public $baseSalary;
    public $actualSalary;
    public $birthday;
    public $departmentName;
    public $positionName;
    public $departmentId;
    public $positionId;

    public function __construct($row) {
        $this->empID          = $row['Mã nhân viên'] ?? '';
        $this->fullName       = $row['Họ tên'] ?? '';
        $this->email          = $row['email'] ?? '';
        $this->baseSalary     = isset($row['Lương cơ bản']) ? (float)$row['Lương cơ bản'] : 0;
        $this->actualSalary   = isset($row['Lương']) ? (float)$row['Lương'] : 0;
        $this->birthday       = $row['Sinh nhật'] ?? '';
        $this->departmentName = $row['Phòng ban'] ?? null;
        $this->positionName   = $row['Chức vụ'] ?? null;
        $this->departmentId   = null;
        $this->positionId     = null;
    }
}