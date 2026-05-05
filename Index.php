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
    $importStatus = $employeeService->importEmployees($employees);

    echo $importStatus;
    echo "<br>";

    $insuranceData = $employeeService->calculateInsurance();
    $insuranceHeader = ['Ma Nhan vien', 'Ho ten', 'Luong co ban', 'Tong BHXH'];

    $insuranceExportStatus = $csvHandler->exportCsv($insuranceData, 'export/output_bhxh.csv', $insuranceHeader);
    echo $insuranceExportStatus . "<br>";

    $allTaxData = $employeeService->calculateAllTax();
    $taxHeader = ['Ma Nhan vien', 'Ho ten', 'Luong', 'Thue phai dong'];

    $taxExportStatus = $csvHandler->exportCsv($allTaxData, 'export/output_tax.csv', $taxHeader);
    echo "CSV 1: " . $taxExportStatus . "<br>";

    usort($allTaxData, function ($a, $b) {
        return $b[3] <=> $a[3];
    });

    $top3TaxData = array_slice($allTaxData, 0, 3);
    $top3TaxExportStatus = $csvHandler->exportCsv($top3TaxData, 'export/output_tax_top3.csv', $taxHeader);
    echo "CSV 2: " . $top3TaxExportStatus . "<br>";

    $avgSalary = $employeeService->getAverageSalaryUnder30();
    echo "Muc luong trung binh cua nhan vien duoi 30 tuoi : " . number_format($avgSalary, 0, ',', '.') . " VNĐ<br>";

    $leadersData = $employeeService->getLeadersData();
    $leadersHeader = ['Ma Nhan vien', 'Ho ten', 'Phong ban', 'Vi tri'];
    $leadersExportStatus = $csvHandler->exportCsv($leadersData, 'export/output_leaders.csv', $leadersHeader);
    echo "CSV 3: " . $leadersExportStatus . "<br>";
} catch (Exception $e) {
    echo "Import that bai.<br> Loi : " . $e->getMessage();
}