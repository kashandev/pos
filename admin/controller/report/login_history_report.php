<?php

class ControllerReportLoginHistoryReport extends HController {

    protected function getAlias() {
        return 'report/login_history_report';
    }

    protected function getDefaultOrder() {
        return 'ledger_id';
    }

    protected function getDefaultSort() {
        return 'DESC';
    }

    protected function getList() {
        parent::getList();

        $this->model['user'] = $this->load->model('user/user');
        $this->data['users'] = $this->model['user']->getRows();

        $this->data['date_from'] = stdDate();
        $this->data['date_to'] = stdDate();
//        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');

//        $this->data['strValidation'] = "{
//            'rules': {
//                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
//                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
//
//            },
//            ignore:[],
//        }";

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

        $this->model['customer'] = $this->load->model('setup/customer');

        //$this->model['trial_balance'] = $this->load->model('report/trial_balance');
        $post = $this->request->post;


//d($post,true);
        $session = $this->session->data;
//                d($session['company_name'],true);
        $datefrom = MySqlDate($post['date_from']);
        $dateto = MySqlDate($post['date_to']);
        $user_id = $post['user_id'];
        $first=$post['with_first'];


        $this->model['login_history_report'] = $this->load->model('report/login_history_report');
        $rows = $this->model['login_history_report']->getReports($datefrom,$dateto,$user_id,$first);

        $name = "";

        if($user_id != '')
        {
            $this->model['user'] = $this->load->model('user/user');
            $customer = $this->model['user']->getRow(array('user_id' => $user_id));
            $name = $customer['user_name'];
        }



        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Fahad Siddiqui');
        $pdf->SetTitle('Login History Report');
        $pdf->SetSubject('Login History Report');

        //Set Header
        $pdf->data = array(
            'report_name' => 'Login History Report',
            'customer_name' => $name,
            'from_date' => $post['date_from'],
            'to_date'   => $post['date_to'],
            'company_name'   => $session['company_name'],

        );


//        d($rows,true);
        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(5, 42, 5);
        $pdf->SetHeaderMargin(2);
        $pdf->SetFooterMargin(2);

        // set auto page breaks
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('times', 'B', 10);


        $pdf->Ln(0);

        // add a page
        $pdf->AddPage();

            $pdf->SetFont('times', 'B', 9);

        $sr=0;
            foreach($rows as $row) {

                $pdf->SetFont('times', '', 9);

                $sr++;
                $pdf->Cell(10, 6,$sr, 1, false, 'L', 0, '', 1);
                $pdf->Cell(120,6,$row['user_name'], 1, false, 'L', 0, '', 1);
                $pdf->Cell(35, 6,stdDateTime($row['login_time']), 1, false, 'L', 0, '', 1);
                $pdf->Cell(35, 6,stdDateTime($row['first_login']), 1, false, 'L', 0, '', 1);

                $pdf->ln(6);

            }

        $pdf->Ln(4);

        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->setXY($x,$y);
        $pdf->SetFont('times', 'B', 7);

        //Close and output PDF document
        $pdf->Output('Login History Report:'.date('YmdHis').'.pdf', 'I');
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
        $this->Ln(8);
        $this->SetFont('helvetica', 'B', 16);
        $this->Cell(0, 10, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(8);
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(0, 10, 'From Date : '.$this->data['from_date'] . '     To Date : '.$this->data['to_date'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->SetFont('helvetica', 'B', 8);
        $this->Ln(6);
        $this->Cell(0, 10, 'User Name : '. $this->data['customer_name'], 0, false, 'R', 0, '', 0, false, 'M', 'M');

        $this->Ln(10);

        $this->SetFont('times', 'B', 9);
        $this->Cell(10, 6,  ' Sr# ', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(120,6,  ' User ', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(35, 6, ' Last Login', 1, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(35, 6, ' First Login', 1, false, 'L', 0, '', 0, false, 'M', 'M');

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