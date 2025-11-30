<?php

class ControllerReportDocumentLedger extends HController {

    protected function getAlias() {
        return 'report/document_ledger';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('gl/ledger');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['partner_types'] = $this->session->data['partner_types'];

        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_get_detail_report'] = $this->url->link($this->getAlias() .'/getDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_summary_report'] = $this->url->link($this->getAlias() .'/getSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_detail_report'] = $this->url->link($this->getAlias() .'/printDetailReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_summary_report'] = $this->url->link($this->getAlias() .'/printSummaryReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate(($this->session->data['fiscal_date_to'] > date('Y-m-d') ? '' : $this->session->data['fiscal_date_to']));

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'people_type_id' : {'required' : true}
            },
            ignore:[],
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getDetailReport() {
        //$lang = $this->load->language('report/party_ledger');
        $post = $this->request->post;
        $session = $this->session->data;

        $this->model['party_ledger'] = $this->load->model('report/party_ledger');
        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['from_date'] != '') {
            $where .= " AND l.document_date >= '".$post['from_date']."'";
        }
        if($post['to_date'] != '') {
            $where .= " AND l.document_date <= '".$post['to_date']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }

        $rows = $this->model['party_ledger']->getPartyLedger($where);
// d($rows,true);
        $html = '';
        foreach($rows as $row) {
            $html .='<tr>';
            $html .='<td>'.$row['partner_type'].'</td>';
            $html .='<td>'.$row['partner_name'].'</td>';
            $html .='<td>'.$row['document_date'].'</td>';
            $html .='<td>'.$row['document_identity'].'</td>';
            $html .='<td>'.$row['ref_document_identity'].'</td>';
            $html .='<td>'.$row['remarks'].'</td>';
            $html .='<td>'.$row['account'].'</td>';
            $html .='<td>'.$row['debit'].'</td>';
            $html .='<td>'.$row['credit'].'</td>';
            $html .='</tr>';
        }

        $json = array(
            'post' => $post,
            'rows' => $rows,
            'html' => $html,
            'success' => true,
        );

