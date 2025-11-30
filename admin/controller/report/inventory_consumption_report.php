<?php

class ControllerReportInventoryConsumptionReport extends HController {

    protected function getAlias() {
        return 'report/inventory_consumption_report';
    }
    
    protected function getDefaultOrder() {
        return 'inventory_consumption_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->model['department'] = $this->load->model('setup/department');
        $this->data['departments'] = $this->model['department']->getRows(array('company_id' => $this->session->data['company_id']));


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

//
//        //        $this->document->addScript("view/javascript/jquery/jquery.dataTables.min.js");
//        $this->document->addLink("view/stylesheet/dataTables.css", "stylesheet");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.js");
//        $this->document->addScript("view/js/plugins/dataTables/js/jquery.dataTables.columnFilter.js");
////        $this->document->addLink("view/js/plugins/dataTables/css/jquery.dataTables.css", "stylesheet");
//
        $this->response->setOutput($this->render());
    }

    public function printReport() {
        $this->init();
        ini_set('memory_limit','1024M');

        $lang = $this->load->language($this->getAlias());
        $filter = array();

        if($this->request->post['date_from'])
            $filter['GTE']['document_date'] = MySqlDate($this->request->post['date_from']);
        if($this->request->post['date_to'])
            $filter['LTE']['document_date'] = MySqlDate($this->request->post['date_to']);
        if($this->request->post['department_id'])
            $filter['EQ']['department_id'] = $this->request->post['department_id'];

//        d($filter,true);
        $cond = getFilterString($filter);
//$data1 = $this->request->post['product_id'];
//d($cond,true);
        $this->model['warehouse'] = $this->load->model('inventory/warehouse');
        $warehouse = $this->model['warehouse']->getRow(array('warehouse_id' => $this->request->post['warehouse_id']));
        $arrWarehouse = $this->model['warehouse']->getArrays('warehouse_id','name',array('company_id' => $this->session->data['company_id']));
        $this->model['product'] = $this->load->model('setup/product');
        $product = $this->model['product']->getArrays('product_id','name', array('company_id' => $this->session->data['company_id']));
        $arrProducts = $this->model['product']->getRow(array('product_id' => $this->request->post['product_id']));
        $this->model['department'] = $this->load->model('setup/department');
        $department = $this->model['department']->getRow(array('department_id' => $this->request->post['department_id']));
        $arrDepartment = $this->model['department']->getArrays('department_id','name', array('company_id' => $this->session->data['company_id']));
//d(array($department,$arrDepartment),true);
        $this->model['currency'] = $this->load->model('setup/currency');
        $arrCurrency = $this->model['currency']->getArrays('currency_id','currency_code', array('company_id' => $this->session->data['company_id']));

//d($product,true);

            $arrfilter = array(
                'from_date' => $this->request->post['date_from'],
                'to_date' => $this->request->post['date_to'],
                'department' => $department['name'],

            );
//        d($arrfilter,true);

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch = $this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id']));

//        d( $company,true);
        $this->model['inventory_consumption_report'] = $this->load->model('report/inventory_consumption_report');
        $rows = $this->model['inventory_consumption_report']->getRows($cond,array('document_date,document_identity ASC'));
//     d($rows,true);
        $arrRows = array();

        foreach($rows as $row) {
            $row['document_date'] = stdDate($row['document_date']);
            if($this->request->post['group_by'] == 'department') {
                $group_name = $department[$row['department_id']];
            } elseif($this->request->post['group_by'] == 'product') {
                $group_name = $product[$row['product_id']];
            } elseif($this->request->post['group_by'] == 'document_date') {
                $group_name = $row['document_date'];
            } elseif($this->request->post['group_by'] == 'warehouse'){
                $group_name = $arrWarehouse[$row['warehouse_id']];
            } elseif($this->request->post['group_by'] == 'document_identity'){
                $group_name = $row['document_identity'];
            }else {
                $group_name = $row['department_name'];
            }
            $groupBy = $this->request->post['group_by'];
            $arrRows[$group_name][] = array(
                'document_type_id' => $row['document_type_id'],
                'document_id' => $row['document_id'],
                'document_date' => $row['document_date'],
                'voucher_no' => $row['document_identity'],
                'document_identity' => $row['document_identity'],
                'warehouse_id' => $row['warehouse_id'],
                'warehouse' => $arrWarehouse[$row['warehouse_id']],
                'product_category_id' => $row['product_category_id'],
                'product_id' => $row['product_id'],
                'product' => $product[$row['product_id']],
                'department_id' => $row['department_id'],
                'department' => $row['department_name'],
                'unit_id' => $row['unit_id'],
                'qty' => $row['qty'],
                'remarks' => $row['remarks']
            );

        }
//d($row,true);

        $data = array(
            'company' => $company,
            'company_branch' => $company_branch,
            'filter' => $arrfilter,
            'lang' => $lang,
            'rows' => $arrRows,
            'group' => 'Department',
            'group_name' => $group_name
        );

//d($data,true);
        try
        {
            $pdf=new mPDF();

            $pdf->SetDisplayMode('fullpage');
            $pdf->mPDF('utf-8','A4','','','15','15','45','18');
            $pdf->setHTMLHeader($this->getPDFHeader($data));
            $pdf->setHTMLFooter($this->getPDFFooter($data));
            $pdf->WriteHTML($this->getPDFStyle($data));
            $pdf->WriteHTML($this->getPDFBodySummary($data));

            $pdf->Output();
        }
        catch(Exception $e) {
            echo $e;
            exit;
        }
        exit;
        //d($arrfilter,true);

//        $data = $arrRows;
//        $pdf = new PDF();
//        $pdf->company_name = $company['name'];
//        $pdf->company_branch_name = $company_branch['name'];
//        $pdf->group_heading = $this->request->post['group_by'];
//        $pdf->filter = $arrfilter;
//        $pdf->AliasNbPages();
//        $pdf->SetFont('Arial','',14);
//        $pdf->AddPage();
//        $pdf->Body($data);
//        $pdf->Output();
//        exit;
       }

