
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<?php echo $header; ?>
<link rel="stylesheet" href="../admin/view/css/common/custom.css"></link>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <style type="text/css">
      .input-width{
        width:120px !important;
      }  
      .input-row{
        display:flex;
        gap:10px;
        justify-content:end;
        flex-wrap: wrap;
    }
    </style>
<?php echo $page_header; ?>
<?php echo $column_left; ?>
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1><?php echo $lang['heading_title']; ?></h1>
    <div class="row">
        <div class="col-sm-4">
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
        <div class="col-sm-8">
            <div class="pull-right">
                <?php if(isset($isEdit) && $isEdit==1): ?>
                <!-- <a class="btn btn-info" target="_blank" href="<?php echo $action_cash_receipt; ?>">
                    <i class="fa fa-money"></i>
                    &nbsp;<?php echo $lang['cash_receipt']; ?>
                </a> -->
<!--                 <a class="btn btn-info" target="_blank" href="<?php echo $action_receipt; ?>">
                    <i class="fa fa-university"></i>
                    &nbsp;<?php echo $lang['receipt']; ?>
                </a> -->
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
                <!-- <a class="btn btn-info" target="_blank" href="<?php echo $action_print_bill; ?>">
                    <i class="fa fa-print"></i>
                    &nbsp;<?php echo $lang['print_bill']; ?>
                </a> -->
        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sales_tax_invoice; ?>">
            <i class="fa fa-print"></i>
            &nbsp;<?php echo $lang['print_sales_bill']; ?>
        </a>
        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sale_receipt; ?>">
             <i class="fa fa-print"></i>
             &nbsp;<?php echo $lang['print_sale_receipt']; ?>
        </a>

        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sales_tax_new_invoice; ?>">
         <i class="fa fa-print"></i>
         &nbsp;<?php echo $lang['print_sale_invoice']; ?>
        </a>


                <!--<a class="btn btn-info" target="_blank" href="<?php echo $action_print_exempted_invoice; ?>">
                    <i class="fa fa-print"></i>
                    &nbsp;<?php echo $lang['print_exempted_invoice']; ?>
                </a>-->
                <?php endif; ?>
                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                    <i class="fa fa-undo"></i>
                    &nbsp;<?php echo $lang['cancel']; ?>
                </a>
                <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
<form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
<input type="hidden" value="<?= $allow_out_of_stock ?>" name="allow_out_of_stock" id="allow_out_of_stock">
<input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
<input type="hidden" value="<?php echo $sale_tax_invoice_id; ?>" name="document_id" id="document_id" />
<input type="hidden" id="sale_invoice" name="sale_type" value="sale_invoice" >
<!-- <div class="panel panel-default">
    <div class="panel-heading" style="font-size: 24px;font-weight: bolder;" >
        <div class="row">
            <div class="col-sm-6 sale_button">
                <label class="switch">
                  <input type="checkbox" id="sale_invoice" name="sale_type" value="sale_invoice" >
                  <span class="slider round"></span>
                </label>
                <label>Sale Invoice</label>    
            </div>
        </div>
    </div>
</div> -->

<div class="row">
   <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['invoice_type']; ?></label>
            <div class="row">
                <div class="col-sm-3 col-xs-6">
                    <div class="radio">
                        <label>
                            <input name="invoice_type" id="invoice_type_credit" value="Credit" <?php echo ($invoice_type != 'Cash'?'checked':''); ?> type="radio">
                            Credit
                        </label>
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="radio">
                        <label>
                            <input name="invoice_type" id="invoice_type_cash" value="Cash" <?php echo ($invoice_type == 'Cash'?'checked':''); ?> type="radio">
                            Cash
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
     <div class="col-sm-3 hide">
        <div class="form-group">
            <label><?php echo $lang['partner_type']; ?></label>
            <select class="form-control" id="partner_type_id" name="partner_type_id">
                <option value="">&nbsp;</option>
                <?php foreach($partner_types as $partner_type): ?>
                <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="selected"':''); ?>><?php echo $partner_type['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="partner_type_id" class="error" style="display: none;">&nbsp;</label>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><span class="required">*</span>&nbsp;<?php echo $lang['customer']; ?></label>
            <select class="form-control" id="partner_id" name="partner_id">
                <option value="">&nbsp;</option>
            </select>
            <label for="partner_id" class="error" style="display: none;">&nbsp;</label>
        </div>
    </div>
