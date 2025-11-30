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
                <div class="col-sm-12">
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
                               <a class="btn btn-info" target="_blank" href="<?php echo $action_print_barcode; ?>">
                                <i class="fa fa-print"></i>
                                &nbsp;<?php echo $lang['print_barcode']; ?>
                               </a>

                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print_purchase_invoice; ?>">
                                 <i class="fa fa-print"></i>
                                 &nbsp;<?php echo $lang['print_purchase_invoice']; ?>
                                 </a>

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
                                <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                                <input type="hidden" value="<?php echo $purchase_invoice_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
<!--                                     <div class="col-sm-3">
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
                                    </div> -->
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
                                        <label style="opacity:0;">czcxz</label>
                                      <br>
                                      <a href="#" class="btn btn-primary pull-left" onClick="AddBrand=window.open('<?php echo $href_product_form ?>','AddBrand','width=600,height=600'); return false;"><i class="fa fa-plus"> Add Product</i></a>                            
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
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_name']; ?></label>
                                            <select class="form-control" id="partner_id" name="partner_id">
                                                <option value="">&nbsp;</option>
                                                <!-- <?php foreach($suppliers as $supplier): ?>
                                                <option value="<?php echo $supplier['supplier_id']; ?>" <?php echo ($partner_id == $supplier['supplier_id']?'selected="true"':''); ?>><?php echo $supplier['name']; ?></option>
                                                <?php endforeach; ?> -->
                                            </select>
                                            <label for="partner_id" class="error" style="display: none;"></label>
                                        </div>
                                    </div>
<!--                                     <div class="col-sm-3">
                                         <div class="form-group">
                                             <label><?php echo $lang['ref_document_type']; ?></label>
                                             <select class="form-control" name="ref_document_type_id" id="ref_document_type_id">
                                                 <option value="">&nbsp;</option>
                                                 <option value="4"><?php echo $lang['purchase_order']; ?></option>
                                                <option value="17"><?php echo $lang['goods_received']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['ref_document_no']; ?></label>
                                            <div class="input-group">
                                                <select class="form-control" id="ref_document_identity" name="ref_document_identity">
                                                    <option value="">&nbsp;</option>
                                                </select>
                                                <span class="input-group-btn">
                                                    <button id="addRefDocument" type="button" class="btn btn-info btn-flat"><i class="fa fa-plus"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
                                        </div>
                                    </div>
                                 </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive form-group">
                                            <table id="tblPurchaseInvoice" class="table table-striped table-bordered" >
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="min-width: 90px !important;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <!-- <td style="width: 100px;"><?php echo $lang['document']; ?></td> -->
                                                    <td style="width: 10%;"><?php echo $lang['product_code']; ?></td>
                                                    <td style="width: 20%;"><?php echo $lang['product_name']; ?></td>
                                                    <td style="width: 20%;"><?php echo $lang['product_category']; ?></td>
                                                    <!-- <td style="width: 10%;"><?php echo $lang['warehouse']; ?></td> -->
                                                    <td style="width: 5%;"><?php echo $lang['quantity']; ?></td>
                                                    <!-- <td style="width: 5%;"><?php echo $lang['unit']; ?></td> -->
                                                    <td style="width: 10%;"><?php echo $lang['rate']; ?></td>
                                                    <td style="width: 10%;"><?php echo $lang['sale_rate']; ?></td>
                                                    <td style="width: 10%;"><?php echo $lang['amount']; ?></td>
<!--                                                     <td style="width: 5%;"><?php echo $lang['discount_percent']; ?></td>
                                                    <td style="width: 5%;"><?php echo $lang['discount_amount']; ?></td>
                                                    <td style="width: 5%;"><?php echo $lang['gross_amount']; ?></td>
                                                    <td style="width: 5%;"><?php echo $lang['tax_percent']; ?></td>
                                                    <td style="width: 5%;"><?php echo $lang['tax_amount']; ?></td>
                                                    <td style="width: 5%;"><?php echo $lang['net_amount']; ?></td>
                                                    <td style="width: 10%";><?php echo $lang['remarks']; ?></td> -->
                                                    <td style="min-width: 90px !important;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody >
                                                <?php foreach($purchase_invoice_details as $grid_row => $detail): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td style="min-width: 90px !important;">
                                                        <!--<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a>-->
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                    <td>
                                                        <input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control code1" name="purchase_invoice_details[<?php echo $grid_row; ?>][product_code]" id="purchase_invoice_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                                    </td>
                                                    <td style="min-width: 300px;">
                                                        <div class="input-group">
                                                            <select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2 product code1" id="purchase_invoice_detail_product_id_<?php echo $grid_row; ?>" name="purchase_invoice_details[<?php echo $grid_row; ?>][product_id]" >
                                                                <option value="<?php echo $detail['product_id']; ?>" selected="selected"><?php echo $detail['product_name']; ?></option>
                                                            </select>
                                                            <span class="input-group-btn ">
                                                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_invoice_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                                    <i class="fa fa-search"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="form-control select2 product_category_id" id="purchase_invoice_detail_product_category_id_<?php echo $grid_row; ?>" name="purchase_invoice_details[<?php echo $grid_row; ?>][product_category_id]"
                                                        value="<?php echo $detail['product_category_id']; ?>">
                                                        <input type="text" style="min-width: 100px;" class="form-control select2 product_category" id="purchase_invoice_detail_product_category_<?php echo $grid_row; ?>"
                                                        value="<?php echo $detail['product_category']; ?>"  readonly>
                                                    </td>    
                                                    <td class="hide">
                                                        <select class="form-control select2 warehouse_id" id="purchase_invoice_detail_warehouse_id_<?php echo $grid_row; ?>" name="purchase_invoice_details[<?php echo $grid_row; ?>][warehouse_id]" >
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
                                                        <input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][qty]" id="purchase_invoice_detail_qty_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['qty']; ?>" />
                                                    </td>
                                                    <td class="hide">
                                                        <input type="hidden" class="form-control" name="purchase_invoice_details[<?php echo $grid_row; ?>][unit_id]" id="purchase_invoice_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                                                        <input  style="min-width: 100px;" type="text" class="form-control " name="purchase_invoice_details[<?php echo $grid_row; ?>][unit]" id="purchase_invoice_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][rate]" id="purchase_invoice_detail_rate_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['rate']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input style="min-width: 100px;" type="text" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][sale_rate]" id="purchase_invoice_detail_sale_rate_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['sale_rate']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][amount]" id="purchase_invoice_detail_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td class="hide">
                                                        <input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][discount_percent]" id="purchase_invoice_detail_discount_percent_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['discount_percent']; ?>" />
                                                    </td>
                                                    <td class="hide">
                                                        <input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][discount_amount]" id="purchase_invoice_detail_discount_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['discount_amount']; ?>" />
                                                    </td>
                                                    <td class="hide">
                                                        <input type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][gross_amount]" id="purchase_invoice_detail_gross_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['gross_amount']; ?>" readonly="true"/>
                                                    </td>
