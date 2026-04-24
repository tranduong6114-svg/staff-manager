<?php
require_once 'config/Database.php';

$file_path = 'import/nhanvien.csv';

if (!file_exists($file_path)) {
    die("Khong tim thay file tai duong dan: $file_path");
}

$handle = fopen($file_path, "r");

if ($handle) {
    $is_header = true;
    $db = new Database();
    $conn = $db->getConnection();

    $conn->beginTransaction();

    try {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($is_header) {
                $is_header = false;
                continue;
            }

            $ma_nv = $row[0];
            $ho_ten = $row[1];
            $email = $row[2];
            $luong_co_ban = (float) $row[3];
            $luong = (float) $row[4];
            $ngay_sinh = $row[5];
            $phong_ban = (int) $row[6];
            $chuc_vu = (int) $row[7];

            $errors = [];

            if (strlen($ho_ten) > 255) {
                $errors[] = "Ho ten vuot qua 255 ky tu";
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email khong hop le";
            }

            if ($luong_co_ban > $luong) {
                $errors[] = "Luong co ban lon hon luong thuc nhan";
            }

            $date = DateTime::createFromFormat('Y-m-d', $ngay_sinh);

            if (!$date || $date->format('Y-m-d') !== $ngay_sinh) {
                $errors[] = "Ngay sinh sai dinh dang";
            }

            if (!empty($errors)) {
                $chuoi_loi = implode(", ", $errors);
                throw new Exception("Loi tai ma NV " . $ma_nv . " : " . $chuoi_loi);
            } else {
                $check_stmt = $conn->prepare("SELECT emp_id FROM employees WHERE emp_id = :emp_id");
                $check_stmt->execute(['emp_id' => $ma_nv]);

                if ($check_stmt->rowCount() > 0) {
                    $update_sql = "UPDATE employees SET full_name = :full_name, email = :email, base_salary = :base_salary, actual_salary = :actual_salary, birthday = :birthday, department_id = :department_id, position_id = :position_id WHERE emp_id = :emp_id";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->execute([
                            'full_name' => $ho_ten,
                            'email' => $email,
                            'base_salary' => $luong_co_ban,
                            'actual_salary' => $luong,
                            'birthday' => $ngay_sinh,
                            'department_id' => $phong_ban,
                            'position_id' => $chuc_vu,
                            'emp_id' => $ma_nv
                    ]);
                } else {
                    $insert_sql = "INSERT INTO employees (emp_id, full_name, email, base_salary, actual_salary, birthday, department_id, position_id) VALUES (:emp_id, :full_name, :email, :base_salary, :actual_salary, :birthday, :department_id, :position_id)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->execute([
                            'emp_id' => $ma_nv,
                            'full_name' => $ho_ten,
                            'email' => $email,
                            'base_salary' => $luong_co_ban,
                            'actual_salary' => $luong,
                            'birthday' => $ngay_sinh,
                            'department_id' => $phong_ban,
                            'position_id' => $chuc_vu
                    ]);
                }
            }
        }

        $conn->commit();
        echo "Import du lieu thanh cong";

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Import that bai.<br> Loi : " . $e->getMessage();
    }
    fclose($handle);
}
?>