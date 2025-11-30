<?php

class ControllerReportDeliveryChallanReport extends HController {

    protected function getAlias() {
        return 'report/delivery_challan_report';
    }
    
    protected function getDefaultOrder() {
        return 'delivery_challan_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        // $this->data['partner_types'] = $this->session->data['partner_types'];

        // $this->model['partner'] = $this->load->model('common/partner');

        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array('company_id' => $this->session->data['company_id'],'partner_type_id' => 2));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},

            },
            ignore:[],
        }";

        $this->template = $this->getAlias() . '.tpl';

//        //        $this->document->addScript("view/javascript/jquery/jquery.dataTables.min.js");
//        $this->document->addLink("view/stylesheet/dataTables.css", "stylesheet");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.js");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.columnFilter.js");
////        $this->document->addLink("view/js/plugins/dataTables/css/jquery.dataTables.css", "stylesheet");
//
//
        $this->response->setOutput($this->render());
    }


    public function printReportExcel()
    {
        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['delivery_challan_report'] = $this->load->model('report/delivery_challan_report');
        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');
        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        $session = $this->session->data;
        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];
        $sort_order = "ASC";

        $where =  "document_date >='$date_f'";
        $where .= " AND document_date <='$date_s'";
        if($post['status'] != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`status`='".$post['status']."'";
        }
        
        if($post['partner_id'] != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`partner_id`='$partner_id'";
        }
        
        if($product_id != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`product_id`='$product_id'";
        }
        
        $arrPartner = $this->model['partner']->getArrays('partner_id','name');
        // $grouping = $this->model['goods_received_report']->getTotalGoodsReceived();
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $rows = $this->model['delivery_challan_detail']->getRows($where,array('document_date asc'));
        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));
        $arrRows = array();
        foreach($rows as $group) {
            $row['document_date'] = stdDate($group['document_date']);
            if($this->request->post['group_by'] == 'partner') {
                $group_name = $arrPartner[$group['partner_id']];
            }
            else if($this->request->post['group_by'] == 'warehouse'){
                $group_name = $group['warehouse'];
            }
            elseif($this->request->post['group_by'] == 'product') {
                $group_name = $group['product_name'];
            }
            elseif($this->request->post['group_by'] == 'document_date') {
                $group_name = stdDate($group['document_date']);
            }
            else {
                $group_name = '';
            }
            $groupBy = $this->request->post['group_by'];
            $arrRows[$group_name][] = array(
                'document_date' => $group['document_date'],
                'document_identity' => $group['document_identity'],
                'warehouse_name' => $group['warehouse'],
                'product_name' => $group['product_name'],
                'partner_name' => $arrPartner[$group['partner_id']],
                'unit_name' => $group['unit'],
                'qty' => $group['qty'],
                'po_no' =>$group['po_no'],
                'cog_rate' => ($group['qty']==0?0:($group['cog_amount']/$group['qty'])),
                'cog_amount' => $group['cog_amount']
            );
        }
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()
            ->setCreator("Farrukh Afaq")
            ->setLastModifiedBy("Hira")
            ->setTitle("Delivery Challan Report");
        $objPHPExcel->data = array(
            'company_name' => $session['company_name'],
            'report_name' => 'Delivery Challan Report',
            'date_to' => $date_s
        );
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'size' => 25
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Delivery Challan Report');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' => array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' => array(
                    'size' => 20
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, '');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
        $rowCount ++;
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(17);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':'.'G'.$rowCount)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, 'Doc. Date')->getStyle('A'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, 'Doc. Identity')->getStyle('B'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, 'Warehouse Name')->getStyle('C'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, 'Partner Name')->getStyle('D'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, 'Product')->getStyle('E'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, 'PO No.')->getStyle('F'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, 'Unit')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, 'Quantity')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, 'Rate')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, 'Amount')->getStyle('G'.$rowCount)->getFont()->setBold( true );
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':J'.$rowCount)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                ),
                'fill' => array(
                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'ebebeb')
                )
            )
        );
        $rowCount++;

        foreach($arrRows as $key => $value) {
            $objPHPExcel->getActiveSheet()->mergeCells('A'.($rowCount).":J".($rowCount));
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, $key);
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->getFill('ebebeb');
            $rowCount ++;

            foreach ($value as $key_1 => $value_1)
            {
                $objPHPExcel->getActiveSheet()->SetCellValue('A'.$rowCount, stdDate($value_1['document_date']));
                $objPHPExcel->getActiveSheet()->SetCellValue('B'.$rowCount, $value_1['document_identity']);
                $objPHPExcel->getActiveSheet()->SetCellValue('C'.$rowCount, $value_1['warehouse_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('D'.$rowCount, $value_1['partner_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('E'.$rowCount, $value_1['product_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('F'.$rowCount, $value_1['po_no']);
                $objPHPExcel->getActiveSheet()->SetCellValue('G'.$rowCount, $value_1['unit_name']);
                $objPHPExcel->getActiveSheet()->SetCellValue('H'.$rowCount, $value_1['qty']);
                $objPHPExcel->getActiveSheet()->SetCellValue('I'.$rowCount, $value_1['cog_rate']);
                $objPHPExcel->getActiveSheet()->SetCellValue('J'.$rowCount, $value_1['cog_amount']);
                $rowCount++;
            }
            $rowCount++;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="delivery_challan_report.xlsx"');
        header('Cache-Control: max-age=0');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save('php://output');
        exit;
    }


    public function printReport() {

        ini_set('max_execution_time',0);
        ini_set('memory_limit',-1);

        $this->model['delivery_challan_report'] = $this->load->model('report/delivery_challan_report');
        $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
        $this->model['partner_type'] = $this->load->model('common/partner_type');
        $this->model['partner'] = $this->load->model('common/partner');



        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;
        $session = $this->session->data;
        // d($post,true);
        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];


        $sort_order = "ASC";

        $where =  "document_date >='$date_f'";
        $where .= " AND document_date <='$date_s'";
        if($post['status'] != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`status`='".$post['status']."'";
        }
        else{
            $where .= "";
        }
        if($post['partner_id'] != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`partner_id`='$partner_id'";
        }
        else{
            $where .= "";
        }

        if($product_id != ''){
            $where .= " AND `vw_ins_delivery_challan_detail`.`product_id`='$product_id'";
        }
        else{
            $where .= "";
        }

//        d($where,true);
        $arrPartner = $this->model['partner']->getArrays('partner_id','name');
        // $grouping = $this->model['goods_received_report']->getTotalGoodsReceived();
        $this->model['company'] = $this->load->model('setup/company');
        $this->model['company_branch'] = $this->load->model('setup/company_branch');

        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));
        $rows = $this->model['delivery_challan_detail']->getRows($where,array('document_date asc'));

        $branch = $this->model['company_branch']->getRow(array('company_branch_id' => $session['company_branch_id']));

        $arrRows = array();

        foreach($rows as $group) {
            $row['document_date'] = stdDate($group['document_date']);
            if($this->request->post['group_by'] == 'partner') {
                $group_name = $arrPartner[$group['partner_id']];
            }
            else if($this->request->post['group_by'] == 'warehouse'){
                $group_name = $group['warehouse'];
            }
            elseif($this->request->post['group_by'] == 'product') {
                $group_name = $group['product_name'];
            }
            elseif($this->request->post['group_by'] == 'document_date') {
                $group_name = stdDate($group['document_date']);
            }
            else {
                $group_name = '';
            }

            $groupBy = $this->request->post['group_by'];

            $arrRows[$group_name][] = array(

                'document_date' => $group['document_date'],

                'document_identity' => $group['document_identity'],
                'warehouse_name' => $group['warehouse'],
                'product_name' => $group['product_name'],
                'partner_name' => $arrPartner[$group['partner_id']],
                'unit_name' => $group['unit'],
                'qty' => $group['qty'],
                'po_no' =>$group['po_no'],
                'cog_rate' => ($group['qty']==0?0:($group['cog_amount']/$group['qty'])),
                'cog_amount' => $group['cog_amount']
            );
        }


        //$arrFilter['supplier'] = $post['partner_type_id'];
        //$arrFilter['product_id'] = $post['product_id'];
        //$arrFilter['company_id'] = $session['company_id'];
        //$arrFilter['branch_id'] = $post['branch_id'];

//        $arrFilter['level'] = $post['level'];





        // $rows = $this->model['trial_balance']->getTrailBalanceConsolidate($arrFilter);


        $pdf = new PDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Aamir Shakil');
        $pdf->SetTitle('Delivery Challan Report');
        $pdf->SetSubject('Delivery Challan Report');

        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        if($post['status'] == "Normal")
        {
            $report_name = "Delivery Challan Report";
        }
        else{
            $report_name = "Sample Delivery Challan Report";
        }

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => $report_name,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'company_logo' => $session['company_image'],
            'supplier_name'=>$post['supplier_name'],

        );


        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 47, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', 'B', 10);


        $sr = 0;
        $pdf->Ln(0);

        $Amount = 0;
        $WhAmount = 0;
        $OtAmount = 0;
        $NetAmount = 0;


        $total_op_debit = 0;
        $total_op_credit = 0;
        $total_cur_debit = 0;
        $total_cur_credit = 0;
        $total_quantity = 0;
        $total_amount = 0;

        // add a page
        $pdf->AddPage();
