<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <div class="content-wrapper">
        <?php if ($error_warning) { ?>
        <div class="warning"><?php echo $error_warning; ?></div>
        <?php } ?>
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><?php echo $lang['heading_title']; ?></h1>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $lang['general_setup']; ?></a>
                                            </h4>
                                        </div>
                                        <div class="panel-collapse in" id="collapseOne" style="height: auto;">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $company_setting; ?>"><?php echo $lang['company_setting']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $company_branch; ?>"><?php echo $lang['company_branch']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $currency; ?>"><?php echo $lang['currency']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $country; ?>"><?php echo $lang['country']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $state; ?>"><?php echo $lang['state']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $warehouse; ?>"><?php echo $lang['ware_house']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $industry; ?>"><?php echo $lang['customer_category']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $customer; ?>"><?php echo $lang['customer']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $supplier; ?>"><?php echo $lang['supplier']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $salesman; ?>"><?php echo $lang['salesman']; ?></a></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $lang['inventory_setup']; ?></a>
                                            </h4>
                                        </div>
                                        <div class="panel-collapse in" id="collapseTwo" style="height: auto;">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $manufacture; ?>"><?php echo $lang['manufacture']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $brand; ?>"><?php echo $lang['brand']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $make; ?>"><?php echo $lang['make']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $model; ?>"><?php echo $lang['model']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $unit; ?>"><?php echo $lang['unit']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $product_category; ?>"><?php echo $lang['product_category']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $product; ?>"><?php echo $lang['product']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $opening_stock; ?>"><?php echo $lang['opening_stock']; ?></a></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a href="#collapseThree" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $lang['user_setup']; ?></a>
                                            </h4>
                                        </div>
                                        <div class="panel-collapse in" id="collapseThree" style="height: auto;">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $user_permission; ?>"><?php echo $lang['user_group_permission']; ?></a></div>
                                                    <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $user; ?>"><?php echo $lang['user']; ?></a></div>
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
        </section>
    </div>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>