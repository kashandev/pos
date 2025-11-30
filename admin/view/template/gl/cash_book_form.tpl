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
            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
        </div>
    </div>
    <!--<div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['cash_account']; ?></label>
            <select class="form-control" id="cash_account_id" name="cash_account_id" >
                <option value="">&nbsp;</option>
                <?php foreach($cash_accounts as $cash_account): ?>
                <option value="<?php echo $cash_account['coa_level3_id']; ?>" <?php echo ($cash_account_id == $cash_account['coa_level3_id']?'selected="selected"':''); ?>"><?php echo $cash_account['level3_display_name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="cash_account_id" class="error" style="display: none;">&nbsp;</label>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['bank_account']; ?></label>
            <select class="form-control" id="bank_account_id" name="bank_account_id" >
                <option value="">&nbsp;</option>
                <?php foreach($bank_accounts as $bank_account): ?>
                <option value="<?php echo $bank_account['coa_level3_id']; ?>" <?php echo ($bank_account_id == $bank_account['coa_level3_id']?'selected="selected"':''); ?>><?php echo $bank_account['level3_display_name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="bank_account_id" class="error" style="display: none;">&nbsp;</label>
        </div>
    </div>-->
    <div class="col-sm-6">
        <div class="form-group">
            <label><?php echo $lang['remarks']; ?></label>
            <input type="text" class="form-control" name="remarks" value="<?php echo $remarks; ?>" />
        </div>
    </div>

</div>
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            <label><?php echo $lang['partner']; ?></label>
            <select class="form-control" id="partner_id" name="partner_id">
                <option value="">&nbsp;</option>
                <?php foreach($partners as $partner): ?>
                <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner_id == $partner['partner_id']?'selected="true"':''); ?>><?php echo $partner['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="partner_id" class="error" style="display: none;">&nbsp;</label>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label id="lblRef"><?php echo $lang['ref_document_no']; ?></label>
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
<div class="row hide">
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $lang['base_currency']; ?></label>
            <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
            <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $lang['document_currency']; ?></label>
            <select class="form-control" id="document_currency_id" name="document_currency_id" onchange="getConversionRate();">
                <option value="">&nbsp;</option>
                <?php foreach($currencies as $currency): ?>
                <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            <label><?php echo $lang['conversion_rate']; ?></label>
            <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="table-responsive form-group">
            <table id="tblCashBook" class="table table-striped table-bordered">
                <thead>
                <tr align="center" data-row_id="H">
                    <td></td>
                    <td style="width: 30%;"><?php echo $lang['partner_name']; ?></td>
                    <td style="width: 20%;"><?php echo $lang['document_no']; ?></td>
                    <!--<td><?php echo $lang['type']; ?></td>
                    <td style="width: 8%;"><?php echo $lang['cheque_no']; ?></td>
                    <td style="width: 8%;"><?php echo $lang['remarks']; ?></td>-->
                    <td style="width: 15%;"><?php echo $lang['po_no']; ?></td>
                    <td style="width: 15%;"><?php echo $lang['dc_no']; ?></td>
                    <td style="width: 15%;"><?php echo $lang['document_amount']; ?></td>
                    <td style="width: 15%;"><?php echo $lang['balance_amount']; ?></td>
                    <td style="width: 15%;"><?php echo $lang['amount']; ?></td>
                    <!--<td><?php echo $lang['coa']; ?></td>-->
                    <td></td>
                </tr>
                </thead>
                <?php $grid_row = 0; ?>
                <tbody>
                <?php foreach($cash_book_details as $detail): ?>
                <tr class="tabletext" id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                   <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                   <td>
                   <input type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][partner_name]" id="cash_book_detail_partner_name_<?php echo $grid_row; ?>" value="<?php echo $detail['partner_name']; ?>" readonly/>
                   <input type="hidden" name="cash_book_details[<?php echo $grid_row; ?>][partner_id]" id="cash_book_detail_partner_id_<?php echo $grid_row; ?>" value="<?php echo $detail['partner_id']; ?>" />
                   </td>
               <td>
                   <input type="hidden" name="cash_book_details[<?php echo $grid_row; ?>][ref_document_type_id]" id="cash_book_detail_ref_document_type_id_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_type_id']; ?>" />
                   <input type="hidden" name="cash_book_details[<?php echo $grid_row; ?>][ref_document_identity]" id="cash_book_detail_ref_document_identity_<?php echo $grid_row; ?>" value="<?php echo $detail['ref_document_identity']; ?>"/>
                   <a target="_blank" href="<?php echo $detail['href']; ?>"><?php echo $detail['ref_document_identity']; ?></a>
                   </td>
               <td>
                   <input type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][po_no]" id="cash_book_detail_po_no_<?php echo $grid_row; ?>" value="<?php echo $detail['po_no']; ?>" readonly="true"/>
                   </td>
               <td>
                   <input type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][dc_no]" id="cash_book_detail_dc_no_<?php echo $grid_row; ?>" value="<?php echo $detail['dc_no']; ?>" readonly="true"/>
                   </td>
               <td>
                   <input type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][document_amount]" id="cash_book_detail_document_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['document_amount']; ?>" readonly="true"/>
                   </td>
               <td>
                   <input  type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][balance_amount]" id="cash_book_detail_balance_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['balance_amount']; ?>"/>
                   </td>
               <td>
                   <input onchange="calculateTotal();" type="text" class="form-control" name="cash_book_details[<?php echo $grid_row; ?>][amount]" id="cash_book_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" />
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
<div class="row" style="margin-top:10px;">
    <!--<div class="col-sm-offset-6 col-md-2">
        <div class="form-group">
            <label><?php echo $lang['cash_amount']; ?></label>
            <input type="text" id="cash_amount" name="cash_amount" value="<?php echo $cash_amount; ?>" class="form-control" readonly="readonly" />
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group">
            <label><?php echo $lang['cheque_amount']; ?></label>
            <input type="text" id="cheque_amount" name="cheque_amount" value="<?php echo $cheque_amount; ?>" class="form-control" readonly="readonly" />
        </div>
    </div>-->
    <div class="col-sm-offset-9 col-md-3">
        <div class="form-group">
            <label><?php echo $lang['total_amount']; ?></label>
            <input type="text" id="total_amount" name="total_amount" value="<?php echo $total_amount; ?>" class="form-control" readonly="readonly" />
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
<script type="text/javascript" src="../admin/view/js/gl/cash_book.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>

    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $partner_id = '<?php echo $partner_id; ?>';
    var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
    var $UrlGetDocumentLedger = '<?php echo $href_get_document_ledger; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetDocuments = '<?php echo $href_get_documents; ?>';
    var $lang = <?php echo json_encode($lang) ?>;
    var $coas = <?php echo json_encode($coas) ?>;
    var $partners = [];
    var $documents = [];

    <?php if($this->request->get['cash_book_id']): ?>
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