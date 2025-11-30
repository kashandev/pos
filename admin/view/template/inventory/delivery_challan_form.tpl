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
                
                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                    <i class="fa fa-balance-scale"></i>
                    &nbsp;<?php echo $lang['ledger']; ?>
                </button>
                
                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                    <i class="fa fa-print"></i>
                    &nbsp;<?php echo $lang['print']; ?>
                </a>
                <a class="btn btn-info" target="_blank" href="<?php echo $action_get_excel_figures; ?>">
                    <i class="fa fa-download"></i>
                    &nbsp;Excel
                </a>
                <?php endif; ?>
                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                    <i class="fa fa-undo"></i>
                    &nbsp;<?php echo $lang['cancel']; ?>
                </a>

                <?php if($validEdit == 1): ?>
                <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                <i class="fa fa-floppy-o"></i>
                &nbsp;<?php echo $lang['save']; ?>
                </button>
                <?php endif; ?>
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
<input type="hidden" value="<?= $allow_out_of_stock ?>" name="allow_out_of_stock" id="allow_out_of_stock">
<input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
<input type="hidden" value="<?php echo $delivery_challan_id; ?>" name="document_id" id="document_id" />
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <div class="radio">
                <label style="font-size: 22px;font-weight: bolder;color:Red "><input name="challan_type" id="challan_type_no" value="GST" type="radio" <?php echo ($challan_type != 'Non GST'?'checked':''); ?>> GST</label>
                &nbsp;&nbsp;
                <label style="font-size: 22px;font-weight: bolder;color: Red"><input name="challan_type" id="challan_type_yes" value="Non GST" type="radio" <?php echo ($challan_type == 'Non GST'?'checked':''); ?>> Non GST</label>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['challan_type']; ?></label>
            <select class="form-control" id="status" name="status">
                <option value="Normal"<?php echo ($status == 'Normal'?'selected="true"':'') ?>>Normal</option>>
                <option value="Sample"<?php echo ($status == 'Sample'?'selected="true"':'') ?>>Sample</option>>
            </select>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['document_no']; ?></label>
            <input class="form-control" type="text" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="Auto" />
            <input class="form-control" type="hidden" name="partner_type_id" readonly="readonly" value="<?php echo $partner_type_id; ?>"/>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
        </div>
    </div>
    <div class="col-sm-3 hide">
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
        <label><span class="required">*</span>&nbsp;<?php echo $lang['partner_name']; ?></label>
            <select class="form-control" id="partner_id" name="partner_id">
            <?php if($partner_name != ''){ ?>
                <option value="<?=$partner_id;?>" selected><?=$partner_name;?></option>
            <?php }?>
                <option value="">&nbsp;</option>
            </select>
            <label for="partner_id" class="error" style="display: none;">&nbsp;</label>
