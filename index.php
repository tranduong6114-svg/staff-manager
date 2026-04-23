<?php
if (isset($_POST['btn_submit'])) {
    $file_path = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file_path, "r");
    if ($handle !== FALSE) {
        $is_header = true;
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($is_header) {
                $is_header = false;
                continue;
            }
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
        fclose($handle);
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Import Nhân Viên - Staff Manager</title>
</head>
<body>
    <h2>Hệ thống Quản Lý Nhân Viên - Import CSV</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="csv_file">Chọn file CSV:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
        <br><br>
        <button type="submit" name="btn_submit">Tải lên</button>
    </form>
</body>
</html>

