<?php
require_once 'config/Database.php';
require_once 'CsvHandler.php';
require_once 'Employee.php';
require_once 'EmployeeService.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $csvHandler = new CsvHandler();
    $csvData = $csvHandler->readCsv('import/nhanvien.csv');

    $employees = [];
    foreach ($csvData as $row) {
        $emp = new Employee($row);
        $employees[] = $emp;
    }

    $employeeService = new EmployeeService($conn);
    $result = $employeeService->importEmployees($employees);

    echo $result;
    echo "<br>";

    $insuranceData = $employeeService->calculateInsurance();
    $headerBHXH = ['Ma Nhan vien', 'Ho ten', 'Luong co ban', 'Tong BHXH'];
    $exportResult = $csvHandler->exportCsv($insuranceData, 'export', 'output_bhxh.csv', $headerBHXH);

    echo $exportResult . "<br>";

    $allTaxData = $employeeService->calculateAllTax();
    $headerTTNCN = ['Ma Nhan vien', 'Ho ten', 'Luong', 'Thue phai dong'];
    $exportTaxResult = $csvHandler->exportCsv($allTaxData, 'export', 'output_tax.csv', $headerTTNCN);

    echo "CSV 1: " . $exportTaxResult . "<br>";

    usort($allTaxData, function ($a, $b) {
        return $b[3] <=> $a[3];
    });

    $top3TaxData = array_slice($allTaxData, 0, 3);
    $exportTaxTop3 = $csvHandler->exportCsv($top3TaxData, 'export', 'output_tax_top3.csv', $headerTTNCN);
    echo "CSV 2: " . $exportTaxTop3 . "<br>";

} catch (Exception $e) {
    echo "Import that bai.<br> Loi : " . $e->getMessage();
}