</div>
<div class="row">

    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $lang['remarks']; ?></label>
            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
        </div>
    </div>
     <div class="col-sm-3">
     <div class="form-group">
     <label><?php echo $lang['customer_no']; ?></label>
    <input type="tel" id="customer_no" name="customer_no" value="<?php echo $customer_no; ?>" class="form-control" />
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
            <input class="form-control fInteger" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" onchage="calcNetAmount()" />
        </div>
    </div>
</div>
<!-- <div class="row hide">
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['partner_type']; ?></label>
            <select class="form-control" id="partner_type_id" name="partner_type_id">
                <option value="">&nbsp;</option>
                <?php foreach($partner_types as $partner_type): ?>
                <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ('2' == $partner_type['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="partner_type_id" class="error" style="display: none;"></label>
        </div>
    </div>
</div> -->
<div class="row">
<!--     <div class="col-sm-2">
        <div class="form-group">
            <label>&nbsp;<?php echo $lang['po_no']; ?></label>
            <input class="form-control " type="text" name="po_no" id="po_no" value="<?php echo $po_no; ?>" />
            <input class="form-control " type="hidden" name="dc_no" id="dc_no" value="<?php echo $dc_no; ?>" />

        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            <label><?php echo $lang['po_date']; ?></label>
            <input class="form-control dtpDate" type="text" id="po_date" name="po_date" value="<?php echo $po_date; ?>" />
        </div>
    </div> -->
    <!--<div class="col-sm-3">
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
    </div>-->
<!--     <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['customer_remarks']; ?></label>
            <input class="form-control" type="text" name="customer_remarks" value="<?php echo $customer_remarks; ?>" />
        </div>
    </div> -->
<!--     <div class="col-sm-2">
        <div class="form-group">
            <label><?php echo $lang['last_rate']; ?></label>
            <input style="color:red;font-weight: bolder; font-size: 18px" class="form-control" type="text" name="last_rate" id="last_rate" value="<?php echo $last_rate; ?>" readonly/>
        </div>
    </div>
 -->
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive form-group">
            <table id="tblSaleInvoice" class="table table-striped table-bordered">
                <thead>
                <tr align="center">
                    <td style="width: 90px;"><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                    <td style="width: 100px;"><?php echo $lang['document']; ?></td>
                    <td style="width: 100px;"><?php echo $lang['product_code']; ?></td>
                    <td style="width: 100px;" class="hide"><?php echo $lang['available_stock']; ?></td>
                    <td style="width: 300px;"><?php echo $lang['product_name']; ?></td>
                    <!-- <td style="width: 300px;"><?php echo $lang['description']; ?></td> -->
                    <td style="width: 20%;"><?php echo $lang['product_category']; ?></td>
                    <!-- <td style="width: 200px;"><?php echo $lang['warehouse']; ?></td> -->
                    <td style="width: 200px;"><?php echo $lang['stock_qty']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                    <!-- <td style="width: 150px;"><?php echo $lang['unit']; ?></td> -->
                    <td style="width: 150px;"><?php echo $lang['rate']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
<!--                     <td style="width: 120px;"><?php echo $lang['discount_percent']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['discount_amount']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['gross_amount']; ?></td> -->
<!--                     <td style="width: 120px;"><?php echo $lang['tax_percent']; ?></td>
                    <td style="width: 120px;"><?php echo $lang['tax_amount']; ?></td> -->
                    <!-- <td style="width: 120px;"><?php echo $lang['net_amount']; ?></td> -->
                    <td style="width: 50px;"><a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                </tr>
                </thead>
                <tbody >
                <?php foreach($sale_tax_invoice_details as $grid_row => $detail): ?>
                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                        <a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                    </td>
                    <td>
                        <input type="hidden" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][ref_document_detail_id]" id="sale_tax_invoice_detail_ref_document_detail_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_detail_id']; ?>" />
                        <input type="hidden" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="sale_tax_invoice_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" />
                        <input type="hidden" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][ref_document_identity]" id="sale_tax_invoice_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>" />
                        <a target="_blank" href="<?php echo $detail['href']; ?>"><?php echo $detail['ref_document_identity']; ?></a>
                    </td>                    
                    <td>
                        <input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][product_code]" id="sale_tax_invoice_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                    </td>
                    <td class="hide">
                        <input type="text" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][available_stock]" id="sale_tax_invoice_details_available_stock_<?php echo $grid_row; ?>" readonly disabled class="form-control">
                        <?php if( $isEdit == 1 ): ?>
                        <script>
                            $(window).load(function(){
                                setTimeout(function(){
                                    validateWarehouseStock($('#sale_tax_invoice_detail_warehouse_id_<?php echo $grid_row; ?>'), true);
                                }, 1000);
                            });
                        </script>
                        <?php endif; ?>
                    </td>
                    <td style="min-width: 300px;">
                        <div class="input-group">
                            <select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2 product code1" id="sale_tax_invoice_detail_product_id_<?php echo $grid_row; ?>" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][product_id]" >
                                <option value="">&nbsp;</option>
                                <?php foreach($products as $product): ?>
                                <?php if($product['product_id']==$detail['product_id']): ?>
                                <option value="<?php echo $product['product_id']; ?>" selected="true"><?php echo $product['name']; ?></option>
                                <?php else: ?>
                                <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                                <option value="<?php echo $detail['product_id']; ?>" selected="selected"><?php echo $detail['product_name']; ?></option>
                            </select>
                            <span class="input-group-btn ">
                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="sale_tax_invoice_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                    <i class="fa fa-search"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                    <td class="hide">
                        <input style="min-width: 300px;" type="text" class="form-control" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][description]" id="sale_tax_invoice_detail_description_<?php echo $grid_row; ?>" value="<?php echo $detail['description']; ?>" />

                        <?php
                        if($detail['ref_document_identity']):
                           
                        ?>
                         <input type="hidden" class="form-control select2 warehouse_id" id="sale_tax_invoice_detail_warehouse_id2_<?php echo $grid_row; ?>" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][warehouse_id]" value="<?php echo $detail['warehouse_id']?>">    
                        <?php
                          endif;
                        ?>
                    </td>

                    <td>
                       <input type="hidden" class="form-control select2 product_category_id" id="sale_tax_invoice_detail_product_category_id_<?php echo $grid_row; ?>" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][product_category_id]"
                        value="<?php echo $detail['product_category_id']; ?>">
                        <input type="text" style="min-width: 300px;" class="form-control select2 product_category" id="sale_tax_invoice_detail_product_category_<?php echo $grid_row; ?>"
                        value="<?php echo $detail['product_category']; ?>"  readonly>
                     </td> 

                    <td class="hide">
                        <input   style="min-width: 300px;" type="hidden" class="form-control" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][ref_dc]" id="sale_tax_invoice_detail_ref_dc_<?php echo $grid_row; ?>" value="0" />
                        <select onchange="validateWarehouseStock(this,$isEdit);" class="form-control select2 warehouse_id" id="sale_tax_invoice_detail_warehouse_id_<?php echo $grid_row; ?>" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][warehouse_id]" >
                            <?php foreach($warehouses as $warehouse): ?>
                            <?php if($warehouse['warehouse_id']==$detail['warehouse_id']): ?>
                            <option value="<?php echo $warehouse['warehouse_id']; ?>" selected="true"><?php echo $warehouse['name']; ?></option>
                            <?php else: ?>
                            <option value="<?php echo $warehouse['warehouse_id']; ?>"><?php echo $warehouse['name']; ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][stock_qty]" id="sale_tax_invoice_detail_stock_qty_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['stock_qty']; ?>" readonly/>
                    </td>
                    <td>
                        <input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][qty]" id="sale_tax_invoice_detail_qty_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['qty']; ?>" />
                    </td>
                    <td class="hide">
                        <input type="text" style="min-width: 100px;" class="form-control" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][unit]" id="sale_tax_invoice_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly />
                        <input type="hidden" class="form-control" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][unit_id]" id="sale_tax_invoice_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                    </td>
                    <td>
                        <input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][rate]" id="sale_tax_invoice_detail_rate_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['rate']; ?>" />
                        <input type="hidden" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][cog_rate]" id="sale_tax_invoice_detail_cog_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['cog_rate']; ?>" />
                    </td>
                    <td>
                        <input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][amount]" id="sale_tax_invoice_detail_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['amount']; ?>" readonly="true" />
                        <input type="hidden" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][cog_amount]" id="sale_tax_invoice_detail_cog_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['cog_amount']; ?>" readonly="true" />
                    </td>
