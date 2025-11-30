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
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetailReport();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print_detail']; ?>
                        </a>
                        <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();">
                            <i class="fa fa-print"></i>
                            &nbsp;Excel
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
                        <div class="box-header">
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
                                            <label><?php echo $lang['warehouse']; ?></label>
                                            <select class="form-control" name="warehouse_id" id="warehouse_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                <option value="<?php echo $warehouse['warehouse_id']; ?>"><?php echo $warehouse['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_category']; ?></label>
                                            <select class="form-control" id="product_category_id" name="product_category_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($product_categories as $product_category): ?>
                                                <option value="<?php echo $product_category['product_category_id']; ?>" <?php echo ($product_category['product_category_id'] == $product_category_id ?
                                                'selected="selected"': ''); ?>><?php echo $product_category['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label><?php echo $lang['product']; ?></label>
                                        <select class="form-control" name="product_id" id="product_id">
                                            <option value="">&nbsp;</option>
                                            <?php foreach($products as $product): ?>
                                            <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name'] . ' (' . $product['product_code'] . ')'; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button id="btnFilter" class="btn btn-info form-control" type="button" onclick="getDetailReport();"><?php echo $lang['filter']; ?></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table id="tblReport" class="table table-striped table-bordered">
                                        <thead class="th-color">
                                        <tr>
                                            <th class="center"><?php echo $lang['document_date']; ?></th>
                                            <th class="center"><?php echo $lang['document_no']; ?></th>
                                            <th class="center"><?php echo $lang['warehouse']; ?></th>
                                            <th class="center"><?php echo $lang['product_category']; ?></th>
                                            <th class="center"><?php echo $lang['product_code']; ?></th>
                                            <th class="center"><?php echo $lang['product']; ?></th>
                                            <th class="center"><?php echo $lang['unit']; ?></th>
                                            <th class="center"><?php echo $lang['qty']; ?></th>
                                            <th class="center"><?php echo $lang['rate']; ?></th>
                                            <th class="center"><?php echo $lang['amount']; ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetailReport();">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print_detail']; ?>
                                </a>
                                <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();"><i class="fa fa-print"></i>&nbsp;Excel
                                </a>
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
    <script type="text/javascript" src="../admin/view/js/report/opening_stock.js"></script>
    <script type="text/javascript">
        var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';
        var $UrlGetDetailReport = '<?php echo $href_get_detail_report; ?>';
        var $UrlPrintDetailReport = '<?php echo $href_print_detail_report; ?>';
        $dataTable = $('#tblReport').DataTable();
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>