        echo json_encode($json);
        exit;
    }

    public function printDetailReport() {

        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        //d($lang,true);
        $post = $this->request->post;
        $session = $this->session->data;

        if($post['date_from'] == "") {
            $post['date_from'] = $session['date_from'];
        } else {
            $post['date_from'] = MySqlDate($post['date_from']);
        }
        if($post['date_to'] == "") {
            $post['date_to'] = $session['date_to'];
        } else {
            $post['date_to'] = MySqlDate($post['date_to']);
        }

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date < '".$post['date_from']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyOpening($where, array('partner_type', 'partner_name'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][] = $row;
        }

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_from'] != '') {
            $where .= " AND l.document_date >= '".$post['date_from']."'";
        }
        if($post['date_to'] != '') {
            $where .= " AND l.document_date <= '".$post['date_to']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyLedger($where, array('partner_type', 'partner_name','document_date', 'document_identity', 'ref_document_identity'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][] = $row;
        }

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRows(array('company_id' => $this->session->data['company_id']));

        $data = array(
            'lang' => $lang,
            'company' => $company,
            'filter' => $post,
            'rows' => $arrLedger
        );

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Hira');
        $pdf->SetTitle('Document Ledger Report Detail');
        $pdf->SetSubject('Document Ledger Report Detail');

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 10);

        $pdf->ln(12);
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 60, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 22, 7, 'Debit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 22, 7, 'Credit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $pdf->Cell( 25, 7, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');

        $sr =0;
        $total_debit = 0;
        $total_credit = 0;
//            $pdf->Ln(1);
        foreach($arrLedger as $detail) {
            $total_debit += $detail['debit'];
            $total_credit += $detail['credit'];
            $balance = $total_debit - $total_credit;
            $sr++;
            $pdf->Ln(7);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($detail['debit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($detail['credit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(25, 7, number_format($balance,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        }
        $pdf->Ln(7);
        $pdf->Cell(117, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(22, 7, number_format($total_debit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
        $pdf->Cell(22, 7, number_format($total_credit,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');


        //Close and output PDF document
        $pdf->Output('Ledger Report:'.date('YmdHis').'.pdf', 'I');








        //d($data, true);
//        try
//        {
//            $pdf=new mPDF();
//
//            $pdf->SetDisplayMode('fullpage');
//            //d($data,true);
//            $pdf->mPDF('utf-8','A4','','','15','15','35','18');
//            $pdf->setHTMLHeader($this->getPDFHeader($data));
//            $pdf->setHTMLFooter($this->getPDFFooter($data));
//            $pdf->WriteHTML($this->getPDFStyle($data));
//            $pdf->WriteHTML($this->getPDFBodyDetail($data));
//
//            $pdf->Output();
//        }
//        catch(Exception $e) {
//            echo $e;
//            exit;
//        }
//        exit;
    }

    public function printSummaryReport() {

        ini_set('max_execution_time',400);
        ini_set('memory_limit','3072M');

        $lang = $this->load->language($this->getAlias());
        //d($lang,true);
        $post = $this->request->post;
        $session = $this->session->data;

        if($post['date_from'] == "") {
            $post['date_from'] = $session['date_from'];
        } else {
            $post['date_from'] = MySqlDate($post['date_from']);
        }
        if($post['date_to'] == "") {
            $post['date_to'] = $session['date_to'];
        } else {
            $post['date_to'] = MySqlDate($post['date_to']);
        }

        if($post['partner_type_id'] != "") {
            $this->model['partner_type'] = $this->load->model('common/partner_type');
            $partner_type = $this->model['partner_type']->getRow(array('partner_type_id' => $post['partner_type_id']));
            $post['partner_type'] = $partner_type['name'];
        } else {
            $post['partner_type'] = '';
        }

        if($post['partner_id'] != "") {
            $this->model['partner'] = $this->load->model('common/partner');
            $partner = $this->model['partner']->getRow(array('partner_id' => $post['partner_id']));
            $post['partner_name'] = $partner['name'];
        } else {
            $post['partner_name'] = '';
        }

        $arrLedger = array();
        $this->model['party_ledger'] = $this->load->model('report/party_ledger');

        $where = "l.company_id = '".$this->session->data['company_id']."'";
        $where .= " AND l.company_branch_id = '".$this->session->data['company_branch_id']."'";
        $where .= " AND l.fiscal_year_id = '".$this->session->data['fiscal_year_id']."'";
        if($post['date_to'] != '') {
            $where .= " AND l.document_date <= '".$post['date_to']."'";
        }
        if($post['partner_type_id'] != '') {
            $where .= " AND l.partner_type_id = '".$post['partner_type_id']."'";
        }
        if($post['partner_id'] != '') {
            $where .= " AND l.partner_id = '".$post['partner_id']."'";
        }
        $rows = $this->model['party_ledger']->getPartyLedger($where, array('partner_type', 'partner_name','document_date', 'document_identity', 'ref_document_identity'));
        foreach($rows as $row) {
            $arrLedger[$row['partner_type'] . ': ' . $row['partner_name']][] = $row;
        }

        $this->model['company'] = $this->load->model('setup/company');
        $company = $this->model['company']->getRows(array('company_id' => $this->session->data['company_id']));

        $data = array(
            'lang' => $lang,
            'company' => $company,
            'filter' => $post,
            'rows' => $arrLedger
        );

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));

        $session = $this->session->data;
        $company_logo = $setting['value'];

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Document Ledger Report');
        $pdf->SetSubject('Document Ledger Report');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(15, 35, 5);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        // set font
            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 10);

            $pdf->ln(12);
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Cell(  7, 7, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 20, 7, 'Doc. Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 30, 7, 'Doc. No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 60, 7, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 22, 7, 'Debit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 22, 7, 'Credit', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell( 25, 7, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');

            $sr =0;
            $total_debit = 0;
            $total_credit = 0;
//            $pdf->Ln(1);
            foreach($arrLedger as $detail) {
                $total_debit += $detail['debit'];
                $total_credit += $detail['credit'];
                $balance = $total_debit - $total_credit;
                $sr++;
                $pdf->Ln(7);
                $pdf->SetFont('helvetica', '', 8);
                $pdf->Cell(7, 7, $sr, 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, $detail['document_date'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(30, 7, $detail['document_identity'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(22, 7, number_format($detail['debit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(22, 7, number_format($detail['credit'],2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(25, 7, number_format($balance,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            }
            $pdf->Ln(7);
            $pdf->Cell(117, 7,'', 0, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($total_debit,2), 1, false, 'R', 0, '', 1, false, 'M', 'M');
            $pdf->Cell(22, 7, number_format($total_credit,4), 1, false, 'R', 0, '', 1, false, 'M', 'M');


        //Close and output PDF document
        $pdf->Output('Ledger Report:'.date('YmdHis').'.pdf', 'I');

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
            $this->Image($image_file, 10, 10, 30, '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }
        // Set font
        $this->SetFont('times', 'B', 20);
        $this->Ln(2);
        // Title
        $this->Cell(0, 10, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('times', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
?>