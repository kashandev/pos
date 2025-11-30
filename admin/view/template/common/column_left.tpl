<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
            <li class="treeview" data-route="common/home">
                <a href="<?php echo $href_dashboard; ?>">
                    <i class="fa fa-dashboard"></i>
                    <span><?php echo $lang['dashboard']; ?></span>
                </a>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_administrator_dashboard; ?>">
                    <i class="fa fa-gear"></i>
                    <span><?php echo $lang['administrator']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['general_setup']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="setup/company"><a href="<?php echo $href_company; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['company']; ?></a></li>
                            <li data-route="setup/company_setting"><a href="<?php echo $href_company_setting; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['company_setting']; ?></a></li>
                            <li data-route="setup/company_branch"><a href="<?php echo $href_company_branch; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['company_branch']; ?></a></li>
                            <li data-route="setup/currency"><a href="<?php echo $href_currency; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['currency']; ?></a></li>
                            <li data-route="setup/partner_category"><a href="<?php echo $href_partner_category; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['partner_category']; ?></a></li>
                            <li data-route="setup/customer"><a href="<?php echo $href_customer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['customer']; ?></a></li>
                            <li data-route="setup/supplier"><a href="<?php echo $href_supplier; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['supplier']; ?></a></li>
                            <li data-route="setup/salesman"><a href="<?php echo $href_salesman; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['salesman']; ?></a></li>

                            <li data-route="setup/project"><a href="<?php echo $href_project; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['project']; ?></a></li>
                            <li data-route="setup/sub_project"><a href="<?php echo $href_sub_project; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['sub_project']; ?></a></li>


                            <li data-route="setup/document"><a href="<?php echo $href_document; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['document']; ?></a></li>
                            
                            <li data-route="setup/terms"><a href="<?php echo $href_terms; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['terms']; ?></a></li>

                            <li data-route="setup/closing_transfer"><a href="<?php echo $href_closing_transfer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['closing_transfer']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['user_management']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="user/user_permission"><a href="<?php echo $href_user_group_permission; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['user_group_permission']; ?></a></li>
                            <li data-route="user/user"><a href="<?php echo $href_user; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['user']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_gl_dashboard; ?>">
                    <i class="fa fa-book"></i>
                    <span><?php echo $lang['general_ledger']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_setup']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="gl/module_setting"><a href="<?php echo $href_gl_module_setting; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_module_setting']; ?></a></li>
                            <li data-route="gl/coa_level1"><a href="<?php echo $href_gl_coa1; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_chart_of_account_level1']; ?></a></li>
                            <li data-route="gl/coa_level2"><a href="<?php echo $href_gl_coa2; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_chart_of_account_level2']; ?></a></li>
                            <li data-route="gl/coa_level3"><a href="<?php echo $href_gl_coa3; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_chart_of_account_level3']; ?></a></li>
                            <li data-route="gl/profit_and_loss_mapping"><a href="<?php echo $href_gl_profit_and_loss_mapping; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_profit_and_loss_mapping']; ?></a></li>

                            <!-- <li data-route="gl/mapping_coa"><a href="<?php echo $href_gl_mapping_account; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_mapping_account']; ?></a></li> -->
                            <li data-route="gl/copy_coa"><a href="<?php echo $href_gl_coa_copy; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_copy_all_chart_of_account']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_transaction']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="gl/opening_account"><a href="<?php echo $href_gl_opening_account; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_opening_account']; ?></a></li>
                            <li data-route="gl/bank_reconciliation"><a href="<?php echo $href_gl_bank_reconciliation; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_bank_reconciliation']; ?></a></li>
                            <li data-route="gl/debit_invoice"><a href="<?php echo $href_gl_debit_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_debit_invoice']; ?></a></li>
                            <li data-route="gl/credit_invoice"><a href="<?php echo $href_gl_credit_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_credit_invoice']; ?></a></li>

                            <li data-route="gl/bank_payment"><a href="<?php echo $href_gl_bank_payment; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_bank_payment_voucher']; ?></a></li>
                            <li data-route="gl/cash_payment"><a href="<?php echo $href_gl_cash_payment; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_cash_payment_voucher']; ?></a></li>
                            <li data-route="gl/bank_receipt"><a href="<?php echo $href_gl_bank_receipt; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_bank_receipt_voucher']; ?></a></li>
                            <li data-route="gl/cash_receipt"><a href="<?php echo $href_gl_cash_receipt; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_cash_receipt_voucher']; ?></a></li>


                            <li data-route="gl/payments"><a href="<?php echo $href_gl_payments; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_payments']; ?></a></li>
                            <li data-route="gl/fund_transfer"><a href="<?php echo $href_gl_fund_transfer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_fund_transfer']; ?></a></li>
                            <li data-route="gl/receipts"><a href="<?php echo $href_gl_receipts; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_receipts']; ?></a></li>


                            <li data-route="gl/journal_voucher"><a href="<?php echo $href_gl_journal_voucher; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_journal_voucher']; ?></a></li>
                            <li data-route="gl/cash_book"><a href="<?php echo $href_gl_cash_book; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_cash_book']; ?></a></li>
                            <li data-route="gl/transfer_settlement"><a href="<?php echo $href_gl_transfer_settlement; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_transfer_settlement']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_advances']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="gl/advance_payment"><a href="<?php echo $href_gl_advance_payment; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_advance_payment']; ?></a></li>
                            <li data-route="gl/advance_receipt"><a href="<?php echo $href_gl_advance_receipt; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['gl_advance_receipt']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_inventory_dashboard; ?>">
                    <i class="fa fa-money"></i>
                    <span><?php echo $lang['inventory_management']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_setup']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="inventory/module_setting"><a href="<?php echo $href_inventory_module_setting; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_module_setting']; ?></a></li>
                            <li data-route="inventory/warehouse"><a href="<?php echo $href_inventory_warehouse; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_warehouse']; ?></a></li>
                            <li data-route="inventory/product_category"><a href="<?php echo $href_inventory_product_category; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_product_category']; ?></a></li>
                            <li data-route="inventory/product_sub_category"><a href="<?php echo $href_inventory_product_sub_category; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_product_sub_category']; ?></a></li>
                            <li data-route="inventory/brand"><a href="<?php echo $href_inventory_brand; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_brand']; ?></a></li>
                            <li data-route="inventory/make"><a href="<?php echo $href_inventory_make; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_make']; ?></a></li>
                            <li data-route="inventory/model"><a href="<?php echo $href_inventory_model; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_model']; ?></a></li>
                            <li data-route="inventory/unit"><a href="<?php echo $href_inventory_unit; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_unit']; ?></a></li>
                            <li data-route="inventory/product"><a href="<?php echo $href_inventory_product; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_product']; ?></a></li>
                            <li data-route="inventory/product_update"><a href="<?php echo $href_inventory_product_update; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_product_update']; ?></a></li>
                            <li data-route="inventory/customer_unit"><a href="<?php echo $href_inventory_customer_unit; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_customer_unit']; ?></a></li>

                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_management']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="inventory/opening_stock"><a href="<?php echo $href_inventory_opening_stock; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_opening_stock']; ?></a></li>
                            <li data-route="inventory/stock_out"><a href="<?php echo $href_stock_out; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_out']; ?></a></li>
                            <li data-route="inventory/stock_in"><a href="<?php echo $href_stock_in; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_in']; ?></a></li>
                            <li data-route="inventory/stock_transfer"><a href="<?php echo $href_stock_transfer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_transfer']; ?></a></li>
                            <li data-route="inventory/branch_stock_transfer"><a href="<?php echo $href_branch_stock_transfer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['branch_stock_transfer']; ?></a></li>
                            <li data-route="inventory/stock_adjustment"><a href="<?php echo $href_stock_adjustment; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_adjustment']; ?></a></li>
                            <li data-route="inventory/stock_update"><a href="<?php echo $href_stock_update; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_stock_update']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_purchase_management']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="inventory/purchase_order"><a href="<?php echo $href_purchase_order; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_purchase_order']; ?></a></li>
                            <li data-route="inventory/goods_received"><a href="<?php echo $href_goods_received; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_goods_received']; ?></a></li>
                            <li data-route="inventory/purchase_invoice"><a href="<?php echo $href_purchase_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_purchase_invoice']; ?></a></li>
                            <li data-route="inventory/purchase_return"><a href="<?php echo $href_purchase_return; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_purchase_return']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_management']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="inventory/sale_inquiry"><a href="<?php echo $href_sale_inquiry; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_inquiry']; ?></a></li>
                            <li data-route="inventory/quotation"><a href="<?php echo $href_quotation; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_quotation']; ?></a></li>
                            <li data-route="inventory/sale_order1"><a href="<?php echo $href_sale_order; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_order']; ?></a></li>
                            <li data-route="inventory/delivery_challan"><a href="<?php echo $href_delivery_challan; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_delivery_challan']; ?></a></li>
                            <li data-route="inventory/sale_invoice"><a href="<?php echo $href_sale_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_invoice']; ?></a></li>
                             <!--<li data-route="inventory/sale_tax_invoice"><a href="<?php echo $href_sale_tax_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_tax_invoice']; ?></a></li>-->

                            <!-- Renaming sale_tax_invoice to sale_invoice -->
                            <li data-route="inventory/sale_tax_invoice"><a href="<?php echo $href_sale_tax_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_invoice']; ?></a></li>
                            
                            <!--<li data-route="inventory/sale_discount_policy"><a href="<?php echo $href_sale_discount_policy; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['sale_discount_policy']; ?></a></li>-->

                            <li data-route="inventory/pos_invoice"><a href="<?php echo $href_pos_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_pos_invoice']; ?></a></li>
                            <li data-route="inventory/sale_return"><a href="<?php echo $href_sale_return; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_sale_return']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_production_dashboard; ?>">
                    <i class="fa fa-apple"></i>
                    <span><?php echo $lang['production']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['production_management']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="production/bom"><a href="<?php echo $href_bill_of_material; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['bill_of_material']; ?></a></li>
                            <li data-route="production/expense"><a href="<?php echo $href_production_expense; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['production_expense']; ?></a></li>
                            <li data-route="production/production"><a href="<?php echo $href_production; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['production']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_vehicle_dashboard; ?>">
                    <i class="fa fa-truck"></i>
                    <span><?php echo $lang['vehicle_management']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['transaction']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="vehicle/work_order"><a href="<?php echo $href_work_order; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['work_order']; ?></a></li>
                            <li data-route="vehicle/dispatch_invoice"><a href="<?php echo $href_dispatch_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['dispatch_invoice']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_travel_dashboard; ?>">
                    <i class="fa fa-plane"></i>
                    <span><?php echo $lang['travel_management']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_setup']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="travel/module_setting"><a href="<?php echo $href_travel_module_setting; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_module_setting']; ?></a></li>
                            <li data-route="travel/country"><a href="<?php echo $href_travel_country; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_country']; ?></a></li>
                            <li data-route="travel/destination"><a href="<?php echo $href_travel_destination; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_destination']; ?></a></li>
                            <li data-route="travel/hotel"><a href="<?php echo $href_travel_hotel; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_hotel']; ?></a></li>
                            <li data-route="travel/service"><a href="<?php echo $href_travel_service; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_service']; ?></a></li>
                            <li data-route="travel/member"><a href="<?php echo $href_travel_member; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_member']; ?></a></li>
                        </ul>
                    </li>
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_transaction']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="travel/travel_invoice"><a href="<?php echo $href_travel_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['travel_invoice']; ?></a></li>
                        </ul>
                    </li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_tool_dashboard; ?>">
                    <i class="fa fa-wrench"></i>
                    <span><?php echo $lang['tool']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li data-route="tool/reminder"><a href="<?php echo $href_tool_reminder; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['reminder']; ?></a></li>
                    <li data-route="tool/backup"><a href="<?php echo $href_tool_backup; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['backup']; ?></a></li>
                    <li data-route="tool/sms"><a href="<?php echo $href_tool_sms; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['sms']; ?></a></li>
                </ul>
            </li>
            <li class="treeview level1">
                <a href="<?php echo $href_report_dashboard; ?>">
                    <i class="fa fa-print"></i>
                    <span><?php echo $lang['report']; ?></span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['general_ledger_report']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="report/aging_report"><a href="<?php echo $href_report_aging; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_aging']; ?></a></li>
                            <li data-route="report/coa"><a href="<?php echo $href_report_coa; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['chart_of_account']; ?></a></li>

                            <li data-route="report/debit_service_invoice_report"><a href="<?php echo $href_report_debit_service_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_debit_service_invoice']; ?></a></li>

                            
                            <!--<li data-route="report/document_ledger"><a href="<?php echo $href_report_document_ledger; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['document_ledger']; ?></a></li>-->
                            <li data-route="report/balance_sheet"><a href="<?php echo $href_report_balance_sheet; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['balance_sheet']; ?></a></li>
                            <li data-route="report/bank_reconciliation_report"><a href="<?php echo $href_report_bank_reconciliation; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['bank_reconciliation']; ?></a></li>
                            <li data-route="report/ledger_report"><a href="<?php echo $href_report_ledger; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['ledger']; ?></a></li>
                            <li data-route="report/outstanding_report"><a href="<?php echo $href_report_outstanding_report; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['outstanding_report']; ?></a></li>
                            <li data-route="report/party_ledger"><a href="<?php echo $href_report_party_ledger; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['party_ledger']; ?></a></li>
                            <li data-route="report/income_statement"><a href="<?php echo $href_report_income_statement; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['profit_lost']; ?></a></li>
                            <li data-route="report/trial_balance"><a href="<?php echo $href_report_trial_balance; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['trial_balance']; ?></a></li>
                        </ul>
                    </li>


                    <li class="level2">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['inventory_report']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="report/bank_receipt_report"><a href="<?php echo $href_report_bank_receipt_report; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_bank_receipt_report']; ?></a></li>
                            <li data-route="report/delivery_challan_report"><a href="<?php echo $href_report_delivery_challan; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_delivery_challan']; ?></a></li>
                            <li data-route="report/delivery_challan_against_sales"><a href="<?php echo $href_report_delivery_challan_against_sales; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_delivery_challan_against_sales']; ?></a></li>
                            
                            <li data-route="report/goods_received_report"><a href="<?php echo $href_report_goods_received; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_goods_received']; ?></a></li>

                            <li data-route="report/opening_stock"><a href="<?php echo $href_report_opening_stock; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_opening_stock']; ?></a></li>
                            <li data-route="report/dead_item_report"><a href="<?php echo $href_report_dead_item; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_dead_item']; ?></a></li>
                            <li data-route="report/product_report"><a href="<?php echo $href_report_product; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_product']; ?></a></li>
                            <li data-route="report/purchase_order_report"><a href="<?php echo $href_report_purchase_order; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_purchase_order']; ?></a></li>
                            <li data-route="report/purchase_invoice_report"><a href="<?php echo $href_report_purchase_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_purchase_invoice']; ?></a></li>
                            <li data-route="report/quotation_report"><a href="<?php echo $href_report_quotation; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_quotation']; ?></a></li>
                            <li data-route="report/sale_order_report"><a href="<?php echo $href_report_sale_order; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_sale_order']; ?></a></li>
                            <li data-route="report/sale_report"><a href="<?php echo $href_report_sale_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_sale_invoice']; ?></a></li>
                            <!--<li data-route="report/sale_tax_report"><a href="<?php echo $href_report_sale_tax_invoice; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_sale_tax_invoice']; ?></a></li>-->
                            <!-- Renaming sale tax invoice report to sale invoice report -->
                            <li data-route="report/sale_tax_report"><a href="<?php echo $href_report_sale_tax_invoice; ?>"><i class="fa fa-arrow-right"></i>Sale Report</a></li>
                            <li data-route="report/stock"><a href="<?php echo $href_report_stock; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_stock']; ?></a></li>
                            <li data-route="report/stock_transfer_report"><a href="<?php echo $href_report_stock_transfer; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_stock_transfer']; ?></a></li>
                            <li data-route="report/inventory_consumption_report"><a href="<?php echo $href_report_inventory_consumption; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_inventory_consumption']; ?></a></li>
                            <li data-route="report/sale_analysis_report"><a href="<?php echo $href_report_sale_analysis; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['report_sale_analysis']; ?></a></li>
                        </ul>
                    </li>

                    <li class="level3">
                        <a href="#"><i class="fa fa-arrow-right"></i><?php echo $lang['user_report']; ?><i class="fa fa-angle-left pull-right"></i></a>
                        <ul class="treeview-menu">
                            <li data-route="report/login_history_report"><a href="<?php echo $href_report_login_history; ?>"><i class="fa fa-arrow-right"></i><?php echo $lang['login_history_report']; ?></a></li>


                        </ul>
                    </li>

                </ul>
            </li>



            <hr>
            <li class="treeview" data-route="user/user_profile">
                <a href="<?php echo $href_user_profile; ?>">
                    <i class="fa fa-user-secret"></i>
                    <span><?php echo $lang['user_profile']; ?></span>
                </a>
            </li>
            <li class="treeview" data-route="common/logout">
                <a href="<?php echo $href_logout; ?>">
                    <i class="fa fa-sign-out"></i>
                    <span><?php echo $lang['sign_out']; ?></span>
                </a>
            </li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>
