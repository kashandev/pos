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
            <!--<div class="col-sm-3">
                <div class="form-group">
                    <label><?php echo $lang['manual_ref_no']; ?></label>
                    <input class="form-control" type="text" name="manual_ref_no" value="<?php echo $manual_ref_no;?>" onchange="autoSave();" />
                </div>
            </div>-->
            <div class="col-sm-3">
                <div class="form-group">
                    <label><?php echo $lang['salesman']; ?></label>
                    <select class="form-control" id="salesman_id" name="salesman_id">
                        <option value="">&nbsp;</option>
                        <?php foreach($salesmans as $salesman): ?>
                        <option value="<?php echo $salesman['salesman_id']; ?>" <?php echo ($salesman_id == $salesman['salesman_id']?'selected="true"':''); ?>><?php echo $salesman['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
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
            <div class="col-sm-3">
                <div class="form-group">
                    <label><?php echo $lang['partner_name']; ?></label>
                    <select class="form-control" id="partner_id" name="partner_id">
                        <option value="">&nbsp;</option>
                    </select>
                    <label for="partner_id" class="error" style="display: none;"></label>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="form-group">
                    <label><?php echo $lang['ref_document_no']; ?></label>
                    <select class="form-control" id="ref_document_id" name="ref_document_id" ">
                        <option value="">&nbsp;</option>
                        <?php foreach($ref_documents as $ref_document): ?>
                        <option value="<?php echo $ref_document['document_id']; ?>" <?php echo ($ref_document['document_id'] == $ref_document_id?'selected="true"':''); ?>><?php echo $ref_document['document_identity'],' ','(',$ref_document['manual_ref_no'],')' ; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3">
                <div class="form-group">
                    <label><?php echo $lang['terms']; ?></label>
                    <input class="form-control" type="text" name="terms" value="<?php echo $terms; ?>" onchange="autoSave();" />
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label><?php echo $lang['remarks']; ?></label>
                    <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" onchange="autoSave();" />
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
                <table id="tblSaleOrder" class="table table-striped table-bordered">
                    <thead>
                    <tr align="center">
                        <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                        <td style="width: 3%;"><?php echo $lang['ref_document']; ?></td>
                        <td style="width: 120px;"><?php echo $lang['code']; ?></td>
                        <td style="width: 200px;"><?php echo $lang['name']; ?></td>
                        <td style="width: 200px;"><?php echo $lang['description']; ?></td>
                        <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                        <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                        <td style="width: 120px;"><?php echo $lang['rate']; ?></td>
                        <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                        <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                    </tr>
                    </thead>
                    <tbody >
                    <?php $grid_row = 0; ?>
                    <?php foreach($sale_order_details as $detail): ?>
                    <?php if($detail['ref_document_identity']): ?>
                    <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                        <td><a title="Remove" class="btnRemoveGrid btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                        <td>
                            <a target="_blank" href="<?php echo $detail['href']; ?>" title="Ref. Document"><?php echo $detail['ref_document_identity']; ?></a>
                            <input type="hidden" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="goods_received_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" readonly/>
                            <input type="hidden" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][ref_document_identity]" id="goods_received_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>" readonly/>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][product_code]" id="goods_received_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" readonly/>
                        </td>
                        <td>
                            <input type="hidden" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][product_id]" id="goods_received_detail_product_id_<?php echo $grid_row; ?>" value="<?php echo $detail['product_id']; ?>" readonly/>
                            <input type="text" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][product_name]" id="goods_received_detail_product_name_<?php echo $grid_row; ?>" value="<?php echo $detail['product_name']; ?>" readonly/>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][description]" id="sale_order_detail_description_<?php echo $grid_row; ?>" value="<?php echo $detail['description']; ?>"  />
                        </td>
                        <td>
                            <input type="hidden" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][unit_id]" id="goods_received_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" readonly/>
                            <input type="text" class="form-control" name="goods_received_details[<?php echo $grid_row; ?>][unit]" id="goods_received_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly/>
                        </td>
                        <td>
                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="goods_received_details[<?php echo $grid_row; ?>][qty]" id="goods_received_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                            <input  type="hidden" class="form-control fPDecimal" name="goods_received_details[<?php echo $grid_row; ?>][utilized_qty]" id="goods_received_detail_utilized_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['utilized_qty']; ?>" readonly/>
                        </td>
                        <td>
                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="goods_received_details[<?php echo $grid_row; ?>][rate]" id="goods_received_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" readonly />
                        </td>
                        <td>
                            <input type="text" class="form-control fPDecimal" name="goods_received_details[<?php echo $grid_row; ?>][amount]" id="goods_received_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly="true" />
                        </td>
                        <td style="width: 3%;"></td>
                    </tr>
                    <?php else: ?>
                    <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                        <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                        <td>
                            <a target="_blank" href="<?php echo $detail['href']; ?>" title="Ref. Document"><?php echo $detail['ref_document_identity']; ?></a>
                            <input type="hidden" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="sale_order_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" readonly/>
                            <input type="hidden" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][ref_document_identity]" id="sale_order_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>" readonly/>
                        </td>
                        <td>
                            <input onchange="getProductByCode(this);" type="text" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][product_code]" id="sale_order_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                        </td>
                        <td>
                            <div class="input-group">
                                <select onchange="getProductById(this);" class="form-control select2" id="sale_order_detail_product_id_<?php echo $grid_row; ?>" name="sale_order_details[<?php echo $grid_row; ?>][product_id]" >
                                    <option value="">&nbsp;</option>
                                    <?php foreach($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':'');?>><?php echo $product['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                                <span class="input-group-btn ">
                                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_order_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                    <i class="fa fa-search"></i>
                                                </button>
                                                </span>
                            </div>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][description]" id="sale_order_detail_description_<?php echo $grid_row; ?>" value="<?php echo $detail['description']; ?>"  />
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][unit]" id="sale_order_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly="true" />
                            <input type="hidden" class="form-control" name="sale_order_details[<?php echo $grid_row; ?>][unit_id]" id="sale_order_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                        </td>
                        <td>
                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details[<?php echo $grid_row; ?>][qty]" id="sale_order_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                        </td>
                        <td>
                            <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="sale_order_details[<?php echo $grid_row; ?>][rate]" id="sale_order_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                        </td>
                        <td>
                            <input type="text" class="form-control fPDecimal" name="sale_order_details[<?php echo $grid_row; ?>][amount]" id="sale_order_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly="true" />
                        </td>
                        <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                    </tr>
                    <?php endif; ?>
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
                    <label><?php echo $column_net_amount; ?></label>
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
<script type="text/javascript" src="../admin/view/js/inventory/sale_order.js"></script>

<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang) ?>;
    var $partner_id = '<?php echo $partner_id; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetRefDocumentNo = '<?php echo $href_get_ref_document_no; ?>';
    var $UrlGetRefDocument = '<?php echo $href_get_ref_document; ?>';

    var $products = <?php echo json_encode($products) ?>;
    var $warehouses = <?php echo json_encode($warehouses) ?>;
    <?php if($this->request->get['sale_order_id']): ?>
    $(document).ready(function() {
        $('#partner_type_id').trigger('change');
    });
    <?php endif; ?>
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>