<!--            <label><span class="required">*</span>&nbsp;<?php echo $lang['partner_name']; ?></label>
            <select class="form-control" id="partner_id" name="partner_id">
                <option value="">&nbsp;</option>
                <?php foreach($customers as $customer): ?>
                <option value="<?php echo $customer['supplier_id']; ?>" <?php echo ($partner_id == $customer['supplier_id']?'selected="true"':''); ?>><?php echo $customer['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="partner_id" class="error" style="display: none;">&nbsp;</label>-->
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['ref_document_type']; ?></label>
            <select class="form-control" name="ref_document_type_id" id="ref_document_type_id">
                <option value="">&nbsp;</option>
                <option value="5" <?php echo ($ref_document_type_id == 5 ? 'selected="selected"' :''); ?>><?php echo $lang['sale_order']; ?></option>
            </select>
        </div>
    </div>
    <div class="col-sm-3">
        <label><?php echo $lang['ref_document_no']; ?></label>
        <div class="form-group">
            <select class="form-control" id="ref_document_identity" name="ref_document_identity" >
                <option value="">&nbsp;</option>
                <?php foreach($ref_documents as $ref_document): ?>
                <option value="<?php echo $ref_document['document_identity']; ?>" <?php echo ($ref_document['document_identity'] == $ref_document_identity?'selected="true"':''); ?>><?php echo $ref_document['document_identity']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['customer_unit']; ?></label>
            <select class="form-control" name="customer_unit_id" id="customer_unit_id">
                <option value="">&nbsp;</option>
                <?php foreach($customer_units as $customer_unit): ?>
                <option value="<?php echo $customer_unit['customer_unit_id']; ?>" <?php echo ($customer_unit_id == $customer_unit['customer_unit_id']?'selected="selected"':''); ?>><?php echo $customer_unit['customer_unit']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['po_no']; ?></label>
            <input class="form-control" type="text" name="po_no" id="po_no" value="<?php echo $po_no; ?>" />
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['po_date']; ?></label>
            <input class="form-control dtpDate" type="text" name="po_date" id="po_date" value="<?php echo $po_date; ?>" />
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['remarks']; ?></label>
            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label><?php echo $lang['last_rate']; ?></label>
            <input style="color:red;font-weight: bolder; font-size: 18px" class="form-control" type="text" name="last_rate" id="last_rate" value="<?php echo $last_rate; ?>" readonly/>
        </div>
    </div>

    <div class="col-sm-2">
        <div class="form-group">
            <label><?php echo $lang['manual_ref_no']; ?></label>
            <input class="form-control" type="text" name="manual_ref_no" id="manual_ref_no" value="<?php echo $manual_ref_no; ?>"/>
        </div>
    </div>

</div>
<div class="row">
    <div class="col-lg-12">
        <div class="table-responsive form-group">
            <table id="tblDeliveryChallan" class="table table-striped table-bordered" style="width: 2000px !important;max-width: 2000px !important;">
                <thead>
                <tr align="center">
                    <td style="width: 3%;"><a class="btn btn-xs btn-primary btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                    <td></td>
                    <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                    <td style="width: 350px;"><?php echo $lang['product_name']; ?></td>
                    <td style="width: 350px;"><?php echo $lang['description']; ?></td>
                    <td style="width: 350px;"><?php echo $lang['remarks']; ?></td>
                    <td style="width: 200px;"><?php echo $lang['warehouse']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['stock_qty']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                    <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                    <td style="width: 150px;"><?php echo $lang['rate']; ?></td>
                    <td style="width: 3%;"><a class="btn btn-xs btn-primary btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                </tr>
                </thead>
                <tbody >
                <?php $grid_row = 0; ?>
                <?php foreach($delivery_challan_details as $detail): ?>
                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                    <td>
                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                    </td>
                    <td>
                        <a target="_blank" href="<?php echo $detail['href']; ?>" title="Ref. Document"><?php echo $detail['ref_document_identity']; ?></a>
                        <input type="hidden" class="form-control" name="delivery_challan_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="goods_received_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" readonly/>
                        <input type="hidden" class="form-control" name="delivery_challan_details[<?php echo $grid_row; ?>][ref_document_identity]" id="goods_received_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>" readonly/>
                        <input type="hidden" class="form-control" name="delivery_challan_details[<?php echo $grid_row; ?>][ref_document_id]" id="goods_received_detail_ref_document_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_id']; ?>" readonly/>
                    </td>
                    <td>
                        <input onchange="getProductByCode(this);" type="text" class="form-control" name="delivery_challan_details[<?php echo $grid_row; ?>][product_code]" id="delivery_challan_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                        <input type="hidden" name="delivery_challan_details[<?php echo $grid_row; ?>][available_stock]"id="delivery_challan_detail_available_stock_<?php echo $grid_row; ?>">
                        <?php
                            if( $isEdit == 1 ) {
                                ?>
                                    <script>
                                        setTimeout(function(){
                                            getWarehouseStock($('#delivery_challan_detail_warehouse_id_<?php echo $grid_row; ?>'), <?= $isEdit ?>);
                                        },1000);
                                    </script>
                                <?php
                            }
                        ?>
                    </td>
                    <td>
                        <div class="input-group">
                            <select onchange="getProductById(this);" class="form-control select2" id="delivery_challan_detail_product_id_<?php echo $grid_row; ?>" name="delivery_challan_details[<?php echo $grid_row; ?>][product_id]" >
                                <option value="">&nbsp;</option>
                                <?php foreach($products as $product): ?>
                                <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':'');?>><?php echo $product['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                                                <span class="input-group-btn ">
                                                    <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="delivery_challan_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                        <i class="fa fa-search"></i>
                                                    </button>
                                                </span>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control " width="350px" name="delivery_challan_details[<?php echo $grid_row; ?>][description]" id="delivery_challan_detail_description_<?php echo $grid_row; ?>" value="<?php echo htmlentities($detail['description']); ?>" />
                    </td>
                    <td>
                        <input type="text" class="form-control " width="350px" name="delivery_challan_details[<?php echo $grid_row; ?>][remarks]" id="delivery_challan_detail_remarks_<?php echo $grid_row; ?>" value="<?php echo htmlentities($detail['remarks']); ?>" />
                    </td>
                    <td>
                        <select onchange="getWarehouseStock(this,<?=$isEdit?>);" class="required form-control select2" id="delivery_challan_detail_warehouse_id_<?php echo $grid_row; ?>" name="delivery_challan_details[<?php echo $grid_row; ?>][warehouse_id]" >
                            <option value="">&nbsp;</option>
                            <?php foreach($warehouses as $warehouse): ?>
                            <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse['warehouse_id']==$detail['warehouse_id']?'selected="true"':'');?>><?php echo $warehouse['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control fPDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][stock_qty]" id="delivery_challan_detail_stock_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['stock_qty']; ?>" readonly/>
                    </td>
                    <td>
                        <input onchange="calculateRowTotal(this);" type="text" class="form-control fPDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][qty]" id="delivery_challan_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                    </td>
                    <td>
                        <!--<select  class="form-control select2" id="delivery_challan_detail_unit_id_<?php echo $grid_row; ?>" name="delivery_challan_details[<?php echo $grid_row; ?>][unit_id]" >
                            <option value="">&nbsp;</option>
                            <?php foreach($units as $unit): ?>
                            <option value="<?php echo $unit['unit_id']; ?>" <?php echo ($unit['unit_id']==$detail['unit_id']?'selected="true"':'');?>><?php echo $unit['name']; ?></option>
                            <?php endforeach; ?>
                        </select>-->
                        <input type="text" class="form-control " name="delivery_challan_details[<?php echo $grid_row; ?>][unit]" id="delivery_challan_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" />
                        <input type="hidden" class="form-control " name="delivery_challan_details[<?php echo $grid_row; ?>][unit_id]" id="delivery_challan_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                    </td>
                    <td>
                        <input onchange="calculateTotal(this);" type="text" class="form-control fPDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][rate]" id="delivery_challan_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                        <input type="hidden" class="form-control fPDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][cog_rate]" id="delivery_challan_detail_cog_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['cog_rate']; ?>" />
                        <input type="hidden" class="form-control fPDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][cog_amount]" id="delivery_challan_detail_cog_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['cog_amount']; ?>" />
                        <input type="hidden" class="form-control fDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][tax_percent]" id="delivery_challan_detail<?php echo $grid_row; ?>_tax_percent" value="<?php echo $detail['tax_percent']; ?>" />
                        <input type="hidden" class="form-control fDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][tax_amount]" id="delivery_challan_detail<?php echo $grid_row; ?>_tax_amount" value="<?php echo $detail['tax_amount']; ?>" />
                        <input type="hidden" class="form-control fDecimal" name="delivery_challan_details[<?php echo $grid_row; ?>][net_amount]" id="delivery_challan_detail<?php echo $grid_row; ?>_net_amount" value="<?php echo $detail['net_amount']; ?>" />
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
</div>
<div class="row">
    <div class="col-sm-offset-9 col-md-3">
        <div class="form-group">
            <label><span class="required">*</span> <?php echo $lang['total_qty']; ?></label>
            <input type="text"  id="total_qty" name="total_qty" value="<?php echo number_format($total_qty,2); ?>" class="form-control fDecimal" readonly="readonly" />
            <input type="hidden"  id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" class="form-control fDecimal" readonly="readonly" />
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
        <?php if($validEdit == 1): ?>
        <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
            <i class="fa fa-floppy-o"></i>
            &nbsp;<?php echo $lang['save']; ?>
        </button>
        <?php endif; ?>
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
<script type="text/javascript" src="../admin/view/js/inventory/delivery_challan.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $allow_out_of_stock = '<?= $allow_out_of_stock ?>';
    var $lang = <?php echo json_encode($lang) ?>;
    <!--var $partner_id = '<?php echo $partner_id; ?>';-->
    var $grid_row = '<?php echo $grid_row; ?>';
    //var $products = <?php echo json_encode($products) ?>;
    var $warehouses = <?php echo json_encode($warehouses) ?>;
    var $units = <?php echo json_encode($units) ?>;
    var $UrlGetReferenceDocumentNo = '<?php echo $href_get_ref_document_no; ?>';
    var $ref_document_id = '<?php echo $ref_document_id; ?>';
    var $UrlGetSaleOrder = '<?php echo $href_get_sale_order; ?>';
    var $UrlGetRefDocument = '<?php echo $href_get_ref_document; ?>';
    var $partner_id  =    '<?php echo $partner_id; ?>';
    var $ref_document_identity  =    '<?php echo $ref_document_identity; ?>';
    var $UrlGetCustomer = '<?php echo $href_get_customer; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    var $UrlGetPartnerJSON = '<?php echo $href_get_partner_json; ?>';
    function formatRepo (repo) {
        if (repo.loading) return repo.text;

        var markup = "<div class='select2-result-repository clearfix'>";
        if(repo.image_url) {
            markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
        }
        markup +="<div class='select2-result-repository__meta'>";
        markup +="  <div class='select2-result-repository__title'>" + repo.name + "</div>";
        // d('abcd',true);
        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.name || repo.text;
    }
      
    <?php if($this->request->get['delivery_challan_id']): ?>
    $(document).ready(function() {
        //$('input[name=challan_type]').filter(':checked').trigger('change')

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

    $(document).ready(function(){
            $('#partner_id').select2({
                width: '100%',
                ajax: {
                    url: $UrlGetPartnerJSON + '&partner_type_id='+$('#partner_type_id').val(),
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
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>