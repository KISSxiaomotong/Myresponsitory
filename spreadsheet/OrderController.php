<?php

namespace App\Http\Controllers;

use App\Models\Order as OrderModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrderController extends Controller
{
    protected $order_model;

    protected $spreadsheet;

    public function __construct()
    {
        $this->order_model = new OrderModel();

        $this->spreadsheet = new Spreadsheet();
    }

    public function exportOrder()
    {
        $lists = $this->order_model->lists();
        $sheet = $this->spreadsheet->getActiveSheet();
        $title = ['订单号','商品名','商品sku','用户名','联系方式'];
        //表头
        //设置单元格内容
        $titCol = 'A';
        foreach ($title as $key => $value) {
            // 单元格内容写入
            $sheet->setCellValue($titCol . '1', $value);
            $titCol++;
        }
        $row = 2; // 从第二行开始
        foreach ($lists as $item) {
            $dataCol = 'A';
            foreach ($item as $value) {
                // 单元格内容写入
                $sheet->setCellValue($dataCol . $row, $value);
                $dataCol++;
            }
            $row++;
        }

        // Redirect output to a client’s web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="01simple.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
