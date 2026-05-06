# Hệ thống Quản lý Nhân viên (CSV Processor)

## Mô tả dự án
Công cụ đọc dữ liệu từ file CSV, xử lý nghiệp vụ (tính thuế, BHXH) và lưu trữ vào Cơ sở dữ liệu.

## Môi trường & Công nghệ
- PHP 8 (OOP, PDO)
- MySQL 8
- Xử lý File: `fopen`, `fgetcsv`, `fputcsv`

## Tính năng chính
1. Import CSV -> Insert/Update (Sử dụng Transaction & Rollback).
2. Validate dữ liệu: format ngày sinh, email, độ dài chuỗi.
3. Tính toán Tổng bảo hiểm (10.5%) và Thuế TNCN (lũy tiến).
4. Xuất file CSV báo cáo (Danh sách nhân viên, Top 3 đóng thuế).
5. Thống kê CLI (Lương trung bình nhân viên < 30 tuổi).
