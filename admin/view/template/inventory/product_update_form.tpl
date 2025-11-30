<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
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
                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                    <i class="fa fa-undo"></i>
                    &nbsp;<?php echo $lang['cancel']; ?>
                </a>
                <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                <i class="fa fa-floppy-o"></i>
                &nbsp;<?php echo $lang['save']; ?>
                </button>
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
                </div><!-- /.box-header -->
                <div class="box-body">
                    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                        <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                        <input type="hidden" value="<?php echo $stock_adjustment_id; ?>" name="document_id" id="document_id" />
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['product_category']; ?></label>
                                    <select class="form-control" id="product_category_id" name="product_category_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($product_categorys as $product_category): ?>
                                        <option value="<?php echo $product_category['product_category_id']; ?>" <?php echo ($product_category_id == $product_category['product_category_id']?'selected="selected"':''); ?>><?php echo $product_category['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="product_category_id" class="error" style="display: none;">&nbsp;</label>
                                </div>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['base_currency']; ?></label>
                                    <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                                    <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['document_currency']; ?></label>
                                    <select class="form-control" id="document_currency_id" name="document_currency_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($currencys as $currency): ?>
                                        <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['conversion_rate']; ?></label>
                                    <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" onchage="calcNetAmount()" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <table id="tblStockAdjustment" class="table table-striped table-bordered">
                                    <thead>
                                    <tr align="center">
                                        <td style="width: 120px;"><?php echo $lang['code']; ?></td>
                                        <td style="width: 300px;"><?php echo $lang['name']; ?></td>
                                        <td style="width: 200px;"><?php echo $lang['brand']; ?></td>
                                        <td style="width: 150px;"><?php echo $lang['cost_price']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['sale_price']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['wholesale_price']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['minimum_price']; ?></td>
                                    </tr>
                                    </thead>
                                    <tbody >
                                    <?php $grid_row = 0; ?>
                                    <?php foreach($stock_adjustment_details as $detail): ?>
                                    <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                        <td>
                                            <input type="hidden" name="stock_adjustment_details[<?php echo $grid_row; ?>][product_code]" value="<?php echo $detail['product_code']; ?>" />
                                            <?php echo $detail['product_code']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" name="stock_adjustment_details[<?php echo $grid_row; ?>][product_id]" value="<?php echo $detail['product_id']; ?>" />
                                            <?php echo $detail['product_name']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" name="stock_adjustment_details[<?php echo $grid_row; ?>][unit_id]" value="<?php echo $detail['unit_id']; ?>" />
                                            <?php echo $detail['unit']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" name="stock_adjustment_details[<?php echo $grid_row; ?>][stock_qty]" value="<?php echo $detail['stock_qty']; ?>" />
                                            <?php echo $detail['stock_qty']; ?>
                                        </td>
                                        <td>
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control" id="stock_adjustment_detail_qty_<?php echo $grid_row; ?>" name="stock_adjustment_details[<?php echo $grid_row; ?>][qty]" value="<?php echo $detail['qty']; ?>" />
                                        </td>
                                        <td>
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control" id="stock_adjustment_detail_rate_<?php echo $grid_row; ?>" name="stock_adjustment_details[<?php echo $grid_row; ?>][rate]" value="<?php echo $detail['rate']; ?>" />
                                        </td>
                                        <td>
                                            <input onchange="calculateTotal();" type="text" class="form-control fDecimal" id="stock_adjustment_detail_amount_<?php echo $grid_row; ?>" name="stock_adjustment_details[<?php echo $grid_row; ?>][amount]" value="<?php echo $detail['amount']; ?>" />
                                        </td>
                                    </tr>
                                    <?php $grid_row++; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="box-footer">
                    <div class="pull-right">
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                        <i class="fa fa-floppy-o"></i>
                        &nbsp;<?php echo $lang['save']; ?>
                        </button>
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
<script type="text/javascript" src="../admin/view/js/inventory/product_update.js"></script>

<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang) ?>;
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetProducts = '<?php echo $href_get_products; ?>';
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>