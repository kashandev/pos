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
                        <?php if(isset($isEdit) && $isEdit==1): ?>
                        <?php if($is_post == 0): ?>
                        <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                            <i class="fa fa-thumbs-up"></i>
                            &nbsp;<?php echo $lang['post']; ?>
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button>
                        <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                        <?php endif; ?>
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
                                <input type="hidden" value="<?php echo $stock_out_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="Auto" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['from_warehouse']; ?></label>
                                            <select class="form-control" id="warehouse_id" name="warehouse_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse_id == $warehouse['warehouse_id']?'selected="selected"':''); ?>><?php echo $warehouse['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
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
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product']; ?></label>
                                            <select class="form-control" id="product_id" name="product_id">
                                            </select>
                                            <input type="hidden" id="product_code" name="product_code" value="" />
                                            <input type="hidden" id="product_name" name="product_name" value="" />
                                            <input type="hidden" id="unit_id" name="unit_id" value="" />
                                            <input type="hidden" id="unit" name="unit" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['quantity']; ?></label>
                                            <div class="input-group">
                                                <input class="form-control text-right" type="text" id="quantity" name="quantity" value="" />
                                                <span class="input-group-btn ">
                                                    <input class="form-control text-right" type="text" id="stock_quantity" name="stock_quantity" value="" readonly disabled />
                                                </span>
                                            </div>
                                            <input type="hidden" id="cog_rate" name="cog_rate" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="form-control btn btn-primary" id="btnAddStock">Add</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table id="tblStockOut" class="table table-striped table-bordered">
                                            <thead>
                                            <tr align="center">
                                                <td style="width: 3%;"><a class="btnAddStock btn btn-primary btn-xs" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                                                <td style="width: 200px;"><?php echo $lang['product_name']; ?></td>
                                                <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                                <td style="width: 120px;"><?php echo $lang['stock_quantity']; ?></td>
                                                <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                                <td style="width: 3%;"><a class="btnAddStock btn btn-primary btn-xs" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                            </tr>
                                            </thead>
                                            <tbody >
                                            <?php $stock_row = count($stock_out_details); $stock_row_desc = count($stock_out_details)-1;?>
                                            <?php foreach($stock_out_details as $detail): ?>
                                            <tr id="stock_row_<?php echo $stock_row_desc; ?>" data-stock_row="<?php echo $stock_row_desc; ?>">
                                                   <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                    <td>
                                                            <input class="form-control" type="text" id="stock_out_detail_<?php echo $stock_row_desc; ?>_product_code" name="stock_out_details[<?php echo $stock_row_desc; ?>][product_code]" value="<?php echo $detail['product_code']; ?>" readonly />
                                                        </td>
                                                    <td>
                                                            <input class="form-control" type="hidden" id="stock_out_detail_<?php echo $stock_row_desc; ?>_product_id" name="stock_out_details[<?php echo $stock_row_desc; ?>][product_id]" value="<?php echo $detail['product_id']; ?>" />
                                                            <input class="form-control" type="text" id="stock_out_detail_<?php echo $stock_row_desc; ?>_product_name" name="stock_out_details[<?php echo $stock_row_desc; ?>][product_name]" value="<?php echo $detail['product_name']; ?>" readonly />
                                                        </td>
                                                    <td>
                                                            <input class="form-control" type="hidden" id="stock_out_detail_<?php echo $stock_row_desc; ?>_unit_id" name="stock_out_details[<?php echo $stock_row_desc; ?>][unit_id]" value="<?php echo $detail['unit_id']; ?>" />
                                                            <input class="form-control" type="text" id="stock_out_detail_<?php echo $stock_row_desc; ?>_unit" name="stock_out_details[<?php echo $stock_row_desc; ?>][unit]" value="<?php echo $detail['unit']; ?>" readonly />
                                                        </td>
                                                    <td>
                                                            <input class="form-control" type="hidden" id="stock_out_detail_<?php echo $stock_row_desc; ?>_cog_rate" name="stock_out_details[<?php echo $stock_row_desc; ?>][cog_rate]" value="<?php echo $detail['cog_rate']; ?>" readonly />
                                                            <input class="form-control" type="text" id="stock_out_detail_<?php echo $stock_row_desc; ?>_stock_qty" name="stock_out_details[<?php echo $stock_row_desc; ?>][stock_qty]" value="<?php echo $detail['stock_qty']; ?>" readonly />
                                                        </td>
                                                    <td>
                                                            <input onchange="calculateCOGAmount(<?php echo $stock_row_desc; ?>);" class="form-control" type="text" id="stock_out_detail_<?php echo $stock_row_desc; ?>_qty" name="stock_out_details[<?php echo $stock_row_desc; ?>][qty]" value="<?php echo $detail['qty']; ?>" />
                                                            <input class="form-control" type="hidden" id="stock_out_detail_<?php echo $stock_row_desc; ?>_cog_amount" name="stock_out_details[<?php echo $stock_row_desc; ?>][cog_amount]" value="<?php echo $detail['cog_amount']; ?>" />
                                                        </td>
                                                   <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                </tr>
                                            <?php $stock_row_desc--; ?>
                                            <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-offset-9 col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_qty']; ?></label>
                                            <input type="text" id="total_qty" name="total_qty" value="<?php echo $total_qty; ?>" class="form-control fDecimal" readonly="readonly" />
                                            <input type="hidden" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" class="form-control fDecimal" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <?php if(isset($isEdit) && $isEdit==1): ?>
                                <?php if($is_post == 0): ?>
                                <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                                    <i class="fa fa-thumbs-up"></i>
                                    &nbsp;<?php echo $lang['post']; ?>
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                                    <i class="fa fa-balance-scale"></i>
                                    &nbsp;<?php echo $lang['ledger']; ?>
                                </button>
                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a>
                                <?php endif; ?>
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
    <script type="text/javascript" src="../admin/view/js/inventory/stock_out.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $lang = <?php echo json_encode($lang) ?>;
        var $stock_row = '<?php echo $stock_row; ?>';
        var $products = <?php echo json_encode($products) ?>;
        var $warehouses = <?php echo json_encode($warehouses) ?>;
        var $UrlGetProductStock = '<?php echo $href_get_product_stock; ?>';
        var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';

        function formatRepo (repo) {
            if (repo.loading) return repo.text;

            var markup = "<div class='select2-result-repository clearfix'>";
            if(repo.image_url) {
                markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
            }
            markup +="<div class='select2-result-repository__meta'>";
            markup +="  <div class='select2-result-repository__title'>" + repo.product_code+' - '+repo.name + "</div>";

            if (repo.description) {
                markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
            }

            if(repo.statistics) {
                markup += "<div class='select2-result-repository__statistics'>" +
                        "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
                        "</div>";
            }
            markup += "</div></div>";

            return markup;
        }

        function formatRepoSelection (repo) {
            return (repo.product_code+'-'+repo.name) || repo.text;
        }
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>