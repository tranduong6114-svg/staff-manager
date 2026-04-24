<?php
require_once 'config/Database.php';
require_once 'CsvHandler.php';
require_once 'EmployeeService.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    $csvHandler = new CsvHandler();
    $csvData = $csvHandler->readCsv('import/nhanvien.csv');

    $employeeService = new EmployeeService($conn);
    $result = $employeeService->importEmployees($csvData);

    echo $result;
    echo "<br>";

    $insuranceData = $employeeService->calculateInsurance();
    $headerBHXH = ['Ma Nhan vien', 'Ho ten', 'Luong co ban', 'Tong BHXH'];
    $exportResult = $csvHandler->exportCsv($insuranceData, 'export', 'output_bhxh.csv', $headerBHXH);

    echo $exportResult . "<br>";

    $allTaxData = $employeeService->calculateAllTax();
    $headerTTNCN = ['Ma Nhan vien', 'Ho ten', ''];
} catch (Exception $e) {
    echo "Import that bai.<br> Loi : " . $e->getMessage();
}
