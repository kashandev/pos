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
                
                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getLedger();">
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
                <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ((($is_post==1))?'disabled="true"':''); ?>>
                <i class="fa fa-floppy-o"></i>
                &nbsp;<?php echo $lang['save']; ?>
                </button>
            </div>
        </div>
    </div>
</section>
<!-- Main content -->
<section class="content">
    <style>
        .loaderjs{
            position:fixed;
            top:0;
            left:0;
            right:0;
            bottom:0;
            background:rgb(0 0 0 / 29%);
            z-index:999999;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        .loaderjs.hide{
            display:none;

        }
    </style>

    <div id="loaderjs" class="loaderjs">
        <div id="loadingGif"><img src="<?php echo $loader_image; ?>"></div>
    </div>
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
                        <input type="hidden" id="form_key" name="form_key" value="<?php echo $form_key ?>" />
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
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['warehouse']; ?></label>
                                    <select class="form-control" id="warehouse_id" name="warehouse_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($warehouses as $warehouse): ?>
                                        <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse_id == $warehouse['warehouse_id']?'selected="selected"':''); ?>><?php echo $warehouse['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="warehouse_id" class="error" style="display: none;">&nbsp;</label>
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
                            <div class="col-lg-12">
                                <table id="tblStockAdjustment" class="table table-striped table-bordered">
                                    <thead>
                                    <tr align="center">
                                        <td style="width:3% !important;"><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                        <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                                        <td style="width: 200px;"><?php echo $lang['product_name']; ?></td>
                                        <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['stock_quantity']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['rate']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                                        <td style="width:3% !important;"><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                    </tr>
                                    </thead>
                                    <tbody >
                                    <?php $grid_row = 0; ?>
                                    <?php foreach($stock_adjustment_details as $detail): ?>
                                    <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                        <td>
                                            <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                            <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                            <?php echo $detail['product_code']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_product_id_<?php echo $grid_row; ?>" value="<?php echo $detail['product_id']; ?>" />
                                            <?php echo $detail['product_name']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                                            <?php echo $detail['unit']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_stock_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['stock_qty']; ?>" class="form-control"/>
                                            <?php echo $detail['stock_qty']; ?>
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_hidden_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>">
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control" id="stock_adjustment_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_hidden_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>">
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control" id="stock_adjustment_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                                        </td>
                                        <td>
                                            <input type="hidden" id="stock_adjustment_detail_hidden_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>">
                                            <input onchange="calculateTotal();" type="text" class="form-control fDecimal" id="stock_adjustment_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" />
                                        </td>
                                        <td>
                                            <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                            <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
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
                        <div class="row">
                            <div class="col-sm-offset-6 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $lang['total_qty']; ?></label>
                                    <input type="text" id="total_qty" name="total_qty" value="<?php echo $total_qty; ?>" class="form-control fDecimal" readonly="readonly" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo $lang['total_amount']; ?></label>
                                    <input type="text" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" class="form-control fDecimal" readonly="readonly" />
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
                        
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getLedger();">
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
                        <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ((($is_post==1))?'disabled="true"':''); ?>>
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
<script type="text/javascript" src="../admin/view/js/inventory/stock_adjustment.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang) ?>;
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetWarehouseStocks = '<?php echo $href_get_warehouse_stocks; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    var $UrlAddRecords = '<?php echo $href_add_record_session; ?>';
    var $UrlGetLedger = '<?php echo $href_get_ledger; ?>';

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