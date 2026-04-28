<?php
class CsvHandler {

    public function readCsv($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Khong tim thay file tai duong dan: $filePath");
        }

        $csvData = [];
        $filePointer = fopen($filePath, "r");

        if ($filePointer) {
            $headers = fgetcsv($filePointer, 1000, ",");
            $headers[0] = preg_replace('/[\xef\xbb\xbf]/', '', $headers[0]);
            $expectedColumns = ['Mã nhân viên', 'Họ tên', 'email', 'Lương cơ bản', 'Lương', 'Sinh nhật', 'Phòng ban', 'Chức vụ'];
            $columnIndexMap = [];

            foreach ($expectedColumns as $colName) {
                $index = array_search($colName, $headers);

                if ($index === false) {
                    throw new Exception("File CSV bi thieu cot bat buoc : " . $colName);
                }
                $columnIndexMap[$colName] = $index;
            }

            while (($row = fgetcsv($filePointer, 1000, ",")) !== FALSE) {
                $mappedRow = [];
                foreach ($expectedColumns as $colName) {
                    $columnIndex = $columnIndexMap[$colName];
                    $mappedRow[$colName] = $row[$columnIndex] ?? null;
                }
                $csvData[] = $mappedRow;
            }
            fclose($filePointer);
        }
        return $csvData;
    }

    public function exportCsv($data, $exportDir, $fileName, $header) {
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $filePath = $exportDir . '/' . $fileName ;
        $filePointer = fopen($filePath, "w");

        if ($filePointer) {
            fputs($filePointer, "\xEF\xBB\xBF");
            fputcsv($filePointer, $header);

            foreach ($data as $row) {
                fputcsv($filePointer, $row);
            }

            fclose($filePointer);
            return "Xuat file thanh cong tai : " . $filePath;
        } else {
            throw new Exception("Khong the tao file tai duong dan: $fileName");
        }
    }
}