<!--                     <td >
                        <input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][discount_percent]" id="sale_tax_invoice_detail_discount_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['discount_percent']; ?>" />
                    </td>
                    <td >
                        <input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][discount_amount]" id="sale_tax_invoice_detail_discount_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['discount_amount']; ?>" />
                    </td>
                    <td >
                        <input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][gross_amount]" id="sale_tax_invoice_detail_gross_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['gross_amount']; ?>" readonly="true"/>
                    </td> -->
<!--                     <td>
                        <input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][tax_percent]"  id="sale_tax_invoice_detail_tax_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_percent']; ?>"  <?php echo ($sale_type == 'sale_invoice'?'readonly="true"':''); ?>/>
                    </td>
                    <td>
                        <input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][tax_amount]" id="sale_tax_invoice_detail_tax_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_amount']; ?>" <?php echo ($sale_type == 'sale_invoice'?'readonly="true"':''); ?> />
                    </td> -->
<!--                     <td>
                        <input type="text" style="min-width: 100px;" class="form-control fPInteger" name="sale_tax_invoice_details[<?php echo $grid_row; ?>][total_amount]" id="sale_tax_invoice_detail_total_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['total_amount']; ?>" readonly="true" />
                    </td> -->
                    <td>
                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                        <a id="btnAddGrid" title="Add" class="btn btn-xs btn-primary" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php $grid_row = count($sale_tax_invoice_details); ?>
                </tbody>
                <tfoot>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<div class="input-row">
    <!-- <div class="col-sm-3"></div> -->
    <div class="input-width">
        <div class="form-group">
            <label ><?php echo $lang['total_qty']; ?></label>   
            <input type="text" id="total_qty" name="total_qty" value="<?php echo (int) $total_qty; ?>" class="form-control fInteger" readonly="true" />
        </div>
    </div>
    <div class="input-width">
        <div class="form-group ml-4">
            <label ><?php echo $lang['item_amount']; ?></label>
            <input type="text" id="item_amount" name="item_amount" value="<?php echo (int) $item_amount; ?>" class="form-control fInteger" readonly="true" />
        </div>
    </div>
    <div class="input-width">
        <div class="form-group">
            <label><?php echo $lang['discount_amount']; ?></label>
            <input class="form-control fInteger" type="text" id="discount_amount" name="discount_amount" value="<?php echo $discount_amount; ?>" onchange="calculateTotal();" />
        </div>
    </div>
    <div class="input-width">
        <div class="form-group">
            <label><?php echo $lang['net_amount']; ?></label>
            <input type="text" id="net_amount" name="net_amount" value="<?php echo (int) $net_amount; ?>" class="form-control fInteger" readonly="readonly" />
        </div>
    </div>
    <div class="input-width">
        <div class="form-group">
            <label><?php echo $lang['cash_received']; ?></label>
            <input type="text" id="cash_received" name="cash_received" value="<?php echo (int) $cash_received; ?>" class="form-control fInteger" onchange="calculateTotal();" />
        </div>
    </div>
    <div class="input-width">
        <div class="form-group">
            <label><?php echo $lang['balance_amount']; ?></label>
            <input type="text" id="balance_amount" name="balance_amount" value="<?php echo (int) $balance_amount; ?>" class="form-control fInteger"  readonly="readonly" />
        </div>
    </div>
