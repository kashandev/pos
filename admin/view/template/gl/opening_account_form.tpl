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
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input type="text" class="form-control" name="remarks" value="<?php echo $remarks; ?>" />
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
                                        <table id="tblOpeningAccountDetail" class="table table-striped table-bordered">
                                            <thead>
                                            <tr align="center" data-row_id="H">
                                                <td  style="min-width: 70px !important;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                <td><?php echo $lang['partner_type']; ?></td>
                                                <td><?php echo $lang['partner_name']; ?></td>
                                                <td><?php echo $lang['document_type']; ?></td>
                                                <td><?php echo $lang['document_no']; ?></td>
                                                <td><?php echo $lang['document_date']; ?></td>
                                                <td style="width: 8%;"><?php echo $lang['document_amount']; ?></td>
                                                <td><?php echo $lang['coa']; ?></td>
                                                <td style="width: 8%;"><?php echo $lang['debit']; ?></td>
                                                <td style="width: 8%;"><?php echo $lang['credit']; ?></td>
                                                <td style="min-width: 70px !important;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                            </tr>
                                            </thead>
                                            <?php $grid_row = 0; ?>
                                            <tbody>
                                            <?php foreach($opening_account_details as $detail): ?>
                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                <td>
                                                    <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                    <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                </td>
                                                <td>
                                                    <select onchange="getPartners(<?php echo $grid_row; ?>);" class="form-control" id="opening_account_detail_partner_type_id_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][partner_type_id]">
                                                        <option value="">&nbsp;</option>
                                                        <?php foreach($partner_types as $partner_type): ?>
                                                        <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type['partner_type_id']==$detail['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="opening_account_detail_partner_id_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][partner_id]">
                                                        <option value="0">&nbsp;</option>
                                                        <?php foreach($partners as $partner): ?>
                                                        <?php if($partner['partner_type_id']==$detail['partner_type_id'] ): ?>
                                                        <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner['partner_id']==$detail['partner_id']?'selected="true"':''); ?>><?php echo $partner['name']; ?></option>
                                                        <?php endif; ?>
                                                        <!-- <input type="hidden" name="partner_account_id_<?php echo $grid_row;?>" id="partner_account_id_<?php echo $grid_row;?>" value="<?php echo $partner['outstanding_account_id'];?>" /> -->
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="opening_account_detail_ref_document_type_id_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][ref_document_type_id]">
                                                        <option value="0">&nbsp;</option>
                                                        <option value="1" <?php echo ($detail['ref_document_type_id']==1?'selected="true"':''); ?>><?php echo $lang['purchase_invoice']; ?></option>
                                                        <option value="39" <?php echo ($detail['ref_document_type_id']==39?'selected="true"':''); ?>><?php echo $lang['sale_invoice']; ?></option>
                                                        <option value="11" <?php echo ($detail['ref_document_type_id']==11?'selected="true"':''); ?>><?php echo $lang['debit_invoice']; ?></option>
                                                        <option value="24" <?php echo ($detail['ref_document_type_id']==24?'selected="true"':''); ?>><?php echo $lang['credit_invoice']; ?></option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" id="opening_account_detail_ref_document_identity_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][ref_document_identity]" value="<?php echo $detail['ref_document_identity']; ?>" />
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control dtpDate" id="opening_account_detail_ref_document_date_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][ref_document_date]" value="<?php echo $detail['ref_document_date']; ?>" />
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control fPDecimal" id="opening_account_detail_ref_document_amount_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][ref_document_amount]" value="<?php echo $detail['ref_document_amount']; ?>" />
                                                </td>
                                                <td>
                                                    <select class="form-control" id="opening_account_detail_coa_level3_id_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][coa_level3_id]">
                                                        <option value="0" >&nbsp;</option>
                                                        <?php foreach($coas as $coa):?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $detail['coa_level3_id']?'selected="true"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td><input onchange="calculateTotal(this);" type="text" class="form-control fDecimal" id="opening_account_detail_document_debit_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][document_debit]" value="<?php echo $detail['document_debit']; ?>" /></td>
                                                <td><input onchange="calculateTotal(this);" type="text" class="form-control fDecimal " id="opening_account_detail_document_credit_<?php echo $grid_row; ?>" name="opening_account_details[<?php echo $grid_row; ?>][document_credit]" value="<?php echo $detail['document_credit']; ?>" /></td>
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
                                <div class="row" style="margin-top:10px;">
                                    <div class="col-sm-offset-8 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['debit']; ?></label>
                                            <input type="text" id="document_debit" name="document_debit" value="<?php echo $document_debit; ?>" class="form-control" readonly="readonly" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['credit']; ?></label>
                                            <input type="text" id="document_credit" name="document_credit" value="<?php echo $document_credit; ?>" class="form-control" readonly="readonly" />
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
    <script type="text/javascript" src="../admin/view/js/gl/opening_account.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $partner_id = '<?php echo $partner_id; ?>';
        var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
        var $UrlGetPartnerAccount = '<?php echo $href_get_partner_account; ?>';
        var $UrlGetDocumentLedger = '<?php echo $href_get_document_ledger; ?>';
        var $grid_row = '<?php echo $grid_row; ?>';
        var $lang = <?php echo json_encode($lang) ?>;
        var $coas = <?php echo json_encode($coas) ?>;
        var $partner_types = <?php echo json_encode($partner_types) ?>;
        var $partners = <?php echo json_encode($partners) ?>;

        <?php if($this->request->get['opening_account_id']): ?>
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