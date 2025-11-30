<?php

class ControllerReportGoodsReceivedReport extends HController {

    protected function getAlias() {
        return 'report/goods_received_report';
    }
    
    protected function getDefaultOrder() {
        return 'goods_received_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->model['supplier'] = $this->load->model('setup/supplier');
        $this->data['suppliers'] = $this->model['supplier']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->model['product'] = $this->load->model('inventory/product');
        $this->data['products'] = $this->model['product']->getRows(array('company_id' => $this->session->data['company_id']));

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));

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

        $date_f = MySqlDate($post['date_from']);
        $date_s = MySqlDate($post['date_to']);
        $partner_id = $post['partner_id'];
        $product_id = $post['product_id'];


        $sort_order = "ASC";

        $where =  "document_date >='$date_f'";
        $where .= " AND document_date <='$date_s'";
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
                $group_name = $group['document_date'];
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
        $pdf->SetTitle('Goods Received Report');
        $pdf->SetSubject('Goods Received Report');

        $date_from = $post['date_from'];
        $date_to = $post['date_to'];

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'company_address' => $branch['address'],
            'company_phone' => $branch['phone_no'],
            'report_name' => 'Goods Received Report',
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
            $pdf->Cell(50, 7,'Group Name :  ' .$group_name, 0, false, 'L', 0, '', 1);

            foreach($rows as $delivery_challans) {

                $pdf->SetFont('helvetica', '', 7);

                $pdf->ln(6);

                $pdf->Cell(20, 5,stdDate($delivery_challans['document_date']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5, html_entity_decode($delivery_challans['document_identity']), 0, false, 'L', 0, '', 1);
                $pdf->Cell(30, 5,$delivery_challans['warehouse_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(50, 5,$delivery_challans['partner_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(50, 5, $delivery_challans['product_name'], 0, false, 'L', 0, '', 1);
                $pdf->Cell(20, 5, $delivery_challans['unit_name'], 0, false, 'L', 0, '', 1);
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
        $pdf->Cell(190, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        // $pdf->Cell(23, 7, number_format($total_op_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_op_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_debit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(5, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        //$pdf->Cell(23, 7, number_format($total_cur_credit,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');

        $pdf->Cell(10, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, number_format($total_quantity,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(23, 7, number_format($total_amount,2), 'T,B,B', false, 'R', 0, '', 0, false, 'M', 'M');
        //Close and output PDF document
        $pdf->Output('Goods Received :'.date('YmdHis').'.pdf', 'I');

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
        $this->SetFont('helvetica', 'B', 20);
        $this->Ln(5);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->SetFont('helvetica', 'B', 10);
        $this->Ln(10);
        $this->Cell(0, 10, 'From Date : '.$this->data['date_from'].'     To Date  :  '.$this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(12);



        $this->SetFont('helvetica', 'B', 7);
        $this->Cell(20, 5, 'Document Date', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Document Identity', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 5, 'Warehouse Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 5, 'Partner Name', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(50, 5, 'Product', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 5, 'Unit', 'T,B', false, 'L', 0, '', 0, false, 'M', 'M');
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




//class PDF extends FPDF
//{
//    var $company_name;
//    var $company_branch_name;
//    var $filter;
//    var $group_heading;
//// Page header
//    function Header()
//    {
//        // Logo
//        $this->Image('image/data/apple_logo.jpg',10,6,30);
//        // Arial bold 15
//        $this->SetFont('helvetica','B',30);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,html_entity_decode($this->company_name),0,0,'C');
//        $this->SetFont('Arial','B',8);
//        $this->Cell(50, 10,'From Date = '.$this->filter['from_date'], 0, 0, 'L');
//        $this->Ln(6);
//
//        $this->Cell(140);
//        $this->Cell(50, 10,'To Date = '.$this->filter['to_date'], 0, 0, 'L');
//        $this->SetFont('Arial','B',15);
//        $this->Ln(6);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,html_entity_decode($this->company_branch_name),0,0,'C');
//
//        $this->SetFont('Arial','B',8);
//
//        $this->Cell(50, 10,'Product ='.$this->filter['product'], 0,0    , 'L');
//        $this->Ln(6);
//        $this->Cell(140);
//        $this->Cell(50, 10,'Supplier ='.$this->filter['supplier'], 0,0 , 'L');
//
//        // Line break
//        $this->Ln(5);
//        // Arial bold 15
//        $this->SetFont('Arial','B',15);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,'Goods Received Report',0,0,'C');
//        // Line break
//        $this->Ln(15);
//    }
//
//// Page footer
//    function Footer()
//    {
//        // Position at 1.5 cm from bottom
//        $this->SetY(-15);
//        // Arial italic 8
//        $this->SetFont('Arial','I',8);
//        // Page number
//        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
//    }
//
//    function Body($data) {
//        $header = array('Date', 'Invoice', 'Supplier', 'Category', 'Product', 'Qty', 'Rate', 'Currency', 'Conversion Rate', 'Amount');
//        $w = array(15, 15, 23, 25, 20, 15,15, 20, 22,20);
//
//        $previous_group = '';
//        foreach($data as $group => $rows) {
//            if($previous_group != $group && $previous_group != '') {
//                $this->AddPage();
//            }
//            $previous_group = $group;
//            $this->SetFont('helvetica','B',11);
//            $this->SetTextColor(0);
//
//            $this->Ln(7);
//            if($this->group_heading == 'invoice_date'){
//            $this->Cell(30,10,'Invoice Date :',0,0,'L');
//            $this->Cell(30,10,stdDate($group),0,0,'L');
//            }
//            else if($this->group_heading == 'supplier'){
//                $this->Cell(20,10,'Supplier :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            }
//            else if($this->group_heading == 'product'){
//                $this->Cell(20,10,'Product :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            }
//            // Line break
//            $this->Ln(7);
//
//            // Colors, line width and bold font
//            $this->SetFillColor(93,123,157);
//            $this->SetTextColor(255);
//            $this->SetDrawColor(128,0,0);
//            $this->SetLineWidth(.3);
//            $this->SetFont('','B','7');
//            // Header
//            for($i=0;$i<count($header);$i++)
//                $this->Cell($w[$i],7,$header[$i],1,0,'C',true);
//            $this->Ln();
//            // Color and font restoration
//            $this->SetFillColor(224,235,255);
//            $this->SetTextColor(0);
//            $this->SetFont('','',6);
//            // Data
//            $fill = false;
//            $total_amount = 0;
//            foreach($rows as $row)
//            {
//                $this->Cell($w[0],6,$row['invoice_date'],'LR',0,'L',$fill);
//                $this->Cell($w[1],6,$row['invoice_no'],'LR',0,'L',$fill);
//                $this->Cell($w[2],6,$row['supplier'],'LR',0,'L',$fill);
//                $this->Cell($w[3],6,$row['product_category'],'LR',0,'L',$fill);
//                $this->Cell($w[4],6,$row['product'],'LR',0,'L',$fill);
//                $this->Cell($w[5],6,$row['qty'] . ' ' . $row['unit'] ,'LR',0,'L',$fill);
//                $this->Cell($w[6],6,number_format($row['rate'],2),'LR',0,'R',$fill);
//                $this->Cell($w[7],6,$row['currency'],'LR',0,'R',$fill);
//                $this->Cell($w[8],6,number_format($row['conversion_rate'],2),'LR',0,'R',$fill);
//                $this->Cell($w[9],6,number_format($row['amount'],2),'LR',0,'R',$fill);
//                $this->Ln();
//                $fill = !$fill;
//                $total_amount += $row['amount'];
//            }
//            //d($row,true);
//            $this->SetFillColor(255,255,255);
//            $this->SetTextColor(0);
//            //$this->SetDrawColor(128,0,0);
//            $this->SetLineWidth(.3);
//            $this->SetFont('','B','7');
//            $this->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7] +$w[8],6,'',1,0,'L',true);
//            $this->Cell($w[9],6,number_format($total_amount,2),1,0,'R',true);
//            $this->Ln();
//            // Closing line
//            $this->Cell(array_sum($w),0,'','T');
//        }
//    }
//}

?>