</div>

<!--     <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['item_discount']; ?></label>
            <input class="form-control fInteger" type="text" id="item_discount" name="item_discount" value="<?php echo $item_discount; ?>" readonly="readonly" />
        </div>
    </div>
<!--     <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['item_tax']; ?></label>
            <input class="form-control fInteger" type="text" id="item_tax" name="item_tax" value="<?php echo $item_tax; ?>" readonly="readonly" />
        </div>
    </div> -->
<!--     <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['item_total']; ?></label>
            <input class="form-control fInteger" type="text" id="item_total" name="item_total" value="<?php echo $item_total; ?>" readonly="readonly" />
        </div>
    </div> -->

<!-- <div class="row"> -->
<!--     <div class="col-sm-3 ">
        <div class="form-group">
            <label><?php echo $lang['tax_percent']; ?></label>
            <input class="form-control fInteger" type="text" id="tax_per" name="tax_per" value="<?php echo $tax_per; ?>" onfocusout="AddTaxes();" />
        </div>
    </div> -->
        <!-- <div class="col-sm-3"></div> -->
<!--     <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['discount_amount']; ?></label>
            <input class="form-control fInteger" type="text" id="discount_amount" name="discount_amount" value="<?php echo $discount_amount; ?>" onchange="calculateTotal();" />
        </div>
    </div> -->
<!--     <div class="col-sm-3" >
        <div class="form-group">
            <label><?php echo $lang['cartage']; ?></label>
            <input class="form-control fInteger" type="text" id="cartage" name="cartage" value="<?php echo $cartage; ?>" onchange="calculateTotal();" />
        </div>
    </div> 
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['net_amount']; ?></label>
            <input type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fInteger" readonly="readonly" />
        </div>
    </div> -->
