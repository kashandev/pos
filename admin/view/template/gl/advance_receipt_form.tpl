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
                <!--
                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                    <i class="fa fa-print"></i>
                    &nbsp;<?php echo $lang['print']; ?>
                </a>
                -->
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
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[document_identity]; ?></label>
                                    <input type="text" name="document_identity" value="<?php echo $document_identity; ?>" class="form-control" readonly="true" />
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[document_date]; ?></label>
                                    <input  type="text" class="dtpDate form-control" name="document_date" value="<?php echo $document_date; ?>" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[transaction_account]; ?></label>
                                    <div class="chosen-search">
                                        <select class="form-control" id="transaction_account_id" name="transaction_account_id" >
                                            <option value="">&nbsp;</option>
                                            <?php foreach($transaction_accounts as $transaction_account): ?>
                                            <option value="<?php echo $transaction_account['coa_level3_id']; ?>" <?php echo ($transaction_account_id == $transaction_account['coa_level3_id']?'selected="selected"':''); ?>"><?php echo $transaction_account['level3_display_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="transaction_account_id" class="error" style="display: none;">&nbsp;</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
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
                            <div class="col-sm-4">
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
                            <!-- /input-group -->
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[cheque_date]; ?></label>
                                    <input  type="text" class="dtpDate form-control" name="cheque_date" id="cheque_date" value="<?php echo $cheque_date; ?>" autocomplete="off"/>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[cheque_no]; ?></label>
                                    <input type="text" name="cheque_no" id="cheque_no" value="<?php echo $cheque_no; ?>" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label><?php echo $lang['remarks']; ?></label>
                                    <input type="text" name="remarks" value="<?php echo $remarks; ?>" class="form-control" />
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
                        <div class="row" >
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $lang[amount]; ?></label>
                                    <input type="text"  id="amount" name="amount" value="<?php echo $amount; ?>" class="form-control fDecimal" onchange="calcTotal()" />
                                </div>
                            </div>
                        </div>
                        <div class="row hide" >
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label><?php echo $column_base_amount; ?></label>
                                    <input type="text" id="base_amount" name="base_amount" value="<?php echo $base_amount; ?>" class="form-control fDecimal" readonly="readonly" />
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
                        <!--
                        <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                        -->
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
<script type="text/javascript" src="../admin/view/js/gl/advance_receipt.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $partner_id = '<?php echo $partner_id; ?>';
    var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
    var $UrlGetBank = '<?php echo $href_get_bank; ?>';
    //    var $UrlGetDocumentLedger = '<?php echo $href_get_document_ledger; ?>';
    var $grid_row = '<?php echo $grid_row; ?>';
    var $lang = <?php echo json_encode($lang) ?>;
    var $coas = <?php echo json_encode($coas) ?>;
    var $partners = [];
    <?php if($this->request->get['advance_receipt_id']): ?>
    $(document).ready(function() {
        $('#partner_type_id').trigger('change');

        var check = $('#account_type:checked').val();
        if(check == 'CA')
        {
            $('#cheque_date').val('');
            $('#cheque_no').val('');
            $('#cheque_date').prop('disabled',true);
            $('#cheque_no').prop('disabled',true);

        }
        else{
            $('#cheque_date').prop('disabled',false);
            $('#cheque_no').prop('disabled',false);
        }
    });
    <?php endif; ?>
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>