    private function getPDFStyle($data) {
        $html = '';
        $html .= '<style type="text/css">';
        $html .= 'body {';
        $html .= 'background: #FFFFFF;';
        $html .= '}';
        $html .= 'body, td, th, input, select, textarea, option, optgroup {';
        $html .= 'font-family: Arial, Helvetica, sans-serif;';
        $html .= 'font-size: 10px;';
        $html .= 'color: #000000;';
        $html .= '}';
        $html .= 'h1 {';
        $html .= 'text-transform: uppercase;';
        $html .= 'text-align: center;';
        $html .= 'font-size: 24px;';
        $html .= 'font-weight: normal;';
        $html .= 'margin: 5px 0;';
        $html .= '}';
        $html .= 'h2 {';
        $html .= 'text-transform: uppercase;';
        $html .= 'text-align: center;';
        $html .= 'font-size: 18px;';
        $html .= 'font-weight: normal;';
        $html .= 'padding: 0;';
        $html .= 'margin: 0;';
        $html .= '}';
        $html .= 'h3 {';
        $html .= 'text-align: center;';
        $html .= 'font-size: 16px;';
        $html .= 'font-weight: normal;';
        $html .= 'padding: 0;';
        $html .= 'margin: 5px 0 0 0;';
        $html .= '}';
        $html .= 'table.page_header {width: 100%; border: none; background-color: #DDDDFF; border-bottom: solid 1mm #AAAADD; padding: 2mm }';
        $html .= 'table.page_body {width: 100%; border: solid 1px #DDDDDD; border-collapse: collapse; align="center" }';
        $html .= 'table.page_body th {border: solid 1px #000000; border-collapse: collapse; background-color: #CDCDCD; text-align: center; font-size: 12px; padding: 5px;}';
        $html .= 'table.page_body td {border: solid 1px #000000; border-collapse: collapse;font-size: 10px; padding: 5px;}';
        $html .= 'table.page_footer {width: 100%; border: none; background-color: #DDDDFF; border-top: solid 1mm #AAAADD; padding: 2mm}';
        $html .= '</style>';

        return $html;
    }
    private function getPDFHeader($data) {
        $filter = $data['filter'];
        $lang = $data['lang'];
        $company = $data['company'];
        $companyBranch = $data['company_branch'];
        $this->model['image'] = $this->load->model('tool/image');
        $company_image = $this->model['image']->resize($company['company_logo'],100,100);
//d($company_image,true);
        $html  = '';
        $html .= '<table class="page_header">';
        $html .= '<tr>';
        $html .= '<td style="width: 33%; text-align: left;">';
        if($company['company_logo']) {
            $html .= '<img src="' . $company_image . '" alt="Logo"/>';
        }
        $html .= '</td>';
        $html .= '<td style="width: 34%; text-align: center">';
        $html .= '<h1>' . $company['name'] .'</h1>';
        $html .= '<h2>' . $companyBranch['name'] . '</h2>';
        $html .= '<h3>' . $lang['heading_title'] . '</h3>';
        $html .= '</td>';
        $html .= '<td style="width: 33%;">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td style="text-align: right; font-weight: bold">' . $lang['from_date'] . '</td><td style="text-align: left;">' . $filter['from_date'] . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="text-align: right; font-weight: bold">' . $lang['to_date'] . '</td><td style="text-align: left;">' . $filter['to_date'] . '</td>';
        $html .= '</tr>';
        $html .= '<tr>';
        $html .= '<td style="text-align: right; font-weight: bold">' . $lang['department'] . '</td><td style="text-align: left;">' . $filter['department'] . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    private function getPDFFooter($data) {
        $html = '';
        $html .= '<table class="page_footer">';
        $html .= '<tr>';
        $html .= '<td style="width: 33%; text-align: left;">';
        $html .= '&nbsp;';
        $html .= '</td>';
        $html .= '<td style="width: 34%; text-align: center">';
        $html .= 'Page: {PAGENO}';
        $html .= '</td>';
        $html .= '<td style="width: 33%; text-align: right">';
        $html .= 'Date: {DATE '.STD_DATETIME.'}';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        return $html;
    }

    private function getPDFBodyDetail($data) {
        $stocks = $data['rows'];
        $lang = $data['lang'];

        $html = '';
        foreach($stocks as $warehouse => $categories) {
            foreach($categories as $category => $products) {
                foreach($products as $product => $rows) {
                    $total_qty = 0;
                    $total_amount =0;

                    $html .= '<div style="padding-top: 10px;">';
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_warehouse'] . '</strong>' . $warehouse . '</div>';
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_product_category'] . '</strong>' . $category . '</div>';
                    $html .= '<div style="font-size: 12px;"><strong>' . $lang['entry_product'] . '</strong>' . $product . '</div>';
                    $html .= '<table class="page_body">';
                    $html .= '<thead>';
                    $html .= '<tr>';
                    $html .= '<th style="width: 20%;">' . $lang['column_document_date'] . '</th>';
                    $html .= '<th style="width: 20%;">' . $lang['column_document_no'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_qty'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_rate'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_amount'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_total_qty'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_total_amount'] . '</th>';
                    $html .= '<th style="width: 10%;">' . $lang['column_avg_rate'] . '</th>';
                    $html .= '</tr>';
                    $html .= '</thead>';
                    $html .= '<tbody>';
                    foreach($rows as $row) {
                        $total_qty += $row['qty'];
                        $total_amount += $row['amount'];
                        $html .= '<tr>';
                        $html .= '<td style="text-align:left;">' . $row['document_date'] . '</td>';
                        $html .= '<td style="text-align:left;">' . $row['document_identity'] . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($row['qty'],0) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($row['rate'],4) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($row['amount'],4) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($total_qty,0) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format($total_amount,4) . '</td>';
                        $html .= '<td style="text-align:right;">' . number_format(($total_amount/$total_qty),4) . '</td>';
                        $html .= '</tr>';
                    }
                    $html .= '</tbody>';
                    $html .= '<tfoot>';
                    $html .= '<tr>';
                    $html .= '<th colspan="2">&nbsp;</th>';
                    $html .= '<th style="text-align:right;">' . number_format($total_qty,0) . '</th>';
                    $html .= '<th style="text-align:right;">&nbsp;</th>';
                    $html .= '<th style="text-align:right;">' . number_format($total_amount,4) . '</th>';
                    $html .= '<th colspan="3">&nbsp;</th>';
                    $html .= '</tr>';
                    $html .= '</tfoot>';
                    $html .= '</table>';
                    $html .= '</div>';
                    $html .= '<pagebreak />';
                }
            }
        }
        $html = substr($html,0,strlen($html)-13);
        return $html;
    }

    private function getPDFBodySummary($data) {
        $stocks = $data['rows'];
        $lang = $data['lang'];
        $group = $data['group'];
        $group_name = $data['group_name'];
//d($group,true);
        $html = '';
        foreach($stocks as $group_name => $products) {
            $total_qty = 0;
            $total_amount =0;
            $base_amount = 0;
            $total_base_amount = 0;

            $html .= '<div style="padding-top: 10px;">';
            if($group == 'Department')
            {
                $html .= '<div style="font-size: 12px;"><strong>' . $lang['department'] . '</strong>' .$group_name . '</div>';
            }

            $html .= '<table class="page_body">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th style="width: 10%;">' . $lang['document_date'] . '</th>';
            $html .= '<th style="width: 10%;">' . $lang['document_identity'] . '</th>';
            $html .= '<th style="width: 18%;">' . $lang['column_department'] . '</th>';
            $html .= '<th style="width: 22%;">' . $lang['product'] . '</th>';
            $html .= '<th style="width: 8%;">' . $lang['qty'] . '</th>';
            $html .= '<th style="width: 32%;">' . $lang['remarks'] . '</th>';

            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';
            foreach($products as $product => $row) {
                $total_qty += $row['qty'];

                $html .= '<tr>';
                $html .= '<td style="text-align:left;">' . $row['document_date'] . '</td>';
                $html .= '<td style="text-align:left;">' . $row['document_identity'] . '</td>';
                $html .= '<td style="text-align:left;">' . $row['department'] . '</td>';
                $html .= '<td style="text-align:left;">' . $row['product'] . '</td>';
                $html .= '<td style="text-align:right;">' . number_format($row['qty'],0) . '</td>';
                $html .= '<td style="text-align:left;">' . $row['remarks'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '<tfoot>';
            $html .= '<tr>';
            $html .= '<th colspan="4">&nbsp;</th>';
            $html .= '<th style="text-align:right;">' . number_format($total_qty,0) . '</th>';
            $html .= '<th colspan="1">&nbsp;</th>';
            $html .= '</tr>';
            $html .= '</tfoot>';
            $html .= '</table>';
            $html .= '</div>';
            $html .= '<pagebreak />';
        }
        $html = substr($html,0,strlen($html)-13);
        return $html;
    }


    }

//class PDF extends FPDF
//{
//// Page header
//    var $company_name;
//    var $company_branch_name;
//    var $filter;
//    var $group_heading;
//
//    function Header()
//    {
////        $data = $this->glob_data;
//        // Logo
//
//        $this->Image('image/data/apple_logo.jpg',10,6,30);
//
//        // Arial bold 15
//        $this->SetFont('times','B',30);
//        // Move to the right
////        $this->Cell(80);
//        // Title
//
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
//
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,html_entity_decode($this->company_branch_name),0,0,'C');
//        $this->SetFont('Arial','B',8);
//
//        $this->Cell(50, 10,'Warehouse = '.$this->filter['warehouse'], 0, 0, 'L');
//
//        // Line break
//        $this->Ln(6);
//        // Arial bold 15
//        $this->SetFont('Arial','B',15);
//        // Move to the right
//        $this->Cell(70);
//        // Title
//        $this->Cell(70,10,'Purchase Report',0,0,'C');
//        $this->SetFont('Arial','B',8);
//
//        $this->Cell(50, 10,'Product ='.$this->filter['product'], 0,0    , 'L');
//        $this->Ln(6);
//        $this->Cell(140);
//        $this->Cell(50, 10,'department ='.$this->filter['department'], 0,0 , 'L');
//        // Line break
//        $this->Ln(15);
//
//    }
//
//// Page footer
//    function Footer()
//    {
//
//        // Position at 1.5 cm from bottom
//        $this->SetY(-15);
//        // Arial italic 8
//        $this->SetFont('Arial','I',8);
//        // Page number
//        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
//    }
//
//    function Body($data) {
//
//        $header = array('Date', 'Invoice', 'Warehouse', 'department', 'Category', 'Product', 'Qty', 'Rate', 'Discount', 'Tax', 'Amount');
//        $w = array(14, 15, 20, 22, 25, 26, 16, 10, 14, 14, 14);
//
//        $previous_group = '';
//        foreach($data as $group => $rows) {
//            if($previous_group != $group && $previous_group != '') {
//                $this->AddPage();
//            }
//
//            $previous_group = $group;
//            $this->SetFont('helvetica','B',11);
//            $this->SetTextColor(0);
//
//            if($this->group_heading == 'document_date'){
//                $this->Cell(30,10,'Invoice Date :',0,0,'L');
//                $this->Cell(30,10,std($group),0,0,'L');}
//            else if($this->group_heading == 'department'){
//                $this->Cell(20,10,'Supplier :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            }
//            else if($this->group_heading == 'product'){
//                $this->Cell(20,10,'Product :',0,0,'L');
//                $this->Cell(30,10,$group,0,0,'L');
//            } else if($this->group_heading == 'warehouse'){
//                $this->Cell(30,10,'Warehouse :',0,0,'L');
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
//            $total_discount=0; $total_tax = 0; $total_expense = 0; $total_amount = 0;
//            foreach($rows as $row)
//            {
//                $this->Cell($w[0],6,$row['document_date'],'LR',0,'L',$fill);
//                $this->Cell($w[1],6,$row['document_identity'],'LR',0,'L',$fill);
//                $this->Cell($w[2],6,$row['warehouse'],'LR',0,'L',$fill);
//                $this->Cell($w[3],6,$row['department'],'LR',0,'L',$fill);
//                $this->Cell($w[4],6,$row['product_category'],'LR',0,'L',$fill);
//                $this->Cell($w[5],6,$row['product'],'LR',0,'L',$fill);
//                $this->Cell($w[6],6,$row['qty'] . ' ' . $row['unit'] ,'LR',0,'L',$fill);
//                $this->Cell($w[7],6,number_format($row['rate'],2),'LR',0,'R',$fill);
//                $this->Cell($w[8],6,number_format($row['discount_amount'],2),'LR',0,'R',$fill);
//                $this->Cell($w[9],6,number_format($row['tax_amount'],2),'LR',0,'R',$fill);
//                $this->Cell($w[11],6,number_format($row['total_amount'],2),'LR',0,'R',$fill);
//                $this->Ln();
//                $fill = !$fill;
//                $total_discount += $row['discount_amount'];
//                $total_tax += $row['tax_amount'];
//                $total_amount += $row['total_amount'];
//            }
//
//            $this->SetFillColor(255,255,255);
//            $this->SetTextColor(0);
//            //$this->SetDrawColor(128,0,0);
//            $this->SetLineWidth(.3);
//            $this->SetFont('','B','7');
//            $this->Cell($w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5] + $w[6] + $w[7],6,'',1,0,'L',true);
//            $this->Cell($w[8],6,number_format($total_discount,2),1,0,'R',true);
//            $this->Cell($w[9],6,number_format($total_tax,2),1,0,'R',true);
//            $this->Cell($w[11],6,number_format($total_amount,2),1,0,'R',true);
//            $this->Ln();
//            // Closing line
//            $this->Cell(array_sum($w),0,'','T');
//        }
//    }
//}

?>