<?php echo $header; ?>
<?php echo $column_left; ?>
<div class="pageheader">
    <h2><i class="fa fa-pencil-square-o"></i> <?php echo $heading_title; ?></h2>
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
                                        <a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_purhcase_management; ?></a>
                                    </h4>
                                </div>
                                <div class="panel-collapse in" id="collapseOne" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $purchase_order; ?>"><?php echo $text_purchase_order; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/good_received_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $goods_received; ?>"><?php echo $text_goods_received; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_invoice_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $purchase_invoice; ?>"><?php echo $text_purchase_invoice; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_return_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $purchase_return; ?>"><?php echo $text_purchase_return; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/fi_customer_invoice_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $fi_supplier_invoice; ?>"><?php echo $text_fisupplier; ?></a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_sale_management; ?></a>
                                    </h4>
                                </div>


                                <div class="panel-collapse in" id="collapseTwo" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">

                                            <!--<div class="col-md-12"> <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $sale_quatation; ?>"><?php echo $text_sale_quatation; ?></a></div>
                                            <div class="col-md-12"> <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $sale_order; ?>"><?php echo $text_sale_order; ?></a></div>
                                            <img border="0" style="vertical-align:middle;" src="view/image/bullet_arrow.gif">&nbsp;&nbsp;<a href="<?php echo $made; ?>"><?php echo $text_made; ?></a><br>-->
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/delivery_chalaan_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $sale_inquiry; ?>"><?php echo $text_sale_inquiry; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/delivery_chalaan_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $delivery_challan; ?>"><?php echo $text_delivery_challan; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/sales_invoice_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $sale_invoice; ?>"><?php echo $text_sale_invoice; ?></a></div>
                                            <!-- <div class="col-md-12"><span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $delivery_challan; ?>"><?php echo $text_delivery_challan; ?></a></div>-->
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/sale_return_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $sale_return; ?>"><?php echo $text_sale_return; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/fi_customer_invoice_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $fi_customer_invoice; ?>"><?php echo $text_ficustomer; ?></a></div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">

                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseThree" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_stock_management; ?></a>
                                    </h4>
                                </div>

                                <div class="panel-collapse in" id="collapseThree" style="height: auto;">


                                    <div class="panel-body">
                                        <div class="row">

                                            <!--<div class="col-md-12"> <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $sale_quatation; ?>"><?php echo $text_sale_quatation; ?></a></div>
                                            <div class="col-md-12"> <span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $sale_order; ?>"><?php echo $text_sale_order; ?></a></div>
                                            <img border="0" style="vertical-align:middle;" src="view/image/bullet_arrow.gif">&nbsp;&nbsp;<a href="<?php echo $made; ?>"><?php echo $text_made; ?></a><br>-->

                                            <!-- <div class="col-md-12"><span class="glyphicon glyphicon-circle-arrow-right"></span>&nbsp;&nbsp;<a href="<?php echo $delivery_challan; ?>"><?php echo $text_delivery_challan; ?></a></div>-->
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/stock_adjustment_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $stock_adjustment; ?>"><?php echo $text_stock_adjustment; ?></a></div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseFour" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_vehicle; ?></a>
                                    </h4>
                                </div>
                                <div class="panel-collapse in" id="collapseFour" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $work_order; ?>"><?php echo $text_work_order; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $vehicle_dispatch; ?>"><?php echo $text_dispatch_invoice; ?></a></div>
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