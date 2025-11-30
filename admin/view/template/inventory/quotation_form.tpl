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
                    <div class="col-sm-4">
                        <ol class="breadcrumb">
                            <?php foreach ($breadcrumbs as $breadcrumb) : ?>
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
                            <?php if (isset($isEdit) && $isEdit == 1) : ?>
                                <?php if ($is_post == 0) : ?>
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
                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print_header_wise; ?>">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print_with_header']; ?>
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
                            <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post == 1 ? 'disabled="true"' : ''); ?>>
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
                                <?php if ($success) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                        <?php echo $success; ?>
                                    </div>
                                <?php  } ?>
                            </div><!-- /.box-header -->
                            <div class="box-body">
                                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                    <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                                    <input type="hidden" value="<?php echo $quotation_id; ?>" name="document_id" id="document_id" />
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
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $lang['partner_name']; ?></label>
                                                <select class="form-control" id="partner_id" name="partner_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($partners as $partner) : ?>
                                                        <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner_id == $partner['partner_id'] ? 'selected="true"' : ''); ?>><?php echo $partner['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="partner_id" class="error" style="display: none;"></label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row hide">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['partner_type']; ?></label>
                                                <select class="form-control" id="partner_type_id" name="partner_type_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($partner_types as $partner_type) : ?>
                                                        <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id'] ? 'selected="true"' : ''); ?>><?php echo $partner_type['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['base_currency']; ?></label>
                                                <input type="hidden" id="base_currency_id" name="base_currency_id" value="<?php echo $base_currency_id; ?>" />
                                                <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['document_currency']; ?></label>
                                                <select class="form-control" id="document_currency_id" name="document_currency_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($currencys as $currency) : ?>
                                                        <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id'] ? 'selected="selected"' : ''); ?>><?php echo $currency['name']; ?></option>
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
                                        <!--<div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $lang['ref_document_type']; ?></label>
                                    <select class="form-control" name="ref_document_type_id" id="ref_document_type_id">
                                        <option value="">&nbsp;</option>
                                        <option value="26" <?php echo ($ref_document_type_id == 26 ? 'selected="selected"' : ''); ?>><?php echo $lang['sale_inquiry']; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <label><?php echo $lang['ref_document_no']; ?></label>
                                <div class="form-group">
                                    <select class="form-control" id="ref_document_id" name="ref_document_id" onchange="getReferenceDocument();">
                                        <option value="">&nbsp;</option>
                                        <?php foreach ($ref_documents as $ref_document) : ?>
                                        <?php if ($ref_document['manual_ref_no'] == "") : ?>
                                        <option value="<?php echo $ref_document['document_id']; ?>" <?php echo ($ref_document['document_id'] == $ref_document_id ? 'selected="true"' : ''); ?>><?php echo $ref_document['document_identity']; ?></option>
                                        <?php else : ?>
                                        <option value="<?php echo $ref_document['document_id']; ?>" <?php echo ($ref_document['document_id'] == $ref_document_id ? 'selected="true"' : ''); ?>><?php echo $ref_document['document_identity'], ' ', '(', $ref_document['manual_ref_no'], ')'; ?></option>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>-->
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['customer_ref_no']; ?></label>
                                                <input class="form-control" type="text" id="customer_ref_no" name="customer_ref_no" value="<?php echo $customer_ref_no; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['customer_date']; ?></label>
                                                <input class="form-control dtpDate" type="text" id="customer_date" name="customer_date" value="<?php echo $customer_date; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['due_date']; ?></label>
                                                <input class="form-control dtpDate" type="text" id="due_date" name="due_date" value="<?php echo $due_date; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['attn']; ?></label>
                                                <input class="form-control" type="text" id="attn" name="attn" value="<?php echo $attn; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row hide">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $lang['ref_no']; ?></label>
                                                <input class="form-control" type="text" name="ref_no" id="ref_no" value="<?php echo $ref_no; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['salesman']; ?></label>
                                                <select class="form-control" id="salesman_id" name="salesman_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($salesmans as $salesman) : ?>
                                                        <option value="<?php echo $salesman['salesman_id']; ?>" <?php echo ($salesman_id == $salesman['salesman_id'] ? 'selected="selected"' : ''); ?>><?php echo $salesman['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                        <div class="form-group">
                                                <label><?php echo $lang['delivery']; ?></label>
                                                <input class="form-control" type="text" name="delivery" id="delivery" value="<?php echo $delivery; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                        <div class="form-group">
                                                <label><?php echo $lang['validity']; ?></label>
                                                <input class="form-control" type="text" name="validity" id="validity" value="<?php echo $validity; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                        <div class="form-group">
                                                <label><?php echo $lang['payment']; ?></label>
                                                <input class="form-control" type="text" name="payment" id="payment" value="<?php echo $payment; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                        <div class="form-group">
                                                <label><?php echo $lang['enclosure']; ?></label>
                                                <input class="form-control" type="text" name="enclosure" id="enclosure" value="<?php echo $enclosure; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                        <div class="form-group">
                                                <label><?php echo $lang['other']; ?></label>
                                                <input class="form-control" type="text" name="other" id="other" value="<?php echo $other; ?>" />
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><?php echo $lang['address']; ?></label>
                                                <input class="form-control" type="text" name="address" id="address" value="<?php echo $address; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['terms']; ?></label>
                                                <select class="form-control " multiple="multiple" id="term_id" name="term_id[]" size="1">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($terms as $term) : ?>
                                                        <option value="<?php echo $term['term_id']; ?>" <?php echo (in_array($term['term_id'], $term_id) ? 'selected="selected"' : ''); ?>><?php echo $term['term']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['terms']; ?></label>
                                                <textarea rows="4" cols="50" class="form-control" type="text" id="term_desc" name="term_desc"><?php echo $term_desc ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="table-responsive form-group">
                                                <table id="tblQuotation" class="table table-striped table-bordered" style="width: 2000px !important;max-width: 2000px !important;">
                                                    <thead>
                                                        <tr align="center">
                                                            <td style="width: 90px;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                            <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                                                            <td style="width: 250px;"><?php echo $lang['product_name']; ?></td>
                                                            <td style="width: 250px;"><?php echo $lang['description']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['delivery']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['stock_qty']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['unit']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['rate']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['tax_percent']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['tax_amount']; ?></td>
                                                            <td style="width: 120px;"><?php echo $lang['net_amount']; ?></td>

                                                            <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $grid_row = 0; ?>
                                                        <?php foreach ($quotation_details as $detail) : ?>
                                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                                <td>
                                                                    <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                                    <a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                                </td>
                                                                <td>
                                                                    <input onchange="getProductByCode(this);" type="text" class="form-control" name="quotation_details[<?php echo $grid_row; ?>][product_code]" id="quotation_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <div class="input-group">
                                                                        <select onchange="getProductById(this);" class="form-control select2 product code1" id="quotation_detail_product_id_<?php echo $grid_row; ?>" name="quotation_details[<?php echo $grid_row; ?>][product_id]">
                                                                            <!-- <option value="">&nbsp;</option>
                                                    <?php foreach ($products as $product) : ?>
                                                    <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id'] == $detail['product_id'] ? 'selected="true"' : ''); ?>><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?> -->
                                                                            <option value="">&nbsp;</option>
                                                                            <?php foreach ($products as $product) : ?>
                                                                                <?php if ($product['product_id'] == $detail['product_id']) : ?>
                                                                                    <option value="<?php echo $product['product_id']; ?>" selected="true"><?php echo $product['name']; ?></option>
                                                                                <?php else : ?>
                                                                                    <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                                                                <?php endif; ?>
                                                                            <?php endforeach; ?>
                                                                            <option value="<?php echo $detail['product_id']; ?>" selected="selected"><?php echo $detail['product_name']; ?></option>
                                                                        </select>
                                                                        <span class="input-group-btn ">
                                                                            <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="quotation_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                                                <i class="fa fa-search"></i>
                                                                            </button>
                                                                        </span>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="quotation_details[<?php echo $grid_row; ?>][description]" id="quotation_detail_description_<?php echo $grid_row; ?>" value="<?php echo $detail['description']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="quotation_details[<?php echo $grid_row; ?>][delivery]" id="quotation_detail_delivery_<?php echo $grid_row; ?>" value="<?php echo $detail['delivery']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="quotation_details[<?php echo $grid_row; ?>][stock_qty]" id="quotation_detail_stock_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['stock_qty']; ?>" readonly/>
                                                                </td>
                                                                <td>
                                                                    <input type="text" onchange="calculateAmount(this);" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][qty]" id="quotation_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" readonly class="form-control" name="quotation_details[<?php echo $grid_row; ?>][unit]" id="quotation_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" />
                                                                    <input type="hidden" readonly class="form-control" name="quotation_details[<?php echo $grid_row; ?>][unit_id]" id="quotation_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />

                                                                    <!-- <select  class="form-control select2" id="quotation_detail_unit_id_<?php echo $grid_row; ?>" name="quotation_details[<?php echo $grid_row; ?>][unit_id]" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach ($units as $unit) : ?>
                                                <option value="<?php echo $unit['unit_id']; ?>" <?php echo ($unit['unit_id'] == $detail['unit_id'] ? 'selected="true"' : ''); ?>><?php echo $unit['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select> -->
                                                                </td>
                                                                <td>
                                                                    <input type="text" onchange="calculateAmount(this);" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][rate]" id="quotation_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][amount]" id="quotation_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input onchange="calculateTaxAmount(this);" type="text" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][tax_percent]" id="quotation_detail_tax_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_percent']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input onchange="calculateTaxAmount(this);" type="text" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][tax_amount]" id="quotation_detail_tax_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_amount']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control fDecimal" name="quotation_details[<?php echo $grid_row; ?>][net_amount]" id="quotation_detail_net_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['net_amount']; ?>" />
                                                                </td>
                                                                <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
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
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-offset-4 col-sm-2">
                                            <div class="form-group">
                                                <label><?php echo $lang['total_quantity']; ?></label>
                                                <input type="text" id="total_quantity" name="total_quantity" value="<?php echo number_format($total_quantity,2); ?>" class="form-control fDecimal" readonly="true" />
                                            </div>
                                        </div>
                                        <div class=" col-sm-2">
                                            <div class="form-group">
                                                <label><?php echo $lang['total_amount']; ?></label>
                                                <input type="text" id="item_amount" name="item_amount" value="<?php echo $item_amount; ?>" class="form-control fDecimal" readonly="true" />
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <label><?php echo $lang['total_tax']; ?></label>
                                                <input class="form-control fDecimal" type="text" id="item_tax" name="item_tax" value="<?php echo $item_tax; ?>" readonly="readonly" />
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="form-group">
                                                <label><?php echo $lang['total_net']; ?></label>
                                                <input class="form-control fDecimal" type="text" id="item_total" name="item_total" value="<?php echo $item_total; ?>" readonly="readonly" />
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="box-footer">
                                <div class="pull-right">
                                    <?php if (isset($isEdit) && $isEdit == 1) : ?>
                                        <?php if ($is_post == 0) : ?>
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
                                    <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post == 1 ? 'disabled="true"' : ''); ?>>
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
        <script type="text/javascript" src="../admin/view/js/inventory/quotation.js"></script>
        <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
        <script>
            jQuery('#form').validate(<?php echo $strValidation; ?>);
            var $restrict_out_of_stock = '<?php echo $restrict_out_of_stock; ?>';
            var $lang = <?php echo json_encode($lang) ?>;
            var $partner_id = '<?php echo $partner_id; ?>';
            var $grid_row = '<?php echo $grid_row; ?>';
            var $products = <?php echo json_encode($products) ?>;
            var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
            var $units = <?php echo json_encode($units) ?>;
            //    var $URLGetExcel = '<?php echo $href_get_excel_figures; ?>';

            function formatRepo(repo) {
                if (repo.loading) return repo.text;

                var markup = "<div class='select2-result-repository clearfix'>";
                if (repo.image_url) {
                    markup += "<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
                }
                markup += "<div class='select2-result-repository__meta'>";
                markup += "  <div class='select2-result-repository__title'>" + repo.name + "</div>";

                if (repo.description) {
                    markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
                }

                markup += "<div class='select2-result-repository__statistics'>" +
                    "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
                    "</div>" +
                    "</div></div>";

                return markup;
            }

            function formatRepoSelection(repo) {
                return repo.name || repo.text;
            }

            <?php if ($this->request->get['quotation_id']) : ?>
                $(document).ready(function() {
                    $('#partner_type_id').trigger('change');
                    $('select.product').select2({
                        width: '100%',
                        ajax: {
                            url: $UrlGetProductJSON,
                            dataType: 'json',
                            type: 'post',
                            mimeType: "multipart/form-data",
                            delay: 250,
                            data: function(params) {
                                return {
                                    q: params.term, // search term
                                    page: params.page
                                };
                            },
                            processResults: function(data, params) {
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
                        escapeMarkup: function(markup) {
                            return markup;
                        }, // let our custom formatter work
                        minimumInputLength: 2,
                        templateResult: formatRepo, // omitted for brevity, see the source of this page
                        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
                    });

                    calculateTotal();
                });
            <?php endif; ?>
        </script>
        <?php echo $page_footer; ?>
        <?php echo $column_right; ?>
    </div><!-- ./wrapper -->
    <?php echo $footer; ?>
</body>

</html>