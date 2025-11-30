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
                <a class="btn btn-info" href="<?php echo $action_post; ?>">
                    <i class="fa fa-thumbs-up"></i>
                    &nbsp;<?php echo $lang['post']; ?>
                </a>
                <?php endif; ?>
                <?php if($is_post == 1): ?>
                <a class="btn btn-info" href="<?php echo $action_un_post; ?>">
                    <i class="fa fa-thumbs-up"></i>
                    &nbsp;<?php echo $lang['un_post']; ?>
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
<input type="hidden" value="<?php echo $stock_transfer_id; ?>" name="document_id" id="document_id" />
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
    <!--<div class="col-sm-3">
        <div class="form-group">
            <label><span class="required">*</span>&nbsp;<?php echo $lang['partner']; ?></label>
            <select class="form-control" id="partner_id" name="partner_id">
                <option value="">&nbsp;</option>
                <?php foreach($partners as $partner): ?>
                <option value="<?php echo $partner['customer_id']; ?>" <?php echo ($partner_id == $partner['customer_id']?'selected="selected"':''); ?>><?php echo $partner['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>-->
    <div class="col-sm-6 hide">
        <div class="form-group">
            <label><span class="required">*</span>&nbsp;<?php echo $lang['to_branch']; ?></label>
            <select class="form-control" id="to_branch_id" name="to_branch_id" onchange="getWarehouseByBranchId();" >
                <option value="">&nbsp;</option>
                <?php foreach($company_branchs as $company_branch): ?>
                <option value="<?php echo $company_branch['company_branch_id']; ?>" <?php echo ($to_branch_id == $company_branch['company_branch_id']?'selected="selected"':''); ?>><?php echo $company_branch['name']; ?></option>
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
    <div class="col-sm-3">
        <div class="form-group">
            <label><span class="required">*</span>&nbsp;<?php echo $lang['from_warehouse']; ?></label>
            <select class="form-control" id="warehouse_id" name="warehouse_id">
                <option value="">&nbsp;</option>
                <?php foreach($FromWarehouses as $warehouse): ?>
                <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse_id == $warehouse['warehouse_id']?'selected="selected"':''); ?>><?php echo $warehouse['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label><?php echo $lang['remarks']; ?></label>
            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label><?php echo $lang['last_rate']; ?></label>
            <input  style="color:red;font-weight: bolder; font-size: 18px" class="form-control" type="text" name="txt_last_rate" id="txt_last_rate" value="<?php echo $txt_last_rate; ?>" readonly/>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['billty_remarks']; ?></label>
            <input class="form-control" type="text" name="billty_remarks" value="<?php echo $billty_remarks; ?>" />
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label>&nbsp;<?php echo $lang['bilty_no']; ?></label>
            <input class="form-control " type="text" id="billty_no" name="billty_no" value="<?php echo $billty_no; ?>" />
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['bilty_date']; ?></label>
            <input class="form-control dtpDate" type="text" id="billty_date" name="billty_date" value="<?php echo $billty_date; ?>" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive form-group">
            <table id="tblStockTransfer" class="table table-striped table-bordered">
                <thead>
                <tr align="center">
                    <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                    <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                    <td style="width: 250px;"><?php echo $lang['product_name']; ?></td>
                    <td style="width: 250px;"><?php echo $lang['description']; ?></td>
                    <td style="width: 150px;"><?php echo $lang['warehouse']; ?></td>
                    <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['stock_quantity']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['rate']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                    <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                </tr>
                </thead>
                <tbody >
                <?php $grid_row = 0; ?>
                <?php foreach($stock_transfer_details as $detail): ?>
                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                    <td>
                        <input onchange="getProductByCode(this);" type="text" class="form-control" name="stock_transfer_details[<?php echo $grid_row; ?>][product_code]" id="stock_transfer_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                    </td>
                    <td style="min-width: 250px;">
                        <div class="input-group">
                            <select onchange="getProductById(this);" class="form-control select2 product" id="stock_transfer_detail_product_id_<?php echo $grid_row; ?>" name="stock_transfer_details[<?php echo $grid_row; ?>][product_id]" >
                                <option value="">&nbsp;</option>
                                 <option value="<?php echo $detail['product_id']; ?>" selected="true"><?php echo $detail['product_name']; ?></option>
<!--                                 <?php foreach($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':'');?>><?php echo $product['name']; ?></option>
                                <?php endforeach; ?> -->
                            </select>
                                <span class="input-group-btn ">
                                    <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="stock_transfer_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
                        </div>
                    </td>
                    <td>
                        <input   style="min-width: 250px;" type="text" class="form-control" name="stock_transfer_details[<?php echo $grid_row; ?>][description]" id="stock_transfer_detail_description_<?php echo $grid_row; ?>" value="<?php echo $detail['description']; ?>" />
                    </td>
                    <td>
                        <select  class="form-control select2" id="stock_transfer_detail_warehouse_id_<?php echo $grid_row; ?>" name="stock_transfer_details[<?php echo $grid_row; ?>][warehouse_id]" required>
                            <option value="">&nbsp;</option>
                            <?php foreach($warehouses as $warehouse): ?>
                            <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse['warehouse_id']==$detail['warehouse_id']?'selected="true"':'');?>><?php echo $warehouse['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="stock_transfer_details[<?php echo $grid_row; ?>][unit]" id="stock_transfer_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly="true" />
                        <input type="hidden" class="form-control" name="stock_transfer_details[<?php echo $grid_row; ?>][unit_id]" id="stock_transfer_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                    </td>
                    <td>
                        <input type="text" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][stock_qty]" id="stock_transfer_detail_stock_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['stock_qty']; ?>" readonly="true" />
                        <input type="hidden" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][cog_rate]" id="stock_transfer_detail_cog_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['cog_rate']; ?>" />
                    </td>
                    <td>
                        <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][qty]" id="stock_transfer_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                        <input type="hidden" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][cog_amount]" id="stock_transfer_detail_cog_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['cog_amount']; ?>" />
                    </td>
                    <td>
                        <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][rate]" id="stock_transfer_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" readonly/>
                    </td>
                    <td>
                        <input type="text" class="form-control fPDecimal" name="stock_transfer_details[<?php echo $grid_row; ?>][amount]" id="stock_transfer_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly/>
                    </td>
                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                </tr>
                <?php $grid_row++; ?>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                </tfoot>
            </table>
        </div>
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
        <a class="btn btn-info" href="<?php echo $action_post; ?>">
            <i class="fa fa-thumbs-up"></i>
            &nbsp;<?php echo $lang['post']; ?>
        </a>
        <?php endif; ?>
        <?php if($is_post == 1): ?>
        <a class="btn btn-info" href="<?php echo $action_un_post; ?>">
            <i class="fa fa-thumbs-up"></i>
            &nbsp;<?php echo $lang['un_post']; ?>
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
<script type="text/javascript" src="../admin/view/js/inventory/stock_transfer.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang) ?>;
    var $partner_id = '<?php echo $partner_id; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetWarehouseByBranchId = '<?php echo $get_warehouse_by_branch; ?>';
    var $products = <?php echo json_encode($products) ?>;
    var $warehouses = <?php echo json_encode($warehouses) ?>;
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    // var $warehouses = '';
    var $company_branchs = <?php echo json_encode($company_branchs) ?>;
    var $branch_id;

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

        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.name || repo.text;
    }

    <?php if($this->request->get['stock_transfer_id']): ?>
    $(document).ready(function() {
        $('#partner_type_id').trigger('change');
        $('#to_branch_id').trigger('change');

        $('select.product').select2({
            width: '100%',
            ajax: {
                url: $UrlGetProductJSON,
                dataType: 'json',
                type: 'post',
                mimeType:"multipart/form-data",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function (data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;

                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 2,
            templateResult: formatRepo, // omitted for brevity, see the source of this page
            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
        });

    });
    <?php endif; ?>
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>