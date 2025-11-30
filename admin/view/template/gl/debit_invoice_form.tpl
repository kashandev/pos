<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <link rel="stylesheet" type="text/css" href="bootstrap-switch/css/bootstrap-switch.css">
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
                            <?php if ($error_warning): ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $error_warning; ?>
                            </div>
                            <?php elseif ($success): ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?>
                            </div>
                            <?php endif; ?>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                            <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input type="text" name="document_identity" value="<?php echo $document_identity; ?>" class="form-control" readonly="true" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_date']; ?></label>
                                            <input  type="text" class="dtpDate form-control" name="document_date" id="document_date" value="<?php echo $document_date; ?>" autocomplete="off" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['sale_tax_invoice']; ?></label>
                                            <div class="form-group">
                                                <input type="checkbox" class="form-control" data-on-text="Yes" data-off-text="No" name="sale_tax_invoice" id="sale_tax_invoice" value="Yes" <?php echo ($sale_tax_invoice=='Yes'?'checked="true"':''); ?> />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['manual_ref_no']; ?></label>
                                            <input type="text" class="form-control" name="manual_ref_no" id="manual_ref_no" value="<?php echo $manual_ref_no; ?>" autocomplete="off"  <?php echo ($sale_tax_invoice=='Yes'?'disabled="true"':''); ?> />
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
                                                <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="selected"':''); ?>><?php echo $partner_type['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="partner_type_id" class="error" style="display: none;">&nbsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner']; ?></label>
                                            <select class="form-control" id="partner_id" name="partner_id">
                                                <option value="">&nbsp;</option>
                                            </select>
                                            <label for="partner_id" class="error" style="display: none;">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input type="text" name="remarks" value="<?php echo $remarks; ?>" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="tblDebitInvoiceDetail" class="table table-striped table-bordered">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 5%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td style="width: 25%;"><?php echo $lang['account']; ?></td>
                                                    <td style="width: 25%;"><?php echo $lang['remarks']; ?></td>
                                                    <td style="width: 10%;"><?php echo $lang['quantity']; ?></td>
                                                    <td style="width: 15%;"><?php echo $lang['rate']; ?></td>
                                                    <td style="width: 15%;"><?php echo $lang['amount']; ?></td>
                                                    <td style="width: 5%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row = 0;?>
                                                <?php foreach($debit_invoice_details as $detail): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                    <td>
                                                        <select class="coa_id form-control" id="debit_invoice_detail_coa_id_<?php echo $grid_row; ?>" name="debit_invoice_details[<?php echo $grid_row; ?>][coa_id]" >
                                                            <?php foreach($coas as $coa): ?>
                                                            <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$detail['coa_id']?'selected="true"':''); ?> ><?php echo $coa['level3_display_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="debit_invoice_details[<?php echo $grid_row; ?>][remarks]" id="debit_invoice_detail_remarks_<?php echo $grid_row; ?>" value="<?php echo $detail[remarks]; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(this);" type="text" class="form-control" name="debit_invoice_details[<?php echo $grid_row; ?>][quantity]" id="debit_invoice_detail_quantity_<?php echo $grid_row; ?>" value="<?php echo $detail[quantity]; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(this);" type="text" class="form-control" name="debit_invoice_details[<?php echo $grid_row; ?>][rate]" id="debit_invoice_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail[rate]; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateTotal(this);" type="text" class="form-control" name="debit_invoice_details[<?php echo $grid_row; ?>][amount]" id="debit_invoice_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail[amount]; ?>" />
                                                    </td>
                                                    <td>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
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
                                    <div class="col-sm-offset-9 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_amount']; ?></label>
                                            <input type="text" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" class="form-control text-right" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['tax_account']; ?></label>
                                            <select class="form-control" id="tax_account_id" name="tax_account_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id'];?>" <?php echo ($tax_account_id==$coa['coa_level3_id']?'selected="true"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['tax_percent']; ?></label>
                                            <input type="text" id="tax_percent" name="tax_percent" value="<?php echo $tax_percent; ?>" class="form-control text-right" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['tax_amount']; ?></label>
                                            <input type="text" id="tax_amount" name="tax_amount" value="<?php echo $tax_amount; ?>" class="form-control text-right" readonly />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['net_amount']; ?></label>
                                            <input type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control text-right" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row <?php echo (count($currencies)==1?'hide':''); ?>">
                                    <div class="col-sm-offset-9 col-sm-3">
                                        <div class="form-group">
                                            <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                                            <label><?php echo $lang['document_currency']; ?></label>
                                            <select class="form-control" id="document_currency_id" name="document_currency_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($currencies as $currency): ?>
                                                <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row <?php echo (count($currencies)==1?'hide':''); ?>">
                                    <div class="col-sm-offset-9 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['conversion_rate']; ?></label>
                                            <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row <?php echo (count($currencies)==1?'hide':''); ?>">
                                    <div class="col-sm-offset-9 col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['base_amount']; ?></label>
                                            <input class="form-control fDecimal" id="base_amount" type="text" name="base_amount" value="<?php echo $base_amount; ?>" readonly/>
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
    
    <link rel="stylesheet" href="plugins/bootstrap-switch/css/bootstrap-switch.css">
    <script type="text/javascript" src="plugins/bootstrap-switch/js/bootstrap-switch.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        $('#sale_tax_invoice').bootstrapSwitch();
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $partner_id = '<?php echo $partner_id; ?>';
        var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
        var $UrlGetDocumentLedger = '<?php echo $href_get_document_ledger; ?>';
        var $grid_row = '<?php echo $grid_row; ?>';
        var $lang = <?php echo json_encode($lang) ?>;
        var $coas = <?php echo json_encode($coas) ?>;
        var $partners = [];
        <?php if($this->request->get['debit_invoice_id']): ?>
        $(document).ready(function() {
            $('#partner_type_id').trigger('change');
        });
        <?php endif; ?>
    </script>

    <script type="text/javascript" src="../admin/view/js/gl/debit_invoice.js"></script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>