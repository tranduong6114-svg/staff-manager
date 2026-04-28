<?php
class EmployeeService {
    private $conn;
    public function __construct($db) {
        $this->conn = $db;
    }
    private function getDepartmentMap() {
        $stmt = $this->conn->prepare("SELECT id, name FROM departments");
        $stmt->execute();
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = mb_strtolower(trim($row['name']), 'UTF-8'); // Chuẩn hóa tên trong DB
            $map[$key] = $row['id'];
        }
        return $map;
    }
    private function getPositionMap() {
        $stmt = $this->conn->prepare("SELECT id, name FROM positions");
        $stmt->execute();
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $key = mb_strtolower(trim($row['name']), 'UTF-8');
            $map[$key] = $row['id'];
        }
        return $map;
    }
    public function validateEmployee(Employee $emp) {
        $errors = [];
        if (strlen($emp->fullName) > 100) {
            $errors[] = "Ho ten vuot qua 100 ky tu";
        }
        if (!filter_var($emp->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email khong hop le";
        }
        if ($emp->baseSalary > $emp->actualSalary) {
            $errors[] = "Luong co ban lon hon luong thuc nhan";
        }
        $date = DateTime::createFromFormat('Y-m-d', $emp->birthday);
        if (!$date || $date->format('Y-m-d') !== $emp->birthday) {
            $errors[] = "Ngay sinh sai dinh dang";
        }
        if ($emp->departmentId === null) {
            $errors[] = "Ten phong ban khong ton tai trong he thong";
        }
        if ($emp->positionId === null) {
            $errors[] = "Ten chuc vu khong ton tai trong he thong";
        }
        return $errors;
    }
    public function saveEmployee(Employee $emp) {
        $check_stmt = $this->conn->prepare("SELECT emp_id FROM employees WHERE emp_id = :emp_id");
        $check_stmt->execute(['emp_id' => $emp->empID]);
        if ($check_stmt->rowCount() > 0) {
            $update_sql = "UPDATE employees SET full_name = :full_name, email = :email, base_salary = :base_salary, actual_salary = :actual_salary, birthday = :birthday, department_id = :department_id, position_id = :position_id WHERE emp_id = :emp_id";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->execute([
                'full_name' => $emp->fullName,
                'email' => $emp->email,
                'base_salary' => $emp->baseSalary,
                'actual_salary' => $emp->actualSalary,
                'birthday' => $emp->birthday,
                'department_id' => $emp->departmentId,
                'position_id' => $emp->positionId,
                'emp_id' => $emp->empID
            ]);
        } else {
            $insert_sql = "INSERT INTO employees (emp_id, full_name, email, base_salary, actual_salary, birthday, department_id, position_id) VALUES (:emp_id, :full_name, :email, :base_salary, :actual_salary, :birthday, :department_id, :position_id)";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->execute([
                'emp_id' => $emp->empID,
                'full_name' => $emp->fullName,
                'email' => $emp->email,
                'base_salary' => $emp->baseSalary,
                'actual_salary' => $emp->actualSalary,
                'birthday' => $emp->birthday,
                'department_id' => $emp->departmentId,
                'position_id' => $emp->positionId,
            ]);
        }
    }
    public function importEmployees($employees) {
        $deptMap = $this->getDepartmentMap();
        $posMap = $this->getPositionMap();
        $this->conn->beginTransaction();
        try {
            $lineNumber = 2;
            foreach ($employees as $emp) {
                $csvDeptName = mb_strtolower(trim($emp->departmentId ?? ''), 'UTF-8');
                $csvPosName  = mb_strtolower(trim($emp->positionId ?? ''), 'UTF-8');

                $emp->departmentId = $deptMap[$csvDeptName] ?? null;
                $emp->positionId   = $posMap[$csvPosName] ?? null;

                $errors = $this->validateEmployee($emp);
                if (!empty($errors)) {
                    $errorString = implode(", ", $errors);
                    $maNvHienThi = empty($emp->empID) ? "Trống" : $emp->empID;

                    throw new Exception("Loi tai dong so {$lineNumber} (Ma NV: {$maNvHienThi}) : {$errorString}");
                }

                $this->saveEmployee($emp);

                $lineNumber++;
            }

            $this->conn->commit();
            return "Impor du lieu thanh cong";

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    public function calculateInsurance() {
        $insuranceData = [];
        $calculate_sql = "SELECT emp_id, full_name, base_salary FROM employees";
        $calculate_stmt = $this->conn->prepare($calculate_sql);
        $calculate_stmt->execute();
        while ($row = $calculate_stmt->fetch(PDO::FETCH_ASSOC)) {
            $baseSalary = (float) $row['base_salary'];
            $totalInsurance = $baseSalary * 0.105;
            $insuranceRow = [
                $row['emp_id'],
                $row['full_name'],
                $baseSalary,
                $totalInsurance
            ];
            $insuranceData[] = $insuranceRow;
        }
        return $insuranceData;
    }
    public function calculateAllTax() {
        $allTaxData = [];
        $taxSql = "SELECT emp_id, full_name, actual_salary FROM employees";
        $taxStmt = $this->conn->prepare($taxSql);
        $taxStmt->execute();

        while ($row = $taxStmt->fetch(PDO::FETCH_ASSOC)) {
            $actualSalary = (float) $row['actual_salary'];

            $taxableIncome = $actualSalary - 11000000;

            $taxAmount = 0;

            if ($taxableIncome > 0) {
                if ($taxableIncome <= 5000000) {
                    $taxAmount = $taxableIncome * 0.05;
                } elseif ($taxableIncome <= 10000000) {
                    $taxAmount = (5000000 * 0.05) + (($taxableIncome - 5000000) * 0.10);
                } else {
                    $taxAmount = (5000000 * 0.05) + (5000000 * 0.10) + (($taxableIncome - 10000000) * 0.15);
                }
            }

            $taxRow = [
                $row['emp_id'],
                $row['full_name'],
                $actualSalary,
                $taxAmount
            ];
            $allTaxData[] = $taxRow;
        }
        return $allTaxData;
    }
}