<!--                                                     <td>
                                                        <input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][tax_percent]" id="purchase_invoice_detail_tax_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_percent']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][tax_amount]" id="purchase_invoice_detail_tax_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_amount']; ?>" />
                                                    </td> -->
                                                    <td class="hide">
                                                        <input type="text" style="min-width: 100px;" class="form-control fpInteger" name="purchase_invoice_details[<?php echo $grid_row; ?>][total_amount]" id="purchase_invoice_detail_total_amount_<?php echo $grid_row; ?>" value="<?php echo (int) $detail['total_amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td class="hide">
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details[<?php echo $grid_row; ?>][remarks]" id="purchase_invoice_detail_remarks_<?php echo $grid_row; ?>" value="<?php echo $detail['remarks']; ?>" />
                                                    </td>
                                                    <td style="min-width: 90px !important;">
                                                        <!-- <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                        &nbsp;<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a> -->
                                                        &nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php $grid_row = count($purchase_invoice_details); ?>
                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                     <div class="col-sm-4"></div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_quantity']; ?></label>
                                            <input type="text" id="total_quantity" name="total_quantity" value="<?php echo (int) $total_quantity; ?>" class="form-control fDecimal" readonly="true" />
                                        </div>
                                    </div>
                                    <div class=" col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_amount']; ?></label>
                                            <input type="text" id="item_amount" name="item_amount" value="<?php echo (int) $item_amount; ?>" class="form-control fDecimal" readonly="true" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2 hide">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_discount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_discount" name="item_discount" value="<?php echo (int) $item_discount; ?>" readonly="readonly" />
                                        </div>
                                    </div>
<!--                                     <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_tax']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_tax" name="item_tax" value="<?php echo $item_tax; ?>" readonly="readonly" />
                                        </div>
                                    </div>
 -->
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['freight_amount']; ?></label>
                                           <input onchange="calculateTotalFreight()" class="form-control fDecimal" type="text" id="freight_master" name="freight_master" value="<?php echo (int) $freight_master; ?>" />
                                        </div>
                                    </div>

                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_net']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_total" name="item_total" value="<?php echo (int) $item_total; ?>" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
                                    <div class="col-sm-offset-6 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['discount_amount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="discount" name="discount" value="<?php echo (int) $discount; ?>" onchange="calculateTotal();" />
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['net_amount']; ?></label>
                                            <input type="text" id="net_amount" name="net_amount" value="<?php echo (int) $net_amount; ?>" class="form-control fDecimal" readonly="readonly" />
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
                               <a class="btn btn-info" target="_blank" href="<?php echo $action_print_barcode; ?>">
                                <i class="fa fa-print"></i>
                                &nbsp;<?php echo $lang['print_barcode']; ?>
                               </a>

                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print_purchase_invoice; ?>">
                                 <i class="fa fa-print"></i>
                                 &nbsp;<?php echo $lang['print_purchase_invoice']; ?>
                                 </a>

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
    <script type="text/javascript" src="../admin/view/js/inventory/purchase_invoice.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $lang = <?php echo json_encode($lang) ?>;
        var $partner_id = '<?php echo $partner_id; ?>';
        var $grid_row = '<?php echo $grid_row; ?>';

        var $UrlGetRefDocumentNo = '<?php echo $href_get_ref_document_no; ?>';
        var $UrlGetRefDocument = '<?php echo $href_get_ref_document; ?>';
        var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
        var $UrlCheckExistProduct = '<?php echo $href_check_exists_product; ?>';
       
        var $warehouses = <?php echo json_encode($warehouses) ?>;
        var $product_categories = <?php echo json_encode($product_categories) ?>;
        var $products = <?php echo json_encode($products) ?>;

        var $UrlGetPartnerJSON = '<?php echo $href_get_partner_json; ?>';


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

        <?php if($this->request->get['purchase_invoice_id']): ?>
        $(document).ready(function() {
            // alert($grid_row);
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