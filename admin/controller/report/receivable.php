<?php

class ControllerReportReceivable extends HController {

    protected function getAlias() {
        return 'report/receivable';
    }

    protected function init() {
        $this->model[$this->getAlias()] = $this->load->model('common/receivable');
        $this->data['lang'] = $this->load->language($this->getAlias());
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    protected function getList() {
        parent::getList();

        $this->data['receivable_types'][] = 'Ground Receivable';
        $this->data['receivable_types'][] = 'Academy Receivable';

        $this->data['partner_types'][] = 'Member';
        $this->data['partner_types'][] = 'Donor';



        $this->data['href_get_partner'] = $this->url->link($this->getAlias() . '/getPartner', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_get_report'] = $this->url->link($this->getAlias() . '/getReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_report'] = $this->url->link($this->getAlias() . '/printReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function getPartner() {
        $partner_type = $this->request->post['partner_type'];
        if($partner_type=='Donor') {
            $this->model['donor'] = $this->load->model('setup/donor');
            $rows = $this->model['donor']->getRows();
            $html = '<option value="">&nbsp;</option>';
            foreach($rows as $row) {
                $html .= '<option value="'.$row['donor_id'].'">'.$row['donor_name'].'</option>';
            }
        } else {
            $this->model['member'] = $this->load->model('setup/member');
            $rows = $this->model['member']->getRows();
            $html = '<option value="">&nbsp;</option>';
            foreach($rows as $row) {
                $html .= '<option value="'.$row['member_id'].'">'.$row['reg_no'] .'-'. $row['member_name'] .'-'. $row['mobile_no'].'</option>';
            }
        }

        $json = array(
            'success' => true,
            'html' => $html
        );

        echo json_encode($json);
    }

    public function getReport() {
        $post = $this->request->post;
        //d($post, true);
        $filter = array();
        if($post['receivable_type'] != '') {
            $filter[] = "`receivable_type` = '".$post['receivable_type']."'";
        }
        if($post['partner_type'] != '') {
            $filter[] = "`partner_type` = '".$post['partner_type']."'";
        }
        if($post['partner_id'] != '') {
            $filter[] = "`partner_id` = '".$post['partner_id']."'";
        }
        $where = implode(' AND ', $filter);

        $this->model['receivable'] = $this->load->model('common/receivable');
        $rows = $this->model['receivable']->getRows($where);

        $html = '';
        foreach($rows as $row) {
            $html .= '<tr>';
            $html .= '<td>'.$row['receivable_type'].'</td>';
            $html .= '<td>'.$row['partner_type'].'</td>';
            $html .= '<td>'.$row['partner_name'].'</td>';
            $html .= '<td>'.stdDate($row['receivable_date']).'</td>';
            $html .= '<td>'.$row['balance_amount'].'</td>';
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
        $filter = array();
        if($post['receivable_type'] != '') {
            $filter[] = "`receivable_type` = '".$post['receivable_type']."'";
        }
        if($post['partner_type'] != '') {
            $filter[] = "`partner_type` = '".$post['partner_type']."'";
        }
        if($post['partner_id'] != '') {
            $filter[] = "`partner_id` = '".$post['partner_id']."'";
        }
        $where = implode(' AND ', $filter);

        $this->model['receivable'] = $this->load->model('common/receivable');
        $rows = $this->model['receivable']->getRows($where, array('partner_type','partner_name','receivable_date'));
        $records = array();
        foreach($rows as $row) {
            $records[$row['receivable_type']][] = $row;
        }

        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Receivable Report');
        $pdf->SetSubject('Receivable Report');

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // add a page
        $pdf->AddPage();

        $page_no =1;
        foreach($records as $receivable_type => $record) {
            // set font
            $pdf->SetFont('helvetica', 'BI', 12);
            $pdf->Cell(0, 10, $receivable_type, 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(10);
            $pdf->Cell(10, 10, 'Sr.', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 10, 'Name', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(30, 10, 'Receivable Date', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(60, 10, 'Remarks', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 10, 'Balance', 1, false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', '', 10);
            $sr = 0;
            $total_amount = 0;
            foreach($record as $detail) {
                $sr++;
                $pdf->Ln(7);
                $pdf->Cell(10, 7, $sr, 1, false, 'L', 0, '', 0, false, 'M', 'M');
                if($detail['partner_type'] == 'Donor') {
                    $pdf->Cell(60, 7, $detail['partner_name'].' [Donor]', 1, false, 'L', 0, '', 0, false, 'M', 'M');
                } else {
                    $pdf->Cell(60, 7, $detail['partner_name'].' ['.$detail['reg_no'].']', 1, false, 'L', 0, '', 0, false, 'M', 'M');
                }
                $pdf->Cell(30, 7, stdDate($detail['receivable_date']), 1, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(60, 7, $detail['remarks'], 1, false, 'L', 0, '', 1, false, 'M', 'M');
                $pdf->Cell(20, 7, number_format($detail['balance_amount'],2), 1, false, 'R', 0, '', 0, false, 'M', 'M');
                $total_amount += $detail['balance_amount'];
            }
            $pdf->Ln(7);
            $pdf->Cell(160, 7, '', 0, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(20, 7, number_format($total_amount,2), 1, false, 'R', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(1);
            if($page_no == count($records)) {
                $pdf->AddPage();
            }
        }

        //Close and output PDF document
        $pdf->Output('Receivable Report:'.date('YmdHis').'.pdf', 'I');
    }

}

class PDF extends TCPDF {

    //Page header
    public function Header() {
        // Logo
        //$image_file = DIR_IMAGE.'logo.jpg';
        //$image_file = DIR_IMAGE.'no_image.jpg';
        //$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        // Set font
        $this->SetFont('helvetica', 'B', 20);
        // Title
        $this->Cell(0, 10, 'ANB - Sport Academy', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(10);
        $this->Cell(0, 10, 'Receivable Report', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(60, 10, '', 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(60, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(60, 10, 'Date: '.date('d-m-Y H:i'), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}
?>