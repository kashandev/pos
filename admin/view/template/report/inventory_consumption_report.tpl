<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><?php echo $lang['heading_title']; ?></h1>
            <div class="row">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <li>
                            <a href="<?php echo $breadcrumb['href']; ?>">
                                <i class="<?php echo $breadcrumb['class']; ?>"></i>
                                <?php echo $breadcrumb['text']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="col-sm-6">
                    <div class="pull-right">
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-header box-default">
                            <?php if ($error_warning) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $error_warning; ?>
                            </div>
                            <?php } ?>
                            <?php  if ($success) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?>
                            </div>
                            <?php  } ?>
                            <form action="#" target="_blank" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['from_date']; ?></label>
                                            <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['to_date']; ?></label>
                                            <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                        <label><?php echo $lang['department']; ?></label>
                                    <select class="form-control" name="department_id" >
                                        <option value="">&nbsp;</option>
                                        <?php foreach($departments as $department): ?>
                                        <option value="<?php echo $department['department_id']; ?>"><?php echo $department['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-md-6">
                                    <label><?php echo $entry_product; ?></label>
                                <div class="form-group input-group">
                                    <select class="form-control" name="product_id" id="product_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($products as $product): ?>
                                        <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="input-group-btn">
                                    <button type="button"  model="setup/product" ref_id="product_id" callback="getProductInformation" value="..." class="QSearch btn btn-default" ><i class="fa fa-search"></i>
                                    </button>
                                    </span>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $entry_invoice_type; ?></label>
                                    <select class="form-control" id="invoice_type" name="invoice_type" >
                                        <option value="">&nbsp;</option>
                                        <option value="cash" <?php echo ($invoice_type == 'cash' ? 'selected="selected"': ''); ?>><?php echo $text_cash; ?></option>
                                        <option value="credit" <?php echo ($invoice_type == 'credit' ? 'selected="selected"': ''); ?>><?php echo $text_credit; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo $entry_group_by; ?></label>
                                    <select class="form-control" name="group_by" >
                                        <option value="">&nbsp;</option>
                                        <option value="invoice_date"><?php echo $text_invoice_date; ?></option>
                                        <option value="invoice_no"><?php echo $text_invoice_no; ?></option>
                                        <option value="warehouse"><?php echo $text_warehouse; ?></option>
                                        <option value="department"><?php echo $text_department; ?></option>
                                        <option value="product"><?php echo $text_product; ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <div class="pull-right">
                                    <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                                        <i class="fa fa-print"></i>
                                        &nbsp;<?php echo $lang['print']; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
    <script src="plugins/dataTables/jquery.dataTables.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="dist/js/pages/report/inventory_consumption.js"></script>
    <script type="text/javascript">
        var $UrlPrint = '<?php echo $href_print_report; ?>';
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>