<?php echo $header; ?>
<?php echo $column_left; ?>
<div class="pageheader">
    <h2><i class="fa fa-cube"></i> <?php echo $heading_title; ?></h2>
</div>
<div id="page-wrapper">

    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <!--<div class="heading">
              <h1><img src="view/image/user.png" alt="" /> <?php echo $heading_title; ?></h1>
          </div>-->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_production_management; ?></a>
                                    </h4>
                                </div>

                                <div class="panel-collapse in" id="collapseOne" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/purchase_order_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $href_bill_of_material; ?>"><?php echo $text_bill_of_material; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/good_received_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $href_production_expense; ?>"><?php echo $text_production_expense; ?></a></div>
                                            <div class="col-md-3 extrapad"> <span class="iccon" style="background-image: url('view/image/icons/product_icon.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $href_production; ?>"><?php echo $text_production; ?></a></div>
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