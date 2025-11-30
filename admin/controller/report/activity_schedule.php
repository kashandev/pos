<?php

class ControllerReportActivitySchedule extends HController {

    protected function getAlias() {
        return 'report/activity_schedule';
    }

    protected function getList() {
        parent::getList();

        $this->model['mohallah'] = $this->load->model('setup/mohallah');
        $this->data['mohallahs'] = $this->model['mohallah']->getRows();

        $this->model['member'] = $this->load->model('setup/member');
        $this->data['members'] = $this->model['member']->getRows(array('status' => 'Active'));

        $this->model['sport'] = $this->load->model('setup/sport');
        $this->data['sports'] = $this->model['sport']->getRows();

        $this->model['activity'] = $this->load->model('module/activity');
        $this->data['activities'] = $this->model['activity']->getRows();

        $this->model['trainer'] = $this->load->model('setup/trainer');
        $this->data['trainers'] = $this->model['trainer']->getRows();

        $this->model['report_criteria'] = $this->load->model('report/criteria');
        $rows = $this->model['report_criteria']->getRows(array('report_name' => 'activity_schedule', 'user_id' => $this->session->data['user_id']),array('created_at DESC'));
        $reports = array();
        foreach($rows as $row) {
            $reports[] = array(
                'report_criteria_id' => $row['report_criteria_id'],
                'report_id' => $row['report_id'],
                'report_title' => $row['report_title']
            );
        }

        $this->data['reports'] = $reports;

        $this->data['action_filter_activity_schedule'] = $this->url->link('report/activity_schedule/filterReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_pdf_activity_schedule'] = $this->url->link('report/activity_schedule/pdfReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_excel_activity_schedule'] = $this->url->link('report/activity_schedule/excelReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['action_save_activity_schedule'] = $this->url->link('report/activity_schedule/saveReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_remove_activity_schedule'] = $this->url->link('report/activity_schedule/removeReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_load_activity_schedule'] = $this->url->link('report/activity_schedule/loadReport', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    public function filterReport() {
        $post = $this->request->post;
        $display_columns = $post['display_columns'];
        $lang = $this->load->language('report/activity_schedule');
        $this->model['activity_report'] = $this->load->model('report/activity_schedule');

        //d($post, true);
        $filter = array();
        if(isset($post['sport_id']) && $post['sport_id'] != '') {
            $filter[] = "`sport_id` = '". $post['sport_id'] . "'";
        }
        if(isset($post['trainer_id']) && $post['trainer_id'] != '') {
            $filter[] = "`trainer_id` = '". $post['trainer_id'] . "'";
        }
        if(isset($post['activity_id']) && $post['activity_id'] != '') {
            $filter[] = "`activity_id` = '". $post['activity_id'] . "'";
        }
        if(isset($post['mohallah_id']) && $post['mohallah_id'] != '') {
            $filter[] = "`mohallah_id` = '". $post['mohallah_id'] . "'";
        }
        if(isset($post['member_id']) && $post['member_id'] != '') {
            $filter[] = "`member_id` = '". $post['member_id'] . "'";
        }
        $where = implode(' AND ', $filter);

        $rows = $this->model['activity_report']->getRows($display_columns,$where, $post['show_empty']);

        $html = '';
        $html .= '<table class="table table-bordered table-striped">' . PHP_EOL;
        $html .= '<thead>' . PHP_EOL;
        $html .= '<tr>' . PHP_EOL;
        $html .= '<td>Sr.</td>' . PHP_EOL;
        foreach($display_columns as $column) {
            $html .= '  <td>'.$column['display_name'].'</td>' . PHP_EOL;
        }
        $html .= '</tr>' . PHP_EOL;
        $html .= '</thead>' . PHP_EOL;
        $html .= '<tbody>' . PHP_EOL;
        foreach($rows as $row_no => $row) {
            $html .='<tr>' . PHP_EOL;
            $html .= '<td>'.($row_no+1).'</td>' . PHP_EOL;
            foreach($display_columns as $column) {
                $html .= '  <td>'.$row[$column['display_name']].'</td>' . PHP_EOL;
            }
            $html .='</tr>' . PHP_EOL;
        }
        $html .= '</tbody>' . PHP_EOL;
        $html .= '</table>' . PHP_EOL;

        $json = array(
            'success' => true,
            'post' => $post,
            'filter' => $filter,
            'html' => $html
        );

        echo json_encode($json);
        exit;
    }

    public function pdfReport() {
        //d($this->request, true);
        ini_set('max_execution_time',0);
        $post = $this->request->get;
        $display_columns = $post['display_columns'];
        $lang = $this->load->language('report/activity_schedule');
        $this->model['activity_schedule'] = $this->load->model('report/activity_schedule');
        $this->model['sport'] = $this->load->model('setup/sport');
        $this->model['activity'] = $this->load->model('module/activity');
        $this->model['trainer'] = $this->load->model('setup/trainer');
        $this->model['mohallah'] = $this->load->model('setup/mohallah');
        $this->model['member'] = $this->load->model('setup/member');

        $arrMohallahs = $this->model['mohallah']->getArrays('mohallah_id', 'mohallah_name');
        $arrMembers = $this->model['member']->getArrays('member_id', 'member_name');
        $arrSports = $this->model['sport']->getArrays('sport_id', 'sport_name');
        $arrActivities = $this->model['activity']->getArrays('activity_id', 'activity_name');
        $arrTrainers = $this->model['trainer']->getArrays('trainer_id', 'trainer_name');

        $filter = array();
        if(isset($post['sport_id']) && $post['sport_id'] != '') {
            $filter[] = "`sport_id` = '". $post['sport_id'] . "'";
        }
        if(isset($post['trainer_id']) && $post['trainer_id'] != '') {
            $filter[] = "`trainer_id` = '". $post['trainer_id'] . "'";
        }
        if(isset($post['activity_id']) && $post['activity_id'] != '') {
            $filter[] = "activity_id = '". $post['activity_id'] . "'";
        }
        if(isset($post['mohallah_id']) && $post['mohallah_id'] != '') {
            $filter[] = "mohallah_id = '". $post['mohallah_id'] . "'";
        }
        if(isset($post['member_id']) && $post['member_id'] != '') {
            $filter[] = "member_id = '". $post['member_id'] . "'";
        }
        $where = implode(' AND ', $filter);
        //d(array($post, $filter, $where), true);
        $rows = $this->model['activity_schedule']->getRows($display_columns,$where, $post['show_empty']);

        $header = '';
        $header .= '<table width"100%" cellpadding="3">';
        $header .= '<tr>';
        $header .= '<td width="30%" style="border: 1px solid #000000;">';
        if($filter) {
            $header .= '<table style="width: 100%;">';
            $header .= '<tr>';
            $header .= '<td colspan="2" style="text-align: center; border-bottom: 1px solid #000000;">Filter:</td>';
            $header .= '</tr>';
            if(isset($post['sport_id']) && $post['sport_id'] != '') {
                $header .= '<tr>';
                $header .= '<td style="width: 40%; text-align: left;">'.$lang['sport'].': </td><td>'. $arrSports[$post['sport_id']] . '</td>';
                $header .= '</tr>';
            }
            if(isset($post['activity_id']) && $post['activity_id'] != '') {
                $header .= '<tr>';
                $header .= '<td style="width: 40%; text-align: left;">'.$lang['activity'].': </td><td>'. $arrActivities[$post['activity_id']] . '</td>';
                $header .= '</tr>';
            }
            if(isset($post['trainer_id']) && $post['trainer_id'] != '') {
                $header .= '<tr>';
                $header .= '<td style="width: 40%; text-align: left;">'.$lang['trainer'].': </td><td>'. $arrTrainers[$post['trainer_id']] . '</td>';
                $header .= '</tr>';
            }
            if(isset($post['mohallah_id']) && $post['mohallah_id'] != '') {
                $header .= '<tr>';
                $header .= '<td style="width: 40%; text-align: left;">'.$lang['mohallah'].': </td><td>'. $arrMohallahs[$post['mohallah_id']] . '</td>';
                $header .= '</tr>';
            }
            if(isset($post['member_id']) && $post['member_id'] != '') {
                $header .= '<tr>';
                $header .= '<td style="width: 40%; text-align: left;">'.$lang['member'].': </td><td>'. $arrMembers[$post['member_id']] . '</td>';
                $header .= '</tr>';
            }
            $header .= '</table>';
        } else {
            $header .= '<table style="width: 100%;">';
            $header .= '<tr>';
            $header .= '<td colspan="2" style="text-align: center; border-bottom: 1px solid #000000;">Filter:</td>';
            $header .= '</tr>';
            $header .= '</table>';
        }
        $header .= '</td>';
        $header .= '<td width="40%">';
        $header .= '<table width="100%">';
        $header .= '<tr>';
        $header .= '<td style="text-align: center; font-size: 20px;">ANB - Sport Academy</td>';
        $header .= '</tr>';
        $header .= '<tr>';
        if($post['report_title']) {
            $header .= '<td style="text-align: center">'.$post['report_title'].'</td>';
        } else {
            $header .= '<td style="text-align: center">Activity Schedule</td>';
        }
        $header .= '</tr>';
        $header .= '</table>';
        $header .= '</td>';
        $header .= '<td width="30%"></td>';
        $header .= '</tr>';
        $header .= '</table>';
        //d($header, true);

        $footer = '<table width="100%" style="border-top: 1px solid #000000; vertical-align: bottom; font-family: serif; font-size: 9pt; color: #000088;" class="no-border">';
        $footer .= '<tr>';
        $footer .= '<td width="33%"><span style="font-size:10pt;">'.date('d-m-Y H:i').'</span></td>';
        $footer .= '<td width="33%" align="center">&nbsp;</td>';
        $footer .= '<td width="33%" style="text-align: right;"><span style="font-size:10pt;">{PAGENO}</span></td>';
        $footer .= '</tr>';
        $footer .= '</table>';

        $body = '';
        $body .= '<style>';
        $body .= 'body { font-family: "Ariel"; font-size: 11pt;  }';
        $body .= 'table {font-size: 9pt; line-height: 1.2; margin-top: 2pt; margin-bottom: 5pt; border-collapse: collapse; }';
        $body .= 'thead {font-weight: bold; vertical-align: bottom; }';
        $body .= 'tbody td {font-size: 9px; font-weight: normal; vertical-align: center; }';
        $body .= 'tfoot {font-weight: bold; vertical-align: top; }';
        $body .= 'thead td { font-weight: bold; }';
        $body .= 'tfoot td { font-weight: bold; }';
        $body .= 'thead td, thead th { background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;  }';
        //$body .= 'tfoot td, tfoot th { background-gradient: linear #b7cebd #f5f8f5 0 1 0 0.2;  }';
        $body .= 'th {font-weight: bold; vertical-align: top; adding-left: 2mm; padding-right: 2mm; padding-top: 0.5mm; padding-bottom: 0.5mm;border: 1px solid #dddddd;}';
        $body .= 'td {padding-left: 2mm; vertical-align: top; padding-right: 2mm; padding-top: 0.5mm; padding-bottom: 0.5mm;border: 1px solid #dddddd;}';
        $body .= '.no-border td {border: 0;}';
        $body .= '</style>';
        $display_columns = $post['display_columns'];

        $body .= '<table style="width: 100%" cellpadding="3">' . PHP_EOL;
        $body .= '<thead>' . PHP_EOL;
        $body .= '<tr>' . PHP_EOL;
        $body .= '  <td style="width: 25px;">Sr.</td>' . PHP_EOL;
        foreach($display_columns as $column) {
            $body .= '  <td>'.$column['display_name'].'</td>' . PHP_EOL;
        }
        $body .= '</tr>' . PHP_EOL;
        $body .= '</thead>' . PHP_EOL;
        $body .= '<tbody>' . PHP_EOL;
        //d(array($display_columns, $rows), true);
        $this->model['image'] = $this->load->model('tool/image');

        foreach($rows as $row_no => $row) {
            $body .='<tr>' . PHP_EOL;
            $body .= '  <td style="width: 25px; font-weight: normal;">'.($row_no+1).'</td>' . PHP_EOL;
            foreach($display_columns as $column) {
                if($column['column_name'] == 'member_image') {
                    if ($row[$column['display_name']] && file_exists(DIR_IMAGE . $row[$column['display_name']]) && is_file(DIR_IMAGE . $row[$column['display_name']])) {
                        $member_image = $this->model['image']->resize($row[$column['display_name']], 50, 50);
                    } else {
                        $member_image = $this->model['image']->resize('no_user.jpg', 50, 50);
                    }
                    $body .= '  <td style="font-weight: normal;">';
                    $body .= '<img src="'.$member_image.'" alt="Member Image" />';
                    $body .= '</td>' . PHP_EOL;
                } else {
                    $body .= '  <td style="font-weight: normal;">'.$row[$column['display_name']].'</td>' . PHP_EOL;
                }
            }
            $body .='</tr>' . PHP_EOL;
        }
        $body .= '</tbody>' . PHP_EOL;
        $body .= '</table>' . PHP_EOL;


        $pdf = new PDF($post['page_orientation'], PDF_UNIT, $post['page_size'], true, 'UTF-8', false);
        $pdf->post_data = $post;
        $pdf->header_data = $header;
        $pdf->footer_data = $footer;
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Huzaifa Khambaty');
        $pdf->SetTitle('Activity Schedule');
        $pdf->SetSubject('Activity Schedule');

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 35, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        $pdf->SetFont('helvetica', 'BI', 12);

        // add a page
        $pdf->AddPage();

        $pdf->writeHTML($body);

        //Close and output PDF document
        $pdf->Output('Activity Schedule:'.date('YmdHis').'.pdf', 'I');
        exit;
    }

    public function excelReport() {
        $post = $this->request->get;
        //d($post, true);
        $display_columns = $post['display_columns'];
        $lang = $this->load->language('report/activity_schedule');
        $this->model['activity_schedule'] = $this->load->model('report/activity_schedule');
        $this->model['sport'] = $this->load->model('setup/sport');
        $this->model['activity'] = $this->load->model('module/activity');
        $this->model['trainer'] = $this->load->model('setup/trainer');
        $this->model['mohallah'] = $this->load->model('setup/mohallah');
        $this->model['member'] = $this->load->model('setup/member');

        $arrMohallahs = $this->model['mohallah']->getArrays('mohallah_id', 'mohallah_name');
        $arrMembers = $this->model['member']->getArrays('member_id', 'member_name');
        $arrSports = $this->model['sport']->getArrays('sport_id', 'sport_name');
        $arrActivities = $this->model['activity']->getArrays('activity_id', 'activity_name');
        $arrTrainers = $this->model['trainer']->getArrays('trainer_id', 'trainer_name');

        $filter = array();
        $arrFilters = array();
        if(isset($post['sport_id']) && $post['sport_id'] != '') {
            $filter[] = "`sport_id` = '". $post['sport_id'] . "'";
            $arrFilters[] = "Sport = '". $arrSports[$post['sport_id']] . "'";
        }
        if(isset($post['trainer_id']) && $post['trainer_id'] != '') {
            $filter[] = "`trainer_id` = '". $post['trainer_id'] . "'";
            $arrFilters[] = "Trainer = '". $arrTrainers[$post['trainer_id']] . "'";
        }
        if(isset($post['activity_id']) && $post['activity_id'] != '') {
            $filter[] = "activity_id = '". $post['activity_id'] . "'";
            $arrFilters[] = "Activity = '". $arrActivities[$post['activity_id']] . "'";
        }
        if(isset($post['mohallah_id']) && $post['mohallah_id'] != '') {
            $filter[] = "mohallah_id = '". $post['mohallah_id'] . "'";
            $arrFilters[] = "Mohallah = '". $arrMohallahs[$post['mohallah_id']] . "'";
        }
        if(isset($post['member_id']) && $post['member_id'] != '') {
            $filter[] = "member_id = '". $post['member_id'] . "'";
            $arrFilters[] = "Member = '". $arrMembers[$post['member_id']] . "'";
        }
        $where = implode(' AND ', $filter);
        //d(array($post, $filter, $where), true);
        $rows = $this->model['activity_schedule']->getRows($display_columns,$where, $post['show_empty']);

        $columns = array_keys($rows[0]);
        $no_of_columns = count($columns);
        $start_column_idx = 0;
        $end_column_idx = $start_column_idx + $no_of_columns -1;

        try {
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getProperties()->setCreator("Huzaifa Khambaty")
                ->setLastModifiedBy("Huzaifa Khambaty")
                //->setDescription("Test document for PHPExcel, generated using PHP classes.")
                //->setKeywords("office PHPExcel php")
                //->setCategory("Test result file")
                ->setTitle($post['report_title'])
                ->setSubject($post['report_title']);

            $row_no = 0;
            $objPHPExcel->setActiveSheetIndex(0);

            // Set the Report Title
            $row_no++;
            $merge_cells = $this->cellsToMergeByColsRow($start_column_idx, $end_column_idx, $row_no);
            //d(array($start_column_idx, $no_of_columns, $end_column_idx, $merge_cells), true);
            $style = array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            );
            $objPHPExcel->getActiveSheet()
                ->mergeCells($merge_cells)
                ->setCellValue('A'.$row_no,$post['report_title'])
                ->getStyle($merge_cells)
                ->getAlignment()
                ->applyFromArray($style)
            ;


            // Set the Filter in Excell
            $strFilter = implode(' AND ', $arrFilters);
            $strFilter = $strFilter != ''?$strFilter:$lang['no_filter'];
            //d(array($lang['no_filter'], $strFilter, $lang));
            $row_no++;
            $merge_cells = $this->cellsToMergeByColsRow($start_column_idx, $end_column_idx, $row_no);
            //d(array($start_column_idx, $no_of_columns, $end_column_idx, $merge_cells), true);
            $objPHPExcel->getActiveSheet()
                ->mergeCells($merge_cells)
                ->setCellValue('A'.$row_no,'Filter: ' . $strFilter)
            ;


            // Set The Header Column
            $column_no = 0;
            $row_no++;
            foreach($columns as $column) {
                $objPHPExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($column_no, $row_no, $column)
                    ->getStyleByColumnAndRow($column_no, $row_no)
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '000000')
                            )
                        ,
                            'font'  => array(
                                'color' => array('rgb' => 'ffffff')
                            )
                        )
                    )
                ;

                $column_no++;
            }


            // Set The Rows
            foreach($rows as $variable_name => $row) {
                $column_no = 0;
                $row_no++;
                foreach($row as $header_row => $value) {
                    $objPHPExcel->getActiveSheet()
                        ->setCellValueByColumnAndRow($column_no, $row_no, $value)
                        ->getStyleByColumnAndRow($column_no, $row_no)
                        ->applyFromArray(
                            array(
//                                'fill' => array(
//                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
//                                    'color' => array('rgb' => 'D8D8D8')
//                                )
//                            ,
                                'font'  => array(
                                    'color' => array('rgb' => '000000')
                                )
                            )
                        )
                    ;

                    $column_no++;
                }
            }

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            ob_end_clean();
            $file_name = $post['report_title'] .'_' . date('YmdHis');
            //header('Content-type: application/vnd.ms-excel');
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

            // It will be called file.xls
            header('Content-Disposition: attachment; filename="'.$file_name.'.xlsx"');

            // Write file to the browser
            $objWriter->save('php://output');
            //$objWriter->save($file_name);
        } catch(Exception $e) {
            d($e, true);
        }

    }

    private function cellsToMergeByColsRow($start = -1, $end = -1, $row = -1){
        $merge = 'A1:A1';
        if($start>=0 && $end>=0 && $row>=0){
            $start = PHPExcel_Cell::stringFromColumnIndex($start);
            $end = PHPExcel_Cell::stringFromColumnIndex($end);
            $merge = "$start{$row}:$end{$row}";
        }
        return $merge;
    }

    public function saveReport() {
        $post = $this->request->post;
        $this->model['report_criteria'] = $this->load->model('report/criteria');
        $user_id = $this->session->data['user_id'];
        $report_id = strtolower($post['report_title']);
        $report_title = $post['report_title'];

        $report = $this->model['report_criteria']->getRow(array('report_name' => 'activity_schedule', 'user_id' => $user_id, 'report_id' => $report_id));
        if(empty($report)) {
            $insertData = array(
                'report_name' => 'activity_schedule',
                'user_id' => $user_id,
                'report_id' => $report_id,
                'report_title' => $report_title,
                'data' => serialize($post)
            );

            $report_criteria_id = $this->model['report_criteria']->add($this->getAlias(), $insertData);
        } else {
            $report_criteria_id = $report['report_criteria_id'];
            $updateData = array(
                'report_name' => 'activity_schedule',
                'user_id' => $user_id,
                'report_id' => $report_id,
                'report_title' => $report_title,
                'data' => serialize($post)
            );

            $this->model['report_criteria']->edit($this->getAlias(), $report_criteria_id, $updateData);
        }

        $reports = array();
        $rows = $this->model['report_criteria']->getRows(array('report_name' => 'activity_schedule', 'user_id' => $user_id),array('created_at DESC'));
        foreach($rows as $row) {
            $reports[] = array(
                'report_criteria_id' => $row['report_criteria_id'],
                'report_id' => $row['report_id'],
                'report_title' => $row['report_title']
            );
        }

        $json = array (
            'success' => true,
            //'post' => $post,
            'report_criteria_id' => $report_criteria_id,
            'reports' => $reports
        );

        echo json_encode($json);
        exit;
    }

    public function removeReport() {
        $post = $this->request->post;
        $report_criteria_id = $post['report_criteria_id'];
        $this->model['report_criteria'] = $this->load->model('report/criteria');
        $this->model['report_criteria']->delete($this->getAlias(), $report_criteria_id);

        $reports = array();
        $rows = $this->model['report_criteria']->getRows(array('report_name' => 'activity_schedule', 'user_id' => $this->session->data['user_id']),array('created_at DESC'));
        foreach($rows as $row) {
            $reports[] = array(
                'report_criteria_id' => $row['report_criteria_id'],
                'report_id' => $row['report_id'],
                'report_title' => $row['report_title']
            );
        }
        $json = array(
            'success' => true,
            //'post' => $post,
            'report_criteria_id' => $report_criteria_id,
            'reports' => $reports
        );

        echo json_encode($json);
        exit;
    }

    public function loadReport() {
        $post = $this->request->post;
        $this->model['report_criteria'] = $this->load->model('report/criteria');
        $report = $this->model['report_criteria']->getRow(array('report_criteria_id' => $post['report_criteria_id']));
        $data = unserialize($report['data']);
        if($data['building_id'] != '') {
            $this->model['building'] = $this->load->model('setup/building');
            $buildings = $this->model['building']->getRows();
            $html = '<option value="">ALL</option>';
            foreach($buildings as $building) {
                $html .= '<option value="'.$building['building_id'].'" '.($building['building_id']==$data['building_id']?'selected="true"':'').'>'.$building['name'].'</option>';
            }
            $data['building'] = $html;
        }
        if($data['location_id'] != '' || $data['building_id'] != '') {
            $this->model['location'] = $this->load->model('setup/location');
            $locations = $this->model['location']->getRows(array('building_id' => $data['building_id']));
            $html = '<option value="">ALL</option>';
            foreach($locations as $location) {
                $html .= '<option value="'.$location['location_id'].'" '.($location['location_id']==$data['location_id']?'selected="true"':'').'>'.$location['name'].'</option>';
            }
            $data['location'] = $html;
        }
        if($data['floor_id'] != '' || $data['location_id'] != '') {
            $this->model['floor'] = $this->load->model('setup/floor');
            $floors = $this->model['floor']->getRows(array('location_id' => $data['location_id']));
            $html = '<option value="">ALL</option>';
            foreach($floors as $floor) {
                $html .= '<option value="'.$floor['floor_id'].'" '.($floor['floor_id']==$data['floor_id']?'selected="true"':'').'>'.$floor['name'].'</option>';
            }
            $data['floor'] = $html;
        }

        $json = array(
            'success' => true,
            'post' => $post,
            'report' => $report,
            'data' => $data
        );

        echo json_encode($json);
        exit;
    }

}

class PDF extends TCPDF {

    public $post_data;
    public $header_data;
    public $footer_data;
    //Page header
    public function Header() {
//        // Logo
//        //$image_file = DIR_IMAGE.'logo.jpg';
//        $image_file = DIR_IMAGE.'no_image.jpg';
//        $this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
//        // Set font
//        $this->SetFont('helvetica', 'B', 20);
//        // Title
//        $this->Cell(0, 10, 'ANB', 0, false, 'C', 0, '', 0, false, 'M', 'M');
//        $this->Ln(10);
//        $this->Cell(0, 10, 'Cash Register', 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->writeHTML($this->header_data);

    }

    // Page footer
    public function Footer() {
        // Position at 15 mm from bottom
        $this->SetY(-15);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
//        $this->writeHTML($this->footer_data);
    }
}
?>