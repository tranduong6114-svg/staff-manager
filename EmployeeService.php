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
            $key = mb_strtolower(trim($row['name']), 'UTF-8');
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
        if (mb_strlen($emp->fullName, 'UTF-8') > 255) {
            $errors[] = "Ho ten vuot qua 255 ky tu";
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
        $existingEmpStmt = $this->conn->prepare("SELECT emp_id FROM employees WHERE emp_id = :emp_id");
        $existingEmpStmt->execute(['emp_id' => $emp->empID]);

        if ($existingEmpStmt->rowCount() > 0) {
            $updateSql = "UPDATE employees SET full_name = :full_name, email = :email, base_salary = :base_salary, actual_salary = :actual_salary, birthday = :birthday, department_id = :department_id, position_id = :position_id WHERE emp_id = :emp_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->execute([
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
            $insertSql = "INSERT INTO employees (emp_id, full_name, email, base_salary, actual_salary, birthday, department_id, position_id) VALUES (:emp_id, :full_name, :email, :base_salary, :actual_salary, :birthday, :department_id, :position_id)";
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->execute([
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
                $csvDeptName = mb_strtolower(trim($emp->departmentName ?? ''), 'UTF-8');
                $csvPosName  = mb_strtolower(trim($emp->positionName ?? ''), 'UTF-8');

                $emp->departmentId = $deptMap[$csvDeptName] ?? null;
                $emp->positionId   = $posMap[$csvPosName] ?? null;

                $errors = $this->validateEmployee($emp);
                if (!empty($errors)) {
                    $errorString = implode(", ", $errors);
                    $displayEmpId = empty($emp->empID) ? "Trong" : $emp->empID;

                    throw new Exception("Loi tai dong so {$lineNumber} (Ma NV: {$displayEmpId}) : {$errorString}");
                }
                $this->saveEmployee($emp);
                $lineNumber++;
            }
            $this->conn->commit();
            return "Import du lieu thanh cong";

        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function calculateInsurance() {
        $insuranceData = [];
        $insuranceSql = "SELECT emp_id, full_name, base_salary FROM employees";
        $insuranceStmt = $this->conn->prepare($insuranceSql);
        $insuranceStmt->execute();

        while ($row = $insuranceStmt->fetch(PDO::FETCH_ASSOC)) {
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

    public function getAverageSalaryUnder30() {
        $sql = "SELECT AVG(actual_salary) as avg_salary 
                FROM employees 
                WHERE TIMESTAMPDIFF(YEAR, birthday, CURDATE()) < 30";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['avg_salary'] !== null) {
            return (float) $row['avg_salary'];
        }
        return 0;
    }

    public function getLeadersData() {
        $sql = "SELECT e.emp_id, e.full_name, d.name AS department_name, p.name AS position_name
                FROM employees e
                INNER JOIN departments d ON e.department_id = d.id
                INNER JOIN positions p ON e.position_id = p.id
                WHERE p.name IN ('Trưởng phòng', 'Phó phòng')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $leadersData = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $leaderRow = [
                $row['emp_id'],
                $row['full_name'],
                $row['department_name'],
                $row['position_name']
            ];
            $leadersData[] = $leaderRow;
        }
        return $leadersData;
    }
}
