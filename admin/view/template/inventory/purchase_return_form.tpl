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
                            <input type="hidden" value="<?php echo $purchase_return_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" id="document_identity" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="<?php echo $lang['auto'];?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
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
                                            </select>
                                            <label for="partner_id" class="error" style="display: none;"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['ref_document_type']; ?></label>
                                            <select class="form-control" name="ref_document_type_id" id="ref_document_type_id">
                                                <option value="">&nbsp;</option>
                                                <!-- <option value="4"><?php echo $lang['purchase_order']; ?></option> -->
                                                <option value="17"><?php echo $lang['goods_received']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['ref_document_no']; ?></label>
                                            <select class="form-control" id="ref_document_identity" name="ref_document_identity">
                                                <option value="">&nbsp;</option>
                                            </select>
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
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks2']; ?></label>
                                            <input class="form-control" type="text" name="remarks2" value="<?php echo $remarks2; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive form-group">
                                            <table id="tblPurchaseInvoice" class="table table-striped table-bordered">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 3px;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td style="width: 100px;"><?php echo $lang['document']; ?></td>
                                                    <td style="width: 100px;"><?php echo $lang['product_code']; ?></td>
                                                    <td style="width: 300px;"><?php echo $lang['product_name']; ?></td>
                                                    <td style="width: 200px;"><?php echo $lang['warehouse']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['rate']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['amount']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['discount_percent']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['discount_amount']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['gross_amount']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['tax_percent']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['tax_amount']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['total_amount']; ?></td>
                                                    <td style="width: 120px";><?php echo $lang['remarks']; ?></td>
                                                    <td style="width: 3px;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody >
                                                <?php
                                                $grid_row = 0; 
                                                 foreach($purchase_return_details as $details => $detail): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                    <td>
                                                        <input type="hidden" name="purchase_return_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="purchase_return_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" />
                                                        <input type="hidden" name="purchase_return_details[<?php echo $grid_row; ?>][ref_document_identity]" id="purchase_return_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>" />
                                                        <a target="_blank" href="<?php echo $detail['href']; ?>"><?php echo $detail['ref_document_identity']; ?></a>
                                                    </td>
                                                    <td>
                                                        <input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details[<?php echo $grid_row; ?>][product_code]" id="purchase_return_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <!-- <select onchange="getProductById(this);" class="form-control select2 product code1" id="purchase_return_detail_product_id_<?php echo $grid_row; ?>" name="purchase_return_details[<?php echo $grid_row; ?>][product_id]" >
                                                                <option value="<?php echo $detail['product_id']; ?>" selected="selected"><?php echo $detail['product_name']; ?></option>
                                                            </select> -->





                                                            <select onchange="getProductById(this);" class="form-control select2 product code1" id="purchase_return_detail_product_id_<?php echo $grid_row; ?>" name="purchase_return_details[<?php echo $grid_row; ?>][product_id]" >
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
                                                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_return_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                                    <i class="fa fa-search"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <select class="form-control select2 warehouse_id" id="purchase_return_detail_warehouse_id_<?php echo $grid_row; ?>" name="purchase_return_details[<?php echo $grid_row; ?>][warehouse_id]" >
                                                            <option value="">&nbsp;</option>
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
                                                        <input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][qty]" id="purchase_return_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                                        <input style="min-width: 100px;" type="hidden" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][purchase_qty]" id="purchase_return_detail_purchase_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details[<?php echo $grid_row; ?>][unit]" id="purchase_return_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly="true" />
                                                        <input type="hidden" class="form-control" name="purchase_return_details[<?php echo $grid_row; ?>][unit_id]" id="purchase_return_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][rate]" id="purchase_return_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][amount]" id="purchase_return_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][discount_percent]" id="purchase_return_detail_discount_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['discount_percent']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][discount_amount]" id="purchase_return_detail_discount_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['discount_amount']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][gross_amount]" id="purchase_return_detail_gross_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['gross_amount']; ?>" readonly="true"/>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][tax_percent]" id="purchase_return_detail_tax_percent_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_percent']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][tax_amount]" id="purchase_return_detail_tax_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['tax_amount']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details[<?php echo $grid_row; ?>][total_amount]" id="purchase_return_detail_total_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['total_amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details[<?php echo $grid_row; ?>][remarks]" id="purchase_return_detail_remarks_<?php echo $grid_row; ?>" value="<?php echo $detail['remarks']; ?>" />
                                                    </td>
                                                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                </tr>
                                                <?php $grid_row++; endforeach; ?>

                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['item_amount']; ?></label>
                                            <input type="text" id="item_amount" name="item_amount" value="<?php echo $item_amount; ?>" class="form-control fDecimal" readonly="true" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['item_discount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_discount" name="item_discount" value="<?php echo $item_discount; ?>" readonly="readonly" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['item_tax']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_tax" name="item_tax" value="<?php echo $item_tax; ?>" readonly="readonly" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['item_total']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="item_total" name="item_total" value="<?php echo $item_total; ?>" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-offset-6 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['deduction_amount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="deduction_amount" name="deduction_amount" value="<?php echo $deduction_amount; ?>" onchange="calculateTotal();" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['net_amount']; ?></label>
                                            <input type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fDecimal" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-offset-6 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['cash_received']; ?></label>
                                            <input type="text" id="cash_received" name="cash_received" value="<?php echo $cash_received; ?>" class="form-control fDecimal" placeholder="0.00" onchange="calculateTotal();" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['balance_amount']; ?></label>
                                            <input type="text" id="balance_amount" name="balance_amount" value="<?php echo $balance_amount; ?>" class="form-control fDecimal"  readonly="readonly" />
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
    <script type="text/javascript" src="../admin/view/js/inventory/purchase_return.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $lang = <?php echo json_encode($lang) ?>;
        var $partner_id = '<?php echo $partner_id; ?>';
        var $ref_document_type_id = '<?php echo $ref_document_type_id; ?>';
        var $ref_document_identity = '<?php echo $ref_document_identity; ?>';
        var $grid_row = '<?php echo $grid_row; ?>';
        var $UrlGetRefDocumentNo = '<?php echo $href_get_ref_document_no; ?>';
        var $UrlGetRefDocument = '<?php echo $href_get_ref_document; ?>';
        var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';

         var $UrlGetPartnerJSON = '<?php echo $href_get_partner_json; ?>';

        // alert($UrlGetProductJSON);
        var $products = <?php echo json_encode($products) ?>;
        var $warehouses = <?php echo json_encode($warehouses) ?>;

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



        <?php if($this->request->get['purchase_return_id']): ?>
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