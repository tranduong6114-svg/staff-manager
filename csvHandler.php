<?php
class CsvHandler {

    public function readCsv($filePath)
    {
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
}
