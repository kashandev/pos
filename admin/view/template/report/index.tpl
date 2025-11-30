<?php echo $header; ?><?php echo $column_left; ?>


<div class="pageheader">
    <h2><i class="fa fa-list-alt"></i> <?php echo $heading_title; ?></h2>

</div>


<div id="page-wrapper">
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">

                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $heading_inventory; ?></a>
                                    </h4>
                                </div>

                                <div class="panel-collapse in" id="collapseOne" style="height: auto;">

                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $purchase_order_report; ?>"><?php echo $text_purchase_order_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $goods_received_report; ?>"><?php echo $text_goods_received_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $purchase_invoice_report; ?>"><?php echo $text_purchase_invoice_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/sale_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $delivery_challan_report; ?>"><?php echo $text_delivery_challan_report; ?></a></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/sale_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $sale_report; ?>"><?php echo $text_sale_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/sale_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $sale_profit; ?>"><?php echo $text_sale_profit; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/stock_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $stock_report; ?>"><?php echo $text_stock_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/stock_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $aging_report; ?>"><?php echo $text_aging_report; ?></a></div>
                                    </div>
                                </div>
                                    </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">


                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $heading_gl; ?></a>
                                    </h4>
                                </div>


                                <div class="panel-collapse in" id="collapseTwo" style="height: auto;">

                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/chart_of_account_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $coa_report; ?>"><?php echo $text_coa; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/ledger_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $ledger_report; ?>"><?php echo $text_ledger; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/entity_ledger_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $entity_ledger; ?>"><?php echo $text_entity_ledger; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/entity_ledger_report_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $party_ledger; ?>"><?php echo $text_party_ledger; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/trial_balance_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $trial_balance; ?>"><?php echo $text_trial_balance; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/trial_balance_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $outstanding_report; ?>"><?php echo $text_outstanding_report; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/balance_sheet_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $balance_sheet; ?>"><?php echo $text_bs; ?></a></div>
                                        <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/profit_lost_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $income_statement; ?>"><?php echo $text_pl; ?></a></div>
                                    </div>
                                </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>