//        d($arrRows,true);
        foreach($arrRows as $group_name => $rows)
        {
            $pdf->SetFont('helvetica', 'B,U', 9);

            $pdf->ln(8);
            $pdf->Cell(150, 7,'Group Name :  ' .$group_name, 0, false, 'L', 0, '', 1);

            foreach($rows as $delivery_challans) {

                $pdf->SetFont('helvetica', '', 7);

                $pdf->ln(6);

                $pdf->Cell(15, 5,stdDate($delivery_challans['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(22, 5, html_entity_decode($delivery_challans['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5,$delivery_challans['warehouse_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(45, 5,$delivery_challans['partner_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(50, 5, $delivery_challans['product_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, $delivery_challans['po_no'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(15, 5, $delivery_challans['unit_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(20, 5, number_format($delivery_challans['qty'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(20, 5, number_format($delivery_challans['cog_rate'],2), 0, false, 'R', 0, '', 1);
                $pdf->Cell(25, 5, number_format($delivery_challans['cog_amount'],2), 0, false, 'R', 0, '', 1);

                $total_quantity += $delivery_challans['qty'];
                $total_amount += $delivery_challans['cog_amount'];
                //$total_cur_debit += $LedgerDetail['cur_debit'];
                //$total_cur_credit += $LedgerDetail['cur_credit'];
                //$total_tot_debit += $LedgerDetail['tot_debit'];
                //$total_tot_credit += $LedgerDetail['tot_credit'];

            }
        }


        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('helvetica', 'B', 7);

        $pdf->Ln(7);
        // $pdf->Cell(190, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(23, 7, number_format($total_op_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_op_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(207, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($total_quantity,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(25, 7, number_format($total_amount,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //Close and output PDF document
        $pdf->Output('Delivery Challan:'.date('YmdHis').'.pdf', 'I');

}
}

class PDF extends TCPDF {
    public $data = array();


    //Page header
    public function Header() {
        // Logo

        if($this->data['company_logo'] != '') {
            $image_file = DIR_IMAGE.$this->data['company_logo'];
            //$this->Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false);
            $this->Image($image_file, 10, 10, 30, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('times', 'B', 20);
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('times', 'B', 10);
        $this->Ln(10);
        $this->Cell(0, 10, 'From Date : '.$this->data['date_from'].'     To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);



        $this->SetFont('helvetica', 'B', 7);
        $this->Cell(15, 5, 'Doc Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(22, 5, 'Doc Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Warehouse Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(45, 5, 'Partner Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 5, 'Product', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Po No', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(15, 5, 'Unit', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Quantity', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Rate', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $this->Cell(25, 5, 'Amount', 'T,B', false, 'R', 0, '', 0, false, 'M', 'M');


    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

?>