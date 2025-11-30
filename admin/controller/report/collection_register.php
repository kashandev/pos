<?php

class ControllerReportCollectionRegister extends HController {

    protected function getAlias() {
        return 'report/collection_register';
    }

    protected function getList() {
        parent::getList();

        $receipt_types = array();
        $receipt_types[] = array('id' => 'General Receipt', 'name' => 'General Receipt');
        $receipt_types[] = array('id' => 'Member Receipt', 'name' => 'Member Receipt');
        $receipt_types[] = array('id' => 'Donor Receipt', 'name' => 'Donor Receipt');
        $receipt_types[] = array('id' => 'Ground Receipt', 'name' => 'Ground Receipt');
        $this->data['receipt_types'] = $receipt_types;

        $this->model['member'] = $this->load->model('setup/member');
        $this->data['members'] = $this->model['member']->getRows(array('status' => 'Active'));

        $this->model['activity'] = $this->load->model('module/activity');
        $this->data['activities'] = $this->model['activity']->getRows(array('status' => 'Active'));

        $this->model['sport'] = $this->load->model('setup/sport');
        $this->data['sports'] = $this->model['sport']->getRows(array('status' => 'Active'));

        $this->data['date_from'] = stdDate();
        $this->data['date_to'] = stdDate();

        $this->data['href_get_report'] = $this->url->link($this->getAlias() . '/getReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() . '/printReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getReport() {
        $post = $this->request->post;
        $filter = array();
        if($post['date_from'] != '') {
            $filter[] = "`receipt_date` >= '".MySqlDate($post['date_from'])."'";
        }
        if($post['date_to'] != '') {
            $filter[] = "`receipt_date` <= '".MySqlDate($post['date_to'])."'";
        }
        if($post['receipt_type'] != '') {
            $filter[] = "`receipt_type` = '".$post['receipt_type']."'";
        }
        if($post['member_id'] != '') {
            $filter[] = "`partner_type`='Member' AND `partner_id` = '".$post['member_id']."'";
        }
        if($post['sport_id'] != '') {
            $filter[] = "`sport_id` = '".$post['sport_id']."'";
        }
        $where = implode(' AND ', $filter);

        $this->model['collection_register'] = $this->load->model('report/collection_register');
        $rows = $this->model['collection_register']->getReports($where);
        $html = '';
        foreach($rows as $row) {
            if($row['receipt_type']=='General Receipt') {
                $href = $this->url->link('module/general_receipt/update', 'token=' . $this->session->data['token'] . '&general_receipt_id=' . $row['id'], 'SSL');
            } elseif($row['receipt_type']=='Member Receipt') {
                $href = $this->url->link('module/member_receipt/update', 'token=' . $this->session->data['token'] . '&member_receipt_id=' . $row['id'], 'SSL');
            } elseif($row['receipt_type']=='Donor Receipt') {
                $href = $this->url->link('module/donor_receipt/update', 'token=' . $this->session->data['token'] . '&donor_receipt_id=' . $row['id'], 'SSL');
            } elseif($row['receipt_type']=='Ground Receipt') {
                $href = $this->url->link('ground/receipt/update', 'token=' . $this->session->data['token'] . '&ground_receipt_id=' . $row['id'], 'SSL');
            }
            $html .= '<tr>';
            $html .= '<td>'.$row['receipt_type'].'</td>';
            $html .= '<td>'.stdDate($row['receipt_date']).'</td>';
            $html .= '<td><a target="_blank" href="'.$href.'">'.$row['receipt_identity'].'</a></td>';
            $html .= '<td>'.$row['receipt_amount'].'</td>';
            $html .= '<td>'.$row['partner_name'].'</td>';
            $html .= '<td>'.stdDateTime($row['created_at']).'</td>';
            $html .= '</tr>';
        }

        $json = array(
            'success' => true,
            'post' => $post,
            'filter' => $filter,
            'rows' => $rows,
            'html' => $html,
        );
        echo json_encode($json);
        exit;
    }

    public function printReport() {
        ini_set('max_execution_time',400);

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;

        $this->model['member'] = $this->load->model('setup/member');
        $arrMembers = $this->model['member']->getArrays('member_id', 'member_name');

        $this->model['sport'] = $this->load->model('setup/sport');
        $arrSports = $this->model['sport']->getArrays('sport_id', 'sport_name');

        $filter = array();
        $arrFilter = array();
        if($post['date_from'] != '') {
            $filter[] = "`receipt_date` >= '".MySqlDate($post['date_from'])."'";
            $arrFilter[] = "From Date: " . $post['date_from'];
        }
        if($post['date_to'] != '') {
            $filter[] = "`receipt_date` <= '".MySqlDate($post['date_to'])."'";
            $arrFilter[] = "To Date: " . $post['date_to'];
        }
        if($post['receipt_type'] != '') {
            $filter[] = "`receipt_type` = '".$post['receipt_type']."'";
            $arrFilter[] = "Receipt Type: " . $post['receipt_type'];
        }
        if($post['member_id'] != '') {
            $filter[] = "`partner_type`='Member' AND `partner_id` = '".$post['member_id']."'";
            $arrFilter[] = "Member: " . $arrMembers[$post['member_id']];
        }
        if($post['sport_id'] != '') {
            $filter[] = "`sport_id` = '".$post['sport_id']."'";
            $arrFilter[] = "Sport: " . $arrSports[$post['sport_id']];
        }
        $where = implode(' AND ', $filter);

        $this->model['collection_register'] = $this->load->model('report/collection_register');
        $rows = $this->model['collection_register']->getReports($where, array('receipt_date', 'receipt_identity'));
        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->filter = $arrFilter;

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Collection Register');
        $pdf->SetSubject('Collection Register');

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 38, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // add a page
        $pdf->AddPage();

//        $pdf->Cell(10, 10, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(20, 10, 'Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(30, 10, 'Receipt No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(20, 10, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Cell(100, 10, 'Member', 1, false, 'C', 0, '', 0, false, 'M', 'M');
//        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 10);
        $sr = 0;
        $total_amount = 0;
        foreach($rows as $detail) {
            $sr++;
            $pdf->Cell(10, 7, $sr, 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, $detail['receipt_date'], 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 7, $detail['receipt_identity'], 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($detail['receipt_amount'],2), 1, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(100, 7, $detail['partner_name'], 1, false, 'L', 0, '', 0, false, 'M', 'M');
            $total_amount += $detail['receipt_amount'];
            $pdf->Ln(7);
        }
        $pdf->Cell(60, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
        $pdf->Cell(20, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');

        //Close and output PDF document
        $pdf->Output('Collection Register:'.date('YmdHis').'.pdf', 'I');
    }
}

class PDF extends TCPDF {
    public $filter;
    //Page header
    public function Header() {
        // Logo
        //$image_file = DIR_IMAGE.'logo.jpg';
        //$image_file = DIR_IMAGE.'no_image.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 14);
        // Title
//        $this->Cell(0, 10, 'ANB - Sport Academy', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Ln(10);
//        $this->Cell(0, 10, 'Cash Register', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $html = '';
        $html .= '<table style="width: 100%;">';
        $html .= '  <tr>';
        $html .= '  <td style="width: 30%;font-weight: normal;font-size: 10px;">';
        foreach($this->filter as $filter) {
            $html .= $filter . '<br />';
        }
        $html .= '  </td>';
        $html .= '  <td style="width: 40%; text-align: center;">';
        $html .= '      ANB - Sport Academy';
        $html .= '      <br />';
        $html .= '      Cash Register';
        $html .= '  </td>';
        $html .= '  <td style="width: 30%;">';
        $html .= '  </td>';
        $html .= '  </tr>';
        $html .= '</table>';

        $this->writeHTML($html);

        // set font
        $this->SetFont('helvetica', 'BI', 12);

        $this->Cell(10, 10, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 10, 'Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(30, 10, 'Receipt No.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(20, 10, 'Amount', 1, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Cell(100, 10, 'Member', 1, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(50, 10, '', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(80, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(50, 10, 'Date : '.date('d M Y H:i'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
?>