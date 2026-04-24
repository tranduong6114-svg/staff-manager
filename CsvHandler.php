<?php
class CsvHandler {

    public function readCsv($filePath) {

        if (!file_exists($filePath)) {
            throw new Exception("Khong tim thay file tai duong dan: $filePath");
        }

        $data = [];
        $handle = fopen($filePath, "r");

        if ($handle) {
            $is_header = true;
            while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($is_header) {
                    $is_header = false;
                    continue;
                }
                $data[] = $row;
            }
            fclose($handle);
        }
        return $data;
    }

    public function exportCsv($data, $exportDir, $fileName, $header) {
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $filePath = $exportDir . '/' . $fileName ;
        $handle = fopen($filePath, "w");
        if ($handle) {
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $header);
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            return "Xuat file thanh cong tai : " . $filePath;
        } else {
            throw new Exception("Khong the tao file tai duong dan: $fileName");
        }
    }
}