<script type="text/javascript">
    var $permissions = <?php echo json_encode($permissions); ?>;
    $("li[data-route]").each(function(){
        var $route = $(this).data('route');
        var $position = $.inArray($route, $permissions);
        if($position < 0) {
            $(this).remove();
        }
    });

    $(".level2 ul").each(function(){
        var $length = $(this).has('li').length;
        if($length == 0) {
            var $obj = $(this).parent();
            $($obj).remove();
        }
    });

    $(".level1 ul").each(function(){
        var $length = $(this).has('li').length;
        if($length == 0) {
            var $obj = $(this).parent();
            $($obj).remove();
        }
    });

    var $controller='<?php echo $controller; ?>';
    $("[data-route='" + $controller + "']").parents('li').addClass('active');
    $("[data-route='" + $controller + "']").addClass('active');
</script>
<!--
<script src="plugins/annyang/annyang.min.js"></script>
<script type="text/javascript">
    if (annyang) {
        // define the functions our commands will run.
        var home = function() {
            var $href = $("ul").find("[data-route='common/home']").children('a').attr("href");
            location.assign($href);
        };
        var project = function() {
            var $href = $("ul").find("[data-route='setup/project']").children('a').attr("href");
            location.assign($href);
        };
        var widget = function() {
            var $href = $("ul").find("[data-route='setup/widget']").children('a').attr("href");
            location.assign($href);
        };
        var report = function() {
            var $href = $("ul").find("[data-route='setup/report']").children('a').attr("href");
            location.assign($href);
        };
        var commands = {
            'dashboard': home,
            'home':      home,
            'project':   project,
            'widget':    widget,
            'report':    report
        };

        // OPTIONAL: activate debug mode for detailed logging in the console
        annyang.debug();

        // Add voice commands to respond to
        annyang.addCommands(commands);

        // OPTIONAL: Set a language for speech recognition (defaults to English)
        // For a full list of language codes, see the documentation:
        // https://github.com/TalAter/annyang/blob/master/docs/README.md#languages
        annyang.setLanguage('en');

        // Start listening. You can call this here, or attach this call to an event, button, etc.
        annyang.start();
    } else {
        console.log('Unsupported');
    }
</script>
-->