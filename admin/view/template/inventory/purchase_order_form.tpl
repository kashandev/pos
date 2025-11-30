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
                <!--
                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                    <i class="fa fa-balance-scale"></i>
                    &nbsp;<?php echo $lang['ledger']; ?>
                </button>
                -->
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
                    <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                    <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />
                    <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
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
                                    <label><?php echo $lang['manual_ref_no']; ?></label>
                                    <input class="form-control" type="text" name="manual_ref_no" value="<?php echo $manual_ref_no;?>" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['invoice_type']; ?></label>
                                    <div class="row">
                                        <div class="col-sm-3 col-xs-6">
                                            <div class="radio">
                                                <label>
                                                    <input name="invoice_type" id="invoice_type_local" value="Local" <?php echo ($invoice_type == 'Local'?'checked':''); ?> type="radio">
                                                    Local
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3 col-xs-6">
                                            <div class="radio">
                                                <label>
                                                    <input name="invoice_type" id="invoice_type_import" value="Import" <?php echo ($invoice_type == 'Import'?'checked':''); ?> type="radio">
                                                    Import
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['partner_type']; ?></label>
                                    <select class="form-control" id="partner_type_id" name="partner_type_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($partner_types as $partner_type): ?>
                                        <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['partner_name']; ?></label>
                                    <select class="form-control" id="partner_id" name="partner_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['supplier_id']; ?>" <?php echo ($partner_id == $supplier['supplier_id']?'selected="true"':''); ?>><?php echo $supplier['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="partner_id" class="error" style="display: none;"></label>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['terms']; ?></label>
                                    <input class="form-control" type="text" name="terms" value="<?php echo $terms; ?>" onchange="autoSave();" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['remarks']; ?></label>
                                    <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" onchange="autoSave();" autocomplete="off" />
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
                            <div class="col-lg-12 table-responsive">
                                <table id="tblPurchaseOrder" class="table table-striped table-bordered">
                                    <thead>
                                    <tr align="center">
                                        <td style="width: 7%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                        <td style="width: 120px;"><?php echo $lang['code']; ?></td>
                                        <td style="width: 200px;"><?php echo $lang['name']; ?></td>
                                        <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['rate']; ?></td>
                                        <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                                        <td style="width: 7%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                    </tr>
                                    </thead>
                                    <tbody >
                                    <?php $grid_row = 0; ?>
                                    <?php foreach($purchase_order_details as $detail): ?>

                                    <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                        <td>
                                            <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                            <a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                        </td>
                                        <td>
                                            <input onchange="getProductByCode(this);" type="text" class="form-control" name="purchase_order_details[<?php echo $grid_row; ?>][product_code]" id="purchase_order_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <select onchange="getProductById(this);" class="form-control select2" id="purchase_order_detail_product_id_<?php echo $grid_row; ?>" name="purchase_order_details[<?php echo $grid_row; ?>][product_id]" >
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach($products as $product): ?>
                                                    <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':'');?>><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <span class="input-group-btn ">
                                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_order_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="purchase_order_details[<?php echo $grid_row; ?>][unit]" id="purchase_order_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly="true" />
                                            <input type="hidden" class="form-control" name="purchase_order_details[<?php echo $grid_row; ?>][unit_id]" id="purchase_order_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                                        </td>
                                        <td>
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="purchase_order_details[<?php echo $grid_row; ?>][qty]" id="purchase_order_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                        </td>
                                        <td>
                                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="purchase_order_details[<?php echo $grid_row; ?>][rate]" id="purchase_order_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                                        </td>
                                        <td>
                                            <input type="text" class="form-control fPDecimal" name="purchase_order_details[<?php echo $grid_row; ?>][amount]" id="purchase_order_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly="true" />
                                        </td>
                                        <td>
                                            <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                            <a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
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
                            <div class="col-md-offset-9 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $lang['net_amount']; ?></label>
                                    <input type="text"  id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fDecimal" readonly="readonly" />
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
                        <!--
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button>
                        -->
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
<script type="text/javascript" src="../admin/view/js/inventory/purchase_order.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang) ?>;
    var $partner_id = '<?php echo $partner_id; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $products = <?php echo json_encode($products) ?>;
    var $warehouses = <?php echo json_encode($warehouses) ?>;
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';

    function formatRepo (repo) {
            if (repo.loading) return repo.text;

            var markup = "<div class='select2-result-repository clearfix'>";
            if(repo.image_url) {
                markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
            }
            markup +="<div class='select2-result-repository__meta'>";
            markup +="  <div class='select2-result-repository__title'>" + repo.name + "</div>";

            if (repo.description) {
                markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
            }

            markup += "<div class='select2-result-repository__statistics'>" +
                    "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
                    "</div>" +
                    "</div></div>";

            return markup;
        }

        function formatRepoSelection (repo) {
            return repo.name || repo.text;
        }




   <?php // if($this->request->get['purchase_order_id']): ?>
    $(document).ready(function() {
        $('#partner_type_id').trigger('change');
    });
   <?php // endif; ?>
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>
