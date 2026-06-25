<?php
/**
 * Excel Export Logic
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_post_lol_export_excel', 'lol_handle_excel_export' );

function lol_handle_excel_export() {
    if ( ! current_user_can('manage_options') ) {
        wp_die('Unauthorized');
    }

    check_admin_referer('lol_export_excel_action', 'lol_export_excel_nonce');

    // Only attempt export if PhpSpreadsheet is available
    if ( ! class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet') ) {
        wp_die('PhpSpreadsheet library is not installed. Please run composer install in the theme directory.');
    }

    global $wpdb;
    $orders_table = $wpdb->prefix . 'laundry_orders';
    $items_table = $wpdb->prefix . 'laundry_order_items';

    // Fetch all orders
    $orders = $wpdb->get_results("SELECT * FROM $orders_table ORDER BY id DESC");

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Header
    $headers = ['Token ID', 'Customer Name', 'Phone', 'Pickup Date', 'Delivery Date', 'Delivery Boy', 'Payment Status', 'Amount', 'Status', 'Items Detail', 'Total Clothes'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        $col++;
    }

    $sheet->getStyle('A1:I1')->getFont()->setBold(true);

    // Add Data
    $row = 2;
    foreach ($orders as $order) {
        // Fetch items for this order
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $items_table WHERE order_id = %d", $order->id));
        $item_details = [];
        foreach ($items as $item) {
            $item_details[] = $item->quantity . 'x ' . $item->service_type;
        }
        $items_str = implode(', ', $item_details);

        $sheet->setCellValue('A' . $row, $order->token_id);
        $sheet->setCellValue('B' . $row, $order->customer_name);
        $sheet->setCellValue('C' . $row, $order->phone_number);
        $sheet->setCellValue('D' . $row, $order->pickup_date);
        $sheet->setCellValue('E' . $row, $order->delivery_date);
        $sheet->setCellValue('F' . $row, $order->delivery_boy);
        $sheet->setCellValue('G' . $row, $order->payment_status);
        $sheet->setCellValue('H' . $row, $order->amount_received);
        $sheet->setCellValue('I' . $row, $order->order_status);
        $sheet->setCellValue('J' . $row, $items_str);
        $lol_total_clothes = 0;
        if ( ! empty($items) ) { foreach ($items as $lol_ci) { $lol_total_clothes += intval($lol_ci->quantity); } }
        $sheet->setCellValue('K' . $row, $lol_total_clothes);

        $row++;
    }

    // Set headers for download
    $filename = 'laundry-orders-' . date('Y-m-d') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1'); // If you're serving to IE 9, then the following may be needed
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
    header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
    header('Pragma: public'); // HTTP/1.0

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
