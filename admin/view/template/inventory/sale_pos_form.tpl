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
                        <!--<button type="button" id="btnsave" class="btn btn-primary" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>-->
                        <a  class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                        <i class="fa fa-floppy-o"></i>
                        &nbsp;<?php echo $lang['save']; ?>
                        </a>
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
                                <input type="hidden" value="<?php echo $sale_pos_id; ?>" name="document_id" id="document_id" />
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
                                            <input class="form-control dtpDate" type="text" id="document_date" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3 hide">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_type']; ?></label>
                                            <select class="form-control select2-default" id="partner_type_id" name="partner_type_id">
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
                                            <select class="form-control select2-default" id="partner_id" name="partner_id">
                                                <?php if(count($partners) != 1): ?>
                                                <option value="">&nbsp;</option>
                                                <?php endif; ?>
                                                <?php foreach($partners as $partner): ?>
                                                <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner_id == $partner['partner_id']?'selected="true"':''); ?>><?php echo $partner['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="partner_id" class="error" style="display: none;"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['warehouse']; ?></label>
                                            <select class="form-control select2-default" id="warehouse_id" name="warehouse_id">
                                                <?php if(count($warehouses)!=1): ?>
                                                <option value="">&nbsp;</option>
                                                <?php endif; ?>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse['warehouse_id']==$warehouse_id?'selected="true"':'');?>><?php echo $warehouse['name']; ?></option>
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
                                <div class="row hide">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['ref_document_type']; ?></label>
                                            <select class="form-control" name="ref_document_type_id" id="ref_document_type_id">
                                                <option value="">&nbsp;</option>
                                                <!-- <option value="26"><?php echo $lang['sale_inquiry']; ?></option> -->
                                                <option value="16"><?php echo $lang['delivery_challan']; ?></option>
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
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['customer']; ?></label>
                                            <input class="form-control" type="text" name="customer" value="<?php echo $customer; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['contact_no']; ?></label>
                                            <input class="form-control" type="text" name="contact_no" value="<?php echo $contact_no; ?>" />
                                        </div>
                                    </div>
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
                                            <label>&nbsp;<?php echo $lang['salesman']; ?></label>
                                            <select class="form-control" id="salesman_id" name="salesman_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($salesmans as $salesman): ?>
                                                <option value="<?php echo $salesman['salesman_id']; ?>" <?php echo ($salesman_id == $salesman['salesman_id']?'selected="selected"':''); ?>><?php echo $salesman['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_code']; ?></label>
                                            <input class="form-control" type="text" id="product_code" name="product_code" value="" />
                                            <input class="form-control" type="hidden" id="unit_id" name="unit_id" value="" />
                                            <input class="form-control" type="hidden" id="unit" name="unit" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_name']; ?></label>
                                            <select data-callback="fillGrid" data-unit_id="unit_id" data-unit="unit" data-rate="sale_price" class="form-control select2-product" id="product_id" name="product_id">
                                                <?php foreach($products as $product): ?>
                                                <option value="<?php echo $product['product_name']; ?>"><?php echo $product['product_id']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <!--<div class="col-sm-3">
                                        <input type="button" value="click" onclick="myfunction()">
                                    </div>-->
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive form-group">
                                            <table id="tblPOSInvoice" class="table table-striped table-bordered">
                                                <thead>
                                                <tr align="center">
                                                    <td style="width: 3px;">&nbsp;</td>
                                                    <td style="width: 100px;"><?php echo $lang['product_code']; ?></td>
                                                    <td style="width: 300px;"><?php echo $lang['product_name']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['stock']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['rate']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['discount_percent']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['discount_amount']; ?></td>
                                                    <td style="width: 120px;"><?php echo $lang['net_amount']; ?></td>
                                                    <td style="width: 3px;">&nbsp;</td>
                                                </tr>
                                                </thead>
                                                <tbody >
                                                <?php foreach($sale_pos_details as $row_id => $detail): ?>
                                                <tr id="row_id_<?php echo $row_id; ?>" data-row_id="<?php echo $row_id; ?>" data-product_id="<?php echo $detail['product_id']; ?>">
                                                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][product_code]" id="sale_pos_detail_<?php echo $row_id; ?>_product_code" value="<?php echo $detail['product_code']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][product_name]" id="sale_pos_detail_<?php echo $row_id; ?>_product_name" value="<?php echo $detail['product_name']; ?>" readonly/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][product_id]" id="sale_pos_detail_<?php echo $row_id; ?>_product_id" value="<?php echo $detail['product_id']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(<?php echo $row_id; ?>);" type="text" style="min-width: 100px;" class="form-control text-right" name="sale_pos_details[<?php echo $row_id; ?>][qty]" id="sale_pos_detail_<?php echo $row_id; ?>_qty" value="<?php echo $detail['qty']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][unit]" id="sale_pos_detail_<?php echo $row_id; ?>_unit" value="<?php echo $detail['unit']; ?>" readonly/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][unit_id]" id="sale_pos_detail_<?php echo $row_id; ?>_unit_id" value="<?php echo $detail['unit_id']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][stock]" id="sale_pos_detail_<?php echo $row_id; ?>_stock" value="<?php echo $detail['stock']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control text-right" name="sale_pos_details[<?php echo $row_id; ?>][rate]" id="sale_pos_detail_<?php echo $row_id; ?>_rate" value="<?php echo $detail['rate']; ?>" readonly/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][cog_rate]" id="sale_pos_detail_<?php echo $row_id; ?>_cog_rate" value="<?php echo $detail['cog_rate']; ?>" readonly/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][cog_amount]" id="sale_pos_detail_<?php echo $row_id; ?>_cog_amount" value="<?php echo $detail['cog_amount']; ?>" readonly/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control fPDecimal" name="sale_pos_details[<?php echo $row_id; ?>][amount]" id="sale_pos_detail_<?php echo $row_id; ?>_amount" value="<?php echo $detail['amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal text-right" name="sale_pos_details[<?php echo $row_id; ?>][discount_percent]" id="sale_pos_detail_<?php echo $row_id; ?>_discount_percent" value="<?php echo $detail['discount_percent']; ?>" readonly />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(<?php echo $row_id; ?>);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="sale_pos_details[<?php echo $row_id; ?>][discount_amount]" id="sale_pos_detail_<?php echo $row_id; ?>_discount_amount" value="<?php echo $detail['discount_amount']; ?>" readonly />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fPDecimal text-right" name="sale_pos_details[<?php echo $row_id; ?>][gross_amount]" id="sale_pos_detail_<?php echo $row_id; ?>_gross_amount" value="<?php echo $detail['gross_amount']; ?>" readonly="true"/>
                                                        <input type="hidden" style="min-width: 100px;" class="form-control" name="sale_pos_details[<?php echo $row_id; ?>][total_amount]" id="sale_pos_detail_<?php echo $row_id; ?>_total_amount" value="<?php echo $detail['total_amount']; ?>" readonly/>
                                                    </td>
                                                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php $row_id = count($sale_pos_details); ?>
                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
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
                                    <div class="col-sm-offset-3 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_quantity']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="total_quantity" name="total_quantity" value="<?php echo $total_quantity; ?>" onchange="calculateNetAmount();" />
                                        </div>
                                    </div>
                                    <div class=" col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['return_amount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="return_amount" name="return_amount" value="<?php echo $return_amount; ?>" onchange="calculateNetAmount();" />
                                        </div>
                                    </div>

                                    <div class="col-sm-offset-0 col-sm-3 hide">
                                        <div class="form-group">
                                            <label><?php echo $lang['discount_amount']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="discount" name="discount" value="<?php echo $discount; ?>" onchange="calculateTotal();" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['net_amount']; ?></label>
                                            <input type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fDecimal" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
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
                                <a  class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                                    <i class="fa fa-floppy-o"></i>
                                    &nbsp;<?php echo $lang['save']; ?>
                                </a>
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
    <script type="text/javascript" src="dist/js/pages/inventory/sale_pos.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript" src="plugins/typeahead/typeahead.min.js"></script>
    <link rel="stylesheet" href="plugins/printJS/print.min.css">
    <script type="text/javascript" src="plugins/printJS/print.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $UrlDiscountPolicy = '<?php echo $href_discount_policy; ?>';
        var $lang = <?php echo json_encode($lang) ?>;
        var $partner_id = '<?php echo $partner_id; ?>';
        var $row_id = '<?php echo $row_id; ?>';
        var $warehouses = <?php echo json_encode($warehouses) ?>;
        <?php if(isset($this->request->get['printInvoice'])): ?>
        $(document).ready(function() {
            printJS({printable:"<?php echo $action_print; ?>", type:'pdf', showModal:true})
            //window.open("<?php echo $action_print; ?>", '_blank');
        })
        <?php endif; ?>
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>