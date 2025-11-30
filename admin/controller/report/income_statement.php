<?php

class ControllerReportIncomeStatement extends HController {

    protected function getAlias() {
        return 'report/income_statement';
    }

    protected function getDefaultOrder() {
        return 'cao_level1_id';
    }

    protected function getDefaultSort() {
        return 'ASC';
    }

    protected function getList() {
        parent::getList();

        $this->data['date_from'] = stdDate($this->session->data['fiscal_date_from']);
        $this->data['date_to'] = stdDate();
        $this->data['href_print_report'] = $this->url->link($this->getAlias() .'/printReport', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_print_excel'] = $this->url->link($this->getAlias() .'/printReportExcel', 'token=' . $this->session->data['token'], 'SSL');

        // $this->model['project'] = $this->load->model('setup/project');
        // $this->data['projects'] = $this->model['project']->getRows(array(
        //     'company_id' => $this->session->data['company_id'], 
        //     'company_branch_id' => $this->session->data['company_branch_id']
        // ), array('name'));

        // $this->model['job_order'] = $this->load->model('production/job_order');
        // $this->data['job_orders'] = $this->model['job_order']->getRows(array(
        //     'company_id' => $this->session->data['company_id'], 
        //     'company_branch_id' => $this->session->data['company_branch_id'],
        //     'fiscal_year_id' => $this->session->data['fiscal_year_id']
        // ), array('document_identity'));

        // $this->data['href_get_sub_projects'] = $this->url->link($this->getAlias() .'/getSubProjects', 'token=' . $this->session->data['token'], 'SSL');
        // // $this->data['href_get_job_orders'] = $this->url->link($this->getAlias() .'/getJobOrders', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_validate_date'] = $this->url->link('common/function/validateDate', 'token=' . $this->session->data['token']);

        $this->data['strValidation'] = "{
            'rules': {
                'date_from': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
                'date_to': {'required': true, 'remote':  {url: '" . $this->data['action_validate_date'] . "', type: 'post'}},
            },
        }";

        $this->template = $this->getAlias() . '.tpl';
        $this->response->setOutput($this->render());
    }

    // public function getSubProjects() {
    //     $project_id = $this->request->post['project_id'];
    //     $this->model['sub_project'] = $this->load->model('setup/sub_project');
    //     $rows = $this->model['sub_project']->getRows(array('company_id' => $this->session->data['company_id'], 'project_id' => $project_id), array('name'));

    //     $html = "";
    //     $html .= '<option value="">&nbsp;</option>';
    //     foreach($rows as $row) {
    //         $html .= '<option value="'.$row['sub_project_id'].'">'.$row['name'].'</option>';
    //     }

    //     $json = array('success' => true, 'html' => $html);
    //     echo json_encode($json);
    // }

   
    public function printReportExcel(){
        $this->init();

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $arrFilter['from_date'] = $post['date_from'];
        $arrFilter['to_date'] = $post['date_to'];

        $this->model['company']=$this->load->model('setup/company');
        $this->model['setting']=$this->load->model('common/setting');
        $company=$this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->model['image'] = $this->load->model('tool/image');
        $company_image = $this->model['image']->resize($company['company_logo'],100,100);
        //d($company_image,true);
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch=$this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id']));        

        $filterArr = [];
        $filterArr[] = "`company_id` = '{$this->session->data['company_id']}'";
        $filterArr[] = "`company_branch_id` = '{$this->session->data['company_branch_id']}'";
        $filterArr[] = "`fiscal_year_id` = '{$this->session->data['fiscal_year_id']}'";
        $filterArr[] = "`module` = 'profit_and_loss'";
        $filterArr = implode(' AND ', $filterArr);
        $profit_and_loss = $this->model['setting']->getRows($filterArr);

        $account_ids = [];
        foreach ($profit_and_loss as $account) {
            if($account['field']=='sale_revenue_account_id'){
                $account_ids['sale_revenue_account_id'][] = $account['value'];
            } else if($account['field']=='sale_return_and_discount_account_id'){
                $account_ids['sale_return_and_discount_account_id'][] = $account['value'];
            } else if($account['field']=='cogs_account_id'){
                $account_ids['cogs_account_id'][] = $account['value'];
            } else if($account['field']=='admin_expense_account_id'){
                $account_ids['admin_expense_account_id'][] = $account['value'];
            } else if($account['field']=='financial_charges_account_id'){
                $account_ids['financial_charges_account_id'][] = $account['value'];
            } else if($account['field']=='sale_marketing_account_id'){
                $account_ids['sale_marketing_account_id'][] = $account['value'];
            } else if($account['field']=='non_operating_income_account_id'){
                $account_ids['non_operating_income_account_id'][] = $account['value'];
            } else if($account['field']=='tax_paid_account_id'){
                $account_ids['tax_paid_account_id'][] = $account['value'];
            }
        }

        $accountHeadings = [];
        
        $accountHeadings['sale_revenue_account'] = array(
            'title' => 'Sales Revenue:',
            'total' => 'Total Sales'
        );
        
        $accountHeadings['sale_return_and_discount_account'] = array(
            'title' => 'Less: Sale Return & Discount:',
            'total' => 'Total Sale Return & Discount'
        );
        
        $accountHeadings['net_sales'] = 'Net Sales';

        $accountHeadings['cogs_account'] = array(
            'title' => 'Less: Cost of Goods Sold:',
            'total' => 'Total Cost of Goods Sold'
        );

        $accountHeadings['gross_profit_loss'] = 'Gross Profit / (Loss)';
        
        $accountHeadings['admin_expense_account'] = array(
            'title' => 'Less: Admin Expenses:',
            'total' => 'Total Admin Expenses'
        );


        $accountHeadings['sale_marketing_account'] = array(
            'title' => 'Less: Sales & Marketing Expenses:',
            'total' => 'Sales & Marketing Expense Total'
        );

        $accountHeadings['operating_income_loss']  = 'Operating Income / (Loss)';

        $accountHeadings['non_operating_income_account'] = array(
            'title' => 'Add: Non Operating Income:',
            'total' => 'Total Non Operating Income'
        );

        $accountHeadings['income_before_charges_and_tax'] = 'Income Before Charges and Taxes';

        $accountHeadings['financial_charges_account'] = array(
            'title' => 'Less: Financial Charges:',
            'total' => 'Financial Charges Total'
        );

        $accountHeadings['net_profit_loss_before_tax'] = 'Net Profit / (Loss) Before Taxes';

        $accountHeadings['tax_paid_account'] = array(
            'title' => 'Less: Income Tax Expenses:',
            'total' => 'Total Income Tax Expenses'
        );

        $accountHeadings['net_profit_after_tax'] = 'Net Profit / (Loss) After Tax';


        $this->model['income_statement'] = $this->load->model('report/income_statement');

        $where = [];
        $where['company_id'] = $this->session->data['company_id'];
        $where['company_branch_id'] = $this->session->data['company_branch_id'];
        $where['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $where['from_date'] = MySqlDate($this->request->post['date_from']);
        $where['to_date'] = MySqlDate($this->request->post['date_to']);

/*        $where['project_id'] = $post['project_id'];
        $where['sub_project_id'] = $post['sub_project_id'];
        $where['job_order_id'] = $post['job_order_id'];
*/
        $where['sale_revenue_account_id'] = "'".implode("','", $account_ids['sale_revenue_account_id'])."'";
        $where['sale_return_and_discount_account_id'] = "'".implode("','", $account_ids['sale_return_and_discount_account_id'])."'";
        $where['cogs_account_id'] = "'".implode("','", $account_ids['cogs_account_id'])."'";
        $where['admin_expense_account_id'] = "'".implode("','", $account_ids['admin_expense_account_id'])."'";
        $where['financial_charges_account_id'] = "'".implode("','", $account_ids['financial_charges_account_id'])."'";
        $where['sale_marketing_account_id'] = "'".implode("','", $account_ids['sale_marketing_account_id'])."'";
        $where['non_operating_income_account_id'] = "'".implode("','", $account_ids['non_operating_income_account_id'])."'";
        $where['tax_paid_account_id'] = "'".implode("','", $account_ids['tax_paid_account_id'])."'";

        $getSaleRevenue = $this->model['income_statement']->getSaleRevenue($where, $accountHeadings['sale_revenue_account']);
        $getSaleReturnAndDiscount = $this->model['income_statement']->getSaleReturnAndDiscount($where, $accountHeadings['sale_return_and_discount_account']);
        $getCOGS = $this->model['income_statement']->getCOGS($where, $accountHeadings['cogs_account']);
        $getAdminExpense = $this->model['income_statement']->getAdminExpense($where, $accountHeadings['admin_expense_account']);
        $getFinancialCharges = $this->model['income_statement']->getFinancialCharges($where, $accountHeadings['financial_charges_account']);
        $getSaleMarkiting = $this->model['income_statement']->getSaleMarkiting($where, $accountHeadings['sale_marketing_account']);
        $getNonOperatingIncome = $this->model['income_statement']->getNonOperatingIncome($where, $accountHeadings['non_operating_income_account']);
        $getTaxPaid = $this->model['income_statement']->getTaxPaid($where, $accountHeadings['tax_paid_account']);


        // Step 1
        $LedgerArr = [];
        foreach ($getSaleRevenue as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_revenue_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 2
        foreach ($getSaleReturnAndDiscount as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_return_and_discount_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 3
        $LedgerArr[$accountHeadings['net_sales']] = [
            'account_total' => $accountHeadings['net_sales'],
            'total' => ($LedgerArr[$accountHeadings['sale_revenue_account']['title']]['total']-$LedgerArr[$accountHeadings['sale_return_and_discount_account']['title']]['total']),
        ];
        

        // Step 4
        foreach ($getCOGS as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['cogs_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 5
        $LedgerArr[$accountHeadings['gross_profit_loss']] = [
            'account_total' => $accountHeadings['gross_profit_loss'],
            'total' => ($LedgerArr[$accountHeadings['net_sales']]['total']-$LedgerArr[$accountHeadings['cogs_account']['title']]['total']),
        ];

        // Step 6
        foreach ($getAdminExpense as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['admin_expense_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 7        
        foreach ($getSaleMarkiting as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_marketing_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 8
        $LedgerArr[$accountHeadings['operating_income_loss']] = [
            'account_total' => $accountHeadings['operating_income_loss'],
            'total' => ($LedgerArr[$accountHeadings['gross_profit_loss']]['total']-$LedgerArr[$accountHeadings['admin_expense_account']['title']]['total']-$LedgerArr[$accountHeadings['sale_marketing_account']['title']]['total']),
        ];


        // Step 9
        foreach ($getNonOperatingIncome as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['non_operating_income_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 10        
        $LedgerArr[$accountHeadings['income_before_charges_and_tax']] = [
            'account_total' => $accountHeadings['income_before_charges_and_tax'],
            'total' => ($LedgerArr[$accountHeadings['operating_income_loss']]['total']+$LedgerArr[$accountHeadings['non_operating_income_account']['title']]['total']),
        ];


        // Step 11        
        foreach ($getFinancialCharges as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['financial_charges_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 12        
        $LedgerArr[$accountHeadings['net_profit_loss_before_tax']] = [
            'account_total' => $accountHeadings['net_profit_loss_before_tax'],
            'total' => ($LedgerArr[$accountHeadings['income_before_charges_and_tax']]['total']-$LedgerArr[$accountHeadings['financial_charges_account']['title']]['total']),
        ];
        

        // Step 13        
        foreach ($getTaxPaid as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['tax_paid_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 14        
        $LedgerArr[$accountHeadings['net_profit_after_tax']] = [
            'account_total' => $accountHeadings['net_profit_after_tax'],
            'total' => ($LedgerArr[$accountHeadings['net_profit_loss_before_tax']]['total']-$LedgerArr[$accountHeadings['tax_paid_account']['title']]['total']),
        ];

        // d($LedgerArr, true);

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];

        $filter = array();
        
        // if( !empty($post['project_id']) ) {
        //     $filter['project_name'] = $post['project_name'];
        // }

        
        // if( !empty($post['sub_project_id']) ) {
        //     $filter['sub_project_name'] = $post['sub_project_name'];
        // }

        
        // if( !empty($post['job_order_id']) ) {
        //     $filter['job_order_no'] = $post['job_order_no'];
        // }

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getProperties()->setCreator("Muhammad Salman")->setLastModifiedBy("Muhammad Salman")->setTitle("Profit & Loss");

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);


        $rowCount = 1;

        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $session['company_name']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' =>array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'font' =>array(
                    'size' => 14,
                    'bold' => true
                ),
            )
        );

        $rowCount++;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, 'Profit & Loss');
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
            array(
                'alignment' =>array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'font' =>array(
                    'size' => 12,
                    'bold' => true
                ),
            )
        );

        // $rowCount++;
        // $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, 'Project: '. $filter['project_name'] .' | Sub Project: ' . $filter['project_name'] . ' | Job Order No. ' .  $filter['job_order_no']);

        $rowCount++;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, 'For the Peroid from '. $post['date_from'] .' to ' . $post['date_to']);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':B'.$rowCount)->applyFromArray(
            array(
                'alignment' =>array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                ),
                'font' =>array(
                    'size' => 10,
                    'bold' => true
                ),
                'borders' => array(
                    'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
                ),
            )
        );

        $rowCount+=2;
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, 'Amount (PKR)');
        $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->applyFromArray(
            array(
                'alignment' =>array(
                    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                ),
                'font' =>array(
                    'bold' => true
                ),
                'borders' => array(
                    'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
                ),
            )
        );

        $rowCount++;
        foreach ($LedgerArr as $type => $ledger) {
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $ledger['type']);
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, '');
            $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount)->applyFromArray(
                array(
                    'alignment' =>array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    ),
                    'font' =>array(
                        'bold' => true
                    ),
                )
            );
            $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->applyFromArray(
                array(
                    'alignment' =>array(
                        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    ),
                    'font' =>array(
                        'bold' => true
                    ),
                    'borders' => array(
                        'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
                    ),
                )
            );
            $rowCount++;
            foreach ($ledger['data'] as $detail) {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, "\t".$detail['level3_display_name']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, number_format($detail['amount'], 2, '.', false));
                $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->applyFromArray(
                    array(
                        'alignment' =>array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        ),
                        'borders' => array(
                            'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                            'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
                        ),
                    )
                );
                $rowCount++;
            }

            if(!empty($ledger['account_total'])){


                // ($rowCount-1) is liye dia hai ta k custom total kay uper ga gap kam kr skyn
                $objPHPExcel->getActiveSheet()->setCellValue('A'.($rowCount-1), $ledger['account_total']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.($rowCount-1), number_format($ledger['total'],2, '.', false));
                $objPHPExcel->getActiveSheet()->getStyle('A'.($rowCount-1).':B'.($rowCount-2))->applyFromArray(
                    array(
                        'alignment' =>array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        ),
                        'font' =>array(
                            'bold' => true
                        ),
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('B'.($rowCount-1))->applyFromArray(
                    array(
                        'alignment' =>array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        ),
                        'font' =>array(
                            'bold' => true
                        ),
                        'borders' => array(
                            'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                    )
                );
                $rowCount+=2;

            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('A'.$rowCount, $ledger['total_type']);
                $objPHPExcel->getActiveSheet()->setCellValue('B'.$rowCount, number_format($ledger['total'],2, '.', false));
                $objPHPExcel->getActiveSheet()->getStyle('A'.$rowCount.':B'.$rowCount)->applyFromArray(
                    array(
                        'alignment' =>array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                        ),
                        'font' =>array(
                            'bold' => true
                        ),
                    )
                );
                $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->applyFromArray(
                    array(
                        'alignment' =>array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        ),
                        'font' =>array(
                            'bold' => true
                        ),
                        'borders' => array(
                            'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
                        ),
                    )
                );
                $rowCount+=2;
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="profit_and_loss.xlsx"');
        header('Cache-Control: max-age=0');
        //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        // $objWriter->save('profit_and_loss.xlsx');
        $objWriter->save('php://output');
        exit;
    }

    public function printReport() {
        $this->init();

        $lang = $this->load->language($this->getAlias());
        $post = $this->request->post;
        $session = $this->session->data;
        $arrFilter['from_date'] = $post['date_from'];
        $arrFilter['to_date'] = $post['date_to'];

        $this->model['company']=$this->load->model('setup/company');
        $this->model['setting']=$this->load->model('common/setting');
        $company=$this->model['company']->getRow(array('company_id' => $this->session->data['company_id']));

        $this->model['image'] = $this->load->model('tool/image');
        $company_image = $this->model['image']->resize($company['company_logo'],100,100);
        //d($company_image,true);
        $this->model['company_branch'] = $this->load->model('setup/company_branch');
        $company_branch=$this->model['company_branch']->getRow(array('company_id' => $this->session->data['company_id']));

        
        $filterArr = [];
        $filterArr[] = "`company_id` = '{$this->session->data['company_id']}'";
        $filterArr[] = "`company_branch_id` = '{$this->session->data['company_branch_id']}'";
        $filterArr[] = "`fiscal_year_id` = '{$this->session->data['fiscal_year_id']}'";
        $filterArr[] = "`module` = 'profit_and_loss'";
        $filterArr = implode(' AND ', $filterArr);
        $profit_and_loss = $this->model['setting']->getRows($filterArr);

        $account_ids = [];
        foreach ($profit_and_loss as $account) {
            if($account['field']=='sale_revenue_account_id'){
                $account_ids['sale_revenue_account_id'][] = $account['value'];
            } else if($account['field']=='sale_return_and_discount_account_id'){
                $account_ids['sale_return_and_discount_account_id'][] = $account['value'];
            } else if($account['field']=='cogs_account_id'){
                $account_ids['cogs_account_id'][] = $account['value'];
            } else if($account['field']=='admin_expense_account_id'){
                $account_ids['admin_expense_account_id'][] = $account['value'];
            } else if($account['field']=='financial_charges_account_id'){
                $account_ids['financial_charges_account_id'][] = $account['value'];
            } else if($account['field']=='sale_marketing_account_id'){
                $account_ids['sale_marketing_account_id'][] = $account['value'];
            } else if($account['field']=='non_operating_income_account_id'){
                $account_ids['non_operating_income_account_id'][] = $account['value'];
            } else if($account['field']=='tax_paid_account_id'){
                $account_ids['tax_paid_account_id'][] = $account['value'];
            }
        }

        $accountHeadings = [];
        
        $accountHeadings['sale_revenue_account'] = array(
            'title' => 'Sales Revenue:',
            'total' => 'Total Sales'
        );
        
        $accountHeadings['sale_return_and_discount_account'] = array(
            'title' => 'Less: Sale Return & Discount:',
            'total' => 'Total Sale Return & Discount'
        );
        
        $accountHeadings['net_sales'] = 'Net Sales';

        $accountHeadings['cogs_account'] = array(
            'title' => 'Less: Cost of Goods Sold:',
            'total' => 'Total Cost of Goods Sold'
        );

        $accountHeadings['gross_profit_loss'] = 'Gross Profit / (Loss)';
        
        $accountHeadings['admin_expense_account'] = array(
            'title' => 'Less: Admin Expenses:',
            'total' => 'Total Admin Expenses'
        );


        $accountHeadings['sale_marketing_account'] = array(
            'title' => 'Less: Sales & Marketing Expenses:',
            'total' => 'Sales & Marketing Expense Total'
        );

        $accountHeadings['operating_income_loss']  = 'Operating Income / (Loss)';

        $accountHeadings['non_operating_income_account'] = array(
            'title' => 'Add: Non Operating Income:',
            'total' => 'Total Non Operating Income'
        );

        $accountHeadings['income_before_charges_and_tax'] = 'Income Before Charges and Taxes';

        $accountHeadings['financial_charges_account'] = array(
            'title' => 'Less: Financial Charges:',
            'total' => 'Financial Charges Total'
        );

        $accountHeadings['net_profit_loss_before_tax'] = 'Net Profit / (Loss) Before Taxes';

        $accountHeadings['tax_paid_account'] = array(
            'title' => 'Less: Income Tax Expenses:',
            'total' => 'Total Income Tax Expenses'
        );

        $accountHeadings['net_profit_after_tax'] = 'Net Profit / (Loss) After Tax';


        $this->model['income_statement'] = $this->load->model('report/income_statement');

        $where = [];
        $where['company_id'] = $this->session->data['company_id'];
        $where['company_branch_id'] = $this->session->data['company_branch_id'];
        $where['fiscal_year_id'] = $this->session->data['fiscal_year_id'];
        $where['from_date'] = MySqlDate($this->request->post['date_from']);
        $where['to_date'] = MySqlDate($this->request->post['date_to']);
        

        // $where['project_id'] = $post['project_id'];
        // $where['sub_project_id'] = $post['sub_project_id'];
        // $where['job_order_id'] = $post['job_order_id'];

        $where['sale_revenue_account_id'] = "'".implode("','", $account_ids['sale_revenue_account_id'])."'";
        $where['sale_return_and_discount_account_id'] = "'".implode("','", $account_ids['sale_return_and_discount_account_id'])."'";
        $where['cogs_account_id'] = "'".implode("','", $account_ids['cogs_account_id'])."'";
        $where['admin_expense_account_id'] = "'".implode("','", $account_ids['admin_expense_account_id'])."'";
        $where['financial_charges_account_id'] = "'".implode("','", $account_ids['financial_charges_account_id'])."'";
        $where['sale_marketing_account_id'] = "'".implode("','", $account_ids['sale_marketing_account_id'])."'";
        $where['non_operating_income_account_id'] = "'".implode("','", $account_ids['non_operating_income_account_id'])."'";
        $where['tax_paid_account_id'] = "'".implode("','", $account_ids['tax_paid_account_id'])."'";

        $getSaleRevenue = $this->model['income_statement']->getSaleRevenue($where, $accountHeadings['sale_revenue_account']);
        $getSaleReturnAndDiscount = $this->model['income_statement']->getSaleReturnAndDiscount($where, $accountHeadings['sale_return_and_discount_account']);
        $getCOGS = $this->model['income_statement']->getCOGS($where, $accountHeadings['cogs_account']);
        $getAdminExpense = $this->model['income_statement']->getAdminExpense($where, $accountHeadings['admin_expense_account']);
        $getFinancialCharges = $this->model['income_statement']->getFinancialCharges($where, $accountHeadings['financial_charges_account']);
        $getSaleMarkiting = $this->model['income_statement']->getSaleMarkiting($where, $accountHeadings['sale_marketing_account']);
        $getNonOperatingIncome = $this->model['income_statement']->getNonOperatingIncome($where, $accountHeadings['non_operating_income_account']);
        $getTaxPaid = $this->model['income_statement']->getTaxPaid($where, $accountHeadings['tax_paid_account']);


        // Step 1
        $LedgerArr = [];
        foreach ($getSaleRevenue as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_revenue_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 2
        foreach ($getSaleReturnAndDiscount as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_return_and_discount_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 3
        $LedgerArr[$accountHeadings['net_sales']] = [
            'account_total' => $accountHeadings['net_sales'],
            'total' => ($LedgerArr[$accountHeadings['sale_revenue_account']['title']]['total']-$LedgerArr[$accountHeadings['sale_return_and_discount_account']['title']]['total']),
        ];
        

        // Step 4
        foreach ($getCOGS as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['cogs_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 5
        $LedgerArr[$accountHeadings['gross_profit_loss']] = [
            'account_total' => $accountHeadings['gross_profit_loss'],
            'total' => ($LedgerArr[$accountHeadings['net_sales']]['total']-$LedgerArr[$accountHeadings['cogs_account']['title']]['total']),
        ];

        // Step 6
        foreach ($getAdminExpense as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['admin_expense_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 7        
        foreach ($getSaleMarkiting as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['sale_marketing_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 8
        $LedgerArr[$accountHeadings['operating_income_loss']] = [
            'account_total' => $accountHeadings['operating_income_loss'],
            'total' => ($LedgerArr[$accountHeadings['gross_profit_loss']]['total']-$LedgerArr[$accountHeadings['admin_expense_account']['title']]['total']-$LedgerArr[$accountHeadings['sale_marketing_account']['title']]['total']),
        ];


        // Step 9
        foreach ($getNonOperatingIncome as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['non_operating_income_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }


        // Step 10        
        $LedgerArr[$accountHeadings['income_before_charges_and_tax']] = [
            'account_total' => $accountHeadings['income_before_charges_and_tax'],
            'total' => ($LedgerArr[$accountHeadings['operating_income_loss']]['total']+$LedgerArr[$accountHeadings['non_operating_income_account']['title']]['total']),
        ];


        // Step 11        
        foreach ($getFinancialCharges as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['financial_charges_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 12        
        $LedgerArr[$accountHeadings['net_profit_loss_before_tax']] = [
            'account_total' => $accountHeadings['net_profit_loss_before_tax'],
            'total' => ($LedgerArr[$accountHeadings['income_before_charges_and_tax']]['total']-$LedgerArr[$accountHeadings['financial_charges_account']['title']]['total']),
        ];
        

        // Step 13        
        foreach ($getTaxPaid as $detail) {
            if(!isset($LedgerArr[$detail['type']])) {
                $LedgerArr[$detail['type']] = array(
                    'type' => $detail['type'],
                    'total_type' => $accountHeadings['tax_paid_account']['total'],
                    'total' => 0,
                    'data' => array()
                );
            }
            $LedgerArr[$detail['type']]['data'][] = array(
                'level3_display_name' => $detail['level3_display_name'],
                'amount' => $detail['balance'],
            );
            $LedgerArr[$detail['type']]['total'] += $detail['balance'];
        }

        // Step 14        
        $LedgerArr[$accountHeadings['net_profit_after_tax']] = [
            'account_total' => $accountHeadings['net_profit_after_tax'],
            'total' => ($LedgerArr[$accountHeadings['net_profit_loss_before_tax']]['total']-$LedgerArr[$accountHeadings['tax_paid_account']['title']]['total']),
        ];

        // d($LedgerArr, true);

        $this->model['image'] = $this->load->model('tool/image');
        $this->model['setting'] = $this->load->model('common/setting');
        $setting = $this->model['setting']->getRow(array(
            'company_id' => $this->session->data['company_id'],
            'company_branch_id' => $this->session->data['company_branch_id'],
            'fiscal_year_id' => $this->session->data['fiscal_year_id'],
            'module' => 'general',
            'field' => 'company_logo',
        ));
        $company_logo = $setting['value'];

        $filter = array();
        
        // if( !empty($post['project_id']) ) {
        //     $filter['project_name'] = $post['project_name'];
        // }

        
        // if( !empty($post['sub_project_id']) ) {
        //     $filter['sub_project_name'] = $post['sub_project_name'];
        // }

        
        // if( !empty($post['job_order_id']) ) {
        //     $filter['job_order_no'] = $post['job_order_no'];
        // }

        //d($arrLedger, true);
        $pdf = new PDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Muhammad Salman');
        $pdf->SetTitle('Profit & Loss');
        $pdf->SetSubject('Profit & Loss');

        //Set Header
        $pdf->data = array(
            'company_name' => $session['company_name'],
            'report_name' => $lang['heading_title'],
            'company_logo' => $company_logo,
            'date_from' => $post['date_from'],
            'date_to' => $post['date_to'],
            'filter' => $filter
        );

        // set margins
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(10, 45, 10);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set font
        // add a page
        $pdf->AddPage();

        foreach ($LedgerArr as $type => $ledger) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(155, 7, $ledger['type'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $pdf->Cell(35, 7, '', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
            $pdf->Ln(7);
            $pdf->SetFont('helvetica', '', 10);
            foreach ($ledger['data'] as $detail) {
                $pdf->Cell(10, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(145, 7, $detail['level3_display_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(35, 7, number_format($detail['amount'], 2), 'LR', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(7);
            }
            $pdf->SetFont('helvetica', 'B', 10);
            if(!empty($ledger['account_total'])){
                $pdf->Ln(-14);
                $pdf->Ln(7);
                $pdf->Cell(155, 7, $ledger['account_total'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(35, 7, number_format($ledger['total'],2), 'T', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(14);
            } else {
                $pdf->Cell(155, 7, $ledger['total_type'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
                $pdf->Cell(35, 7, number_format($ledger['total'],2), 'T', false, 'R', 0, '', 0, false, 'M', 'M');
                $pdf->Ln(7);
            }
        }

        //Close and output PDF document
        $pdf->Output('Profit & Loss:'.date('YmdHis').'.pdf', 'I');
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
        $this->Ln(2);
        // Title
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 7, $this->data['company_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(7);
        $this->Cell(0, 7, $this->data['report_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(7);
        $this->SetFont('times', 'B', 10);
        $this->Cell(0, 7, 'From Date: ' . $this->data['date_from'] . ' To Date: ' . $this->data['date_to'], 0, false, 'C', 0, '', 0, false, 'M', 'M');

        $this->Ln(7);

        // $this->SetFont('helvetica', 'B', 8);
        // $this->Cell(63.3, 7, 'Project: ' . $this->data['filter']['project_name'], 0, false, 'L', 0, '', 0, false, 'M', 'M');
        // $this->Cell(63.3, 7, 'Sub Project: ' . $this->data['filter']['sub_project_name'], 0, false, 'C', 0, '', 0, false, 'M', 'M');
        // $this->Cell(63.3, 7, 'Job Order No. ' . $this->data['filter']['job_order_no'], 0, false, 'R', 0, '', 0, false, 'M', 'M');

        // $this->Ln(1);
        $this->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $this->Cell(190, 1, '', 'B', false, 'L', 0, '', 0, false, 'M', 'M');

        $this->Ln(10);
        $this->SetLineStyle(array('width' => .5, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $this->SetFont('helvetica', 'B', 10);
        $this->Cell(155, 7, '', 0, false, 'L', 0, '', 0, false, 'M', 'M');
        $this->Cell(35, 7, 'Amount (PKR)', 'B', false, 'C', 0, '', 0, false, 'M', 'M');
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