<!-- </div> -->
</form>
</div>
<div class="box-footer">
    <div class="pull-right">
        <?php if(isset($isEdit) && $isEdit==1): ?>
        <!-- <a class="btn btn-info" target="_blank" href="<?php echo $action_cash_receipt; ?>">
            <i class="fa fa-money"></i>
            &nbsp;<?php echo $lang['cash_receipt']; ?>
        </a> -->
<!--         <a class="btn btn-info" target="_blank" href="<?php echo $action_receipt; ?>">
            <i class="fa fa-university"></i>
            &nbsp;<?php echo $lang['receipt']; ?>
        </a> -->
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
        <!-- <a class="btn btn-info" target="_blank" href="<?php echo $action_print_bill; ?>">
            <i class="fa fa-print"></i>
            &nbsp;<?php echo $lang['print_bill']; ?>
        </a> -->
        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sales_tax_invoice; ?>">
            <i class="fa fa-print"></i>
            &nbsp;<?php echo $lang['print_sales_bill']; ?>
        </a>
        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sale_receipt; ?>">
             <i class="fa fa-print"></i>
             &nbsp;<?php echo $lang['print_sale_receipt']; ?>
        </a>
        <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sales_tax_new_invoice; ?>">
         <i class="fa fa-print"></i>
         &nbsp;<?php echo $lang['print_sale_invoice']; ?>
        </a>

<!--         <a class="btn btn-info" target="_blank" href="<?php echo $action_print_sales_tax_commercial_invoice; ?>">
                    <i class="fa fa-print"></i>
                    &nbsp;<?php echo $lang['print_c_invoice']; ?>
                </a> -->
        <!-- <a class="btn btn-info" target="_blank" href="<?php echo $action_print_exempted_invoice; ?>">
            <i class="fa fa-print"></i>
            &nbsp;<?php echo $lang['print_exempted_invoice']; ?>
        </a> -->
        <?php endif; ?>
        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
            <i class="fa fa-undo"></i>
            &nbsp;<?php echo $lang['cancel']; ?>
        </a>
        <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
<script type="text/javascript" src="../admin/view/js/inventory/sale_tax_invoice.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<link rel="stylesheet"  href="plugins/iCheck/all.css">
<link rel="stylesheet"  href="plugins/iCheck/line/line.css">
<script type="text/javascript" src="plugins/iCheck/icheck.js"></script>

<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $isEdit = '<?= $isEdit; ?>';
    var $allow_out_of_stock = '<?= $allow_out_of_stock ?>';
    var $sale_type = '<?php echo $sale_type; ?>';
    var $url_validate_stock = '<?php echo $url_validate_stock; ?>';
    var $lang = <?php echo json_encode($lang) ?>;
    var $partner_id = '<?php echo $partner_id; ?>';
    var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
    var $GetRefDocument = '<?php echo $get_ref_document; ?>';
    var $GetRefDocumentRecord = '<?php echo $get_ref_document_record; ?>';
    var $GetRefDocumentJson = '<?php echo $href_get_ref_document_json; ?>';
    var $UrlGetDocumentDetails = '<?php echo $href_get_document_detail; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetContainerProducts = '<?php echo $href_get_container_products; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    var $products = <?php echo json_encode($products) ?>;
    var $warehouses = <?php echo json_encode($warehouses) ?>;
    var $customer_no = '<?php echo $customer_no ?>';

    var $UrlGetPartnerJSON = '<?php echo $href_get_partner_json; ?>';

    $(document).ready(function() {
//        $('#partner_type_id').val('2').trigger('change');
        calculateTotal();
        
        $('.iCheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
    });

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
        "</div></div>";

        return markup;
    }

    function formatReposit (repo) {
        if (repo.loading) return repo.text;

        var markup = "<div class='select2-result-repository clearfix'>";
//        if(repo.image_url) {
//            markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
//        }
        markup +="<div class='select2-result-repository__meta'>";
        markup +="  <div class='select2-result-repository__title'>" + repo.document_identity + "</div>";

//        if (repo.description) {
//            markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
//        }
        "</div></div>";

        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.name || repo.text;
    }

        <?php if($this->request->get['sale_tax_invoice_id']): ?>
        $(document).ready(function() {
            $('#partner_type_id').trigger('change');
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

            calculateTotal();
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