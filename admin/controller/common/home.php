<?php

class ControllerCommonHome extends Controller {

    protected function getAlias() {
        return 'common/home';
    }

    public function index() {

        $this->data['lang'] = $this->load->language('common/home');
        $this->model['image'] = $this->load->model('tool/image');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['heading_title'] = $this->language->get('heading_title');
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $this->data['base'] = HTTPS_BASE;
        } else {
            $this->data['base'] = HTTP_BASE;
        }

//        $pdf = new Pdf('http://jkorpela.fi/forms/file.html');
////        // Save the PDF
////        if (!$pdf->saveAs('report.pdf')) {
////            $error = $pdf->getError();
////            // ... handle error here
////        }
//
//// On some systems you may have to set the path to the wkhtmltopdf executable
//// $pdf->binary = 'C:\...';
//
//        if (!$pdf->saveAs('product_sub_category.pdf')) {
//            $error = $pdf->getError();
//            // ... handle error here
//        }
        if (isset($this->session->data['error'])) {
            $this->data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->session->data['warning'])) {
            $this->data['error_warning'] = $this->session->data['warning'];

            unset($this->session->data['warning']);
        } elseif (isset($this->session->data['error_warning'])) {
            $this->data['error_warning'] = $this->session->data['error_warning'];

            unset($this->session->data['error_warning']);
        } else {
            $this->data['error_warning'] = '';
        }


        //Url Graph //

        $this->data['href_get_sale_month_chart'] = $this->url->link('common/function/getSaleMonthChart', 'token=' . $this->session->data['token'] . '&case_id=' . $this->request->get['case_id']);
        $this->data['href_get_top_5_customers'] = $this->url->link('common/function/get_top_5_customers', 'token=' . $this->session->data['token'] . '&case_id=' . $this->request->get['case_id']);

        // Url Print Report //

        $this->data['ledger_report'] = $this->url->link('report/ledger_report', '&token=' . $this->session->data['token']);
        $this->data['party_ledger_report'] = $this->url->link('report/party_ledger', '&token=' . $this->session->data['token']);
        $this->data['outstanding_report'] = $this->url->link('report/outstanding_report', '&token=' . $this->session->data['token']);
        $this->data['sale_tax_report'] = $this->url->link('report/sale_tax_report', '&token=' . $this->session->data['token']);

            // End Work

            // Url Add Inventory //

        $this->data['inventory_customer'] = $this->url->link('setup/customer', '&token=' . $this->session->data['token']);
        $this->data['inventory_product_category'] = $this->url->link('inventory/product_category', '&token=' . $this->session->data['token']);
        $this->data['inventory_product'] = $this->url->link('inventory/product', '&token=' . $this->session->data['token']);
        $this->data['inventory_quotation'] = $this->url->link('inventory/quotation', '&token=' . $this->session->data['token']);


        $this->data['inventory_sale_order'] = $this->url->link('inventory/sale_order1', '&token=' . $this->session->data['token']);
        $this->data['inventory_purchase_invoice'] = $this->url->link('inventory/purchase_invoice', '&token=' . $this->session->data['token']);
        $this->data['inventory_delivery_challan'] = $this->url->link('inventory/delivery_challan', '&token=' . $this->session->data['token']);
        $this->data['inventory_sale_invoice'] = $this->url->link('inventory/sale_invoice', '&token=' . $this->session->data['token']);
        $this->data['inventory_sale_tax_invoice'] = $this->url->link('inventory/sale_tax_invoice', '&token=' . $this->session->data['token']);

            // End Work

                // Total Record //

                  // Total Customer
                $this->model['customer'] = $this->load->model('setup/customer');
                $total_customer = $this->model['customer']->getTotalCustomer();
                $this->data['total_customers'] = $total_customer['total_customer'];

                // Total Product //
                $this->model['product'] = $this->load->model('inventory/product');
                $total_product = $this->model['product']->getTotalProduct();
                $this->data['total_products'] = $total_product['total_product'];

                // Total Product Category //
                $this->model['product_category'] = $this->load->model('inventory/product_category');
                $total_product_category = $this->model['product_category']->getTotalProductCategory();
                $this->data['total_product_category'] = $total_product_category['total_product_category'];

                // Total Non Gst DC //
                $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
                $total_delivery_challan = $this->model['delivery_challan_detail']->getTotalPendingDCNonGst();
                $this->data['total_pending_dc'] = $total_delivery_challan['total_pending_dc'];

                // Total Gst DC //
                $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
                $total_delivery_challan = $this->model['delivery_challan_detail']->getTotalPendingDCGst();
                $this->data['total_pending_gst_dc'] = $total_delivery_challan['total_pending_dc'];


                    // End //

                // Sale Order //
                $this->model['sale_order'] = $this->load->model('inventory/sale_order');
                $sale_order_details = $this->model['sale_order']->getLatestSaleOrders();

                    foreach($sale_order_details as $row_id => $detail) {

                        $detail['href'] = $this->url->link('inventory/sale_order1'.'/update', 'token=' . $this->session->data['token'] . '&' . 'sale_order_id' . '=' . $detail['sale_order_id'], 'SSL');
                        $this->data['sale_order_details'][$row_id] = $detail;

                    }

                    $this->data['new_sale_order'] = $this->url->link('inventory/sale_order1'.'/insert', 'token=' . $this->session->data['token']);
                    $this->data['all_sale_order'] = $this->url->link('inventory/sale_order1'.'&token=' . $this->session->data['token'] );




                // Challan //
                $this->model['delivery_challan_detail'] = $this->load->model('inventory/delivery_challan_detail');
                $delivery_challan_details = $this->model['delivery_challan_detail']->getLatestChallans();

                    foreach($delivery_challan_details as $row_id => $detail) {

                        $detail['href'] = $this->url->link('inventory/delivery_challan'.'/update', 'token=' . $this->session->data['token'] . '&' . 'delivery_challan_id' . '=' . $detail['delivery_challan_id'], 'SSL');
                        $this->data['challan_details'][$row_id] = $detail;

                    }

                    $this->data['new_challan'] = $this->url->link('inventory/delivery_challan'.'/insert', 'token=' . $this->session->data['token']);
                    $this->data['all_challan'] = $this->url->link('inventory/delivery_challan'.'&token=' . $this->session->data['token'] );


                // Bank Receipt //
                $this->model['bank_receipt'] = $this->load->model('gl/bank_receipt');
                $bank_receipts = $this->model['bank_receipt']->getLatestReceipts();

                    foreach($bank_receipts as $row_id => $detail) {

                        $detail['href'] = $this->url->link('gl/bank_receipt'.'/update', 'token=' . $this->session->data['token'] . '&' . 'bank_receipt_id' . '=' . $detail['bank_receipt_id'], 'SSL');
                        $this->data['bank_receipts'][$row_id] = $detail;

                    }

                    $this->data['new_receipt'] = $this->url->link('gl/bank_receipt'.'/insert', 'token=' . $this->session->data['token']);
                    $this->data['all_receipt'] = $this->url->link('gl/bank_receipt'.'&token=' . $this->session->data['token'] );


            $party_ledger = 'party_ledger.png';
        $ledger = 'ledger.png';
        $this->data['party_ledger'] = $this->model['image']->resize($party_ledger,50,50);
        $this->data['ledger'] = $this->model['image']->resize($ledger,70,70);
//        d($this->data['party_ledger'],true);

        $this->data['permission_id'] = $this->session->data['user_permission'];
        //d($this->data['permission_id'],true);

        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['lang']['dashboard'],
            'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'class' => 'fa fa-dashboard',
            'separator' => false
        );

        $this->data['token'] = $this->session->data['token'];


//        $this->model['company'] = $this->load->model('setup/company');
//        $this->data['companies'] = $this->model['company']->getRows(array('status' => 'Active'));
//
//        $this->data['action_get_customer'] = $this->url->link($this->getAlias() . '/getCustomer', 'token=' . $this->session->data['token'], 'SSL');
//        $this->data['action_get_sales'] = $this->url->link($this->getAlias() . '/getSale', 'token=' . $this->session->data['token'], 'SSL');
//        $this->data['action_load_widget_graphs'] = $this->url->link($this->getAlias() . '/loadWidgetGraphs', 'token=' . $this->session->data['token'], 'SSL');
//        $this->data['action_update_widget_graphs'] = $this->url->link($this->getAlias() . '/updateWidgetGraphs', 'token=' . $this->session->data['token'], 'SSL');

        $this->template = 'common/home.tpl';
        $this->children = array(
            'common/header',
            'common/column_left',
            'common/column_right',
            'common/page_header',
            'common/page_footer',
            'common/footer',
        );

        $this->response->setOutput($this->render());
    }

    public function login() {
        if(isset($this->session->data['user_id']) && $this->session->data['user_id']) {
            $this->model['user'] = $this->load->model('user/user');
            $user = $this->model['user']->getRow(array('user_id' => $this->session->data['user_id']));
            if ($user) {
                $this->model['user_permission'] = $this->load->model('user/user_permission');
                $permissions = $this->model['user_permission']->getRow(array('user_permission_id' => $user['user_permission_id']));
                $data = $user;
                $data['permissions'] = unserialize($permissions['permission']);

                $this->user->set($data);
            } else {
                return $this->forward('common/logout');
            }

        }

        $route = '';

        if (isset($this->request->get['route'])) {
            $part = explode('/', $this->request->get['route']);

            if (isset($part[0])) {
                $route .= $part[0];
            }

            if (isset($part[1])) {
                $route .= '/' . $part[1];
            }
        }

        $ignore = array(
            'common/login',
            'common/preset',
            'common/forgotten',
            'common/filemanager',
            'common/reset'
        );

        //d(array($this->user->getData(), $this->session->data), true);
        if ((!$this->user->isLogged()) && !in_array($route, $ignore)) {
            return $this->forward('common/logout');
        }

        $user_restricted_ip = $this->user->getIP();
        if($user_restricted_ip &&  $user_restricted_ip != $this->request->server['REMOTE_ADDR']) {
            return $this->forward('common/logout');
        }
    }

    public function permission() {
//        $sk = $this->config->get('config_security_key');
//        if (!$sk) {
//            return $this->forward('error/security');
//        } else {
//            $arrSK = unserialize(base64_decode($sk));
//            if ($arrSK['server'] == $this->request->server['HTTP_HOST']) {
//                if ($arrSK['expiry_date'] != "") {
//                    if ($arrSK['expiry_date'] < date('Y-m-d')) {
//                        return $this->forward('error/security');
//                    }
//                }
//            } else {
//                return $this->forward('error/security');
//            }
//        }

        if (isset($this->request->get['route'])) {
            $route = '';

            $part = explode('/', $this->request->get['route']);

            if (isset($part[0])) {
                $route .= $part[0];
            }

            if (isset($part[1])) {
                $route .= '/' . $part[1];
            }

            $ignore = array(
                'common/home',
                'common/page_header',
                'common/login',
                'common/preset',
                'common/logout',
                'common/forgotten',
                'common/reset',
                'common/filemanager',
                'common/function',
                'error/not_found',
                'error/permission',
                'error/error'
            );

            if (!in_array($route, $ignore) && !$this->user->hasPermission('view', $route)) {
                return $this->forward('error/permission');
            }
        }
    }

}

?>