<!DOCTYPE html>
<html>
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
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
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
                                <?php echo $error_warning; ?></div>
                            <?php } ?>
                            <?php  if ($success) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?></div>
                            <?php  } ?>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form action="<?php echo $action_update; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['cash_account']; ?></label>
                                            <select class="form-control" id="cash_account_id" name="cash_account_id[]" multiple="multiple" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'],$cash_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="cash_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_accounts']; ?></label>
                                            <select class="form-control" multiple="multiple" id="transaction_account_id" name="transaction_account_id[]" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'],$transaction_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="transaction_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                    <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['withholding_tax_account']; ?>(Payment)</label>
                                            <select class="form-control" id="withholding_tax_account_id" name="withholding_tax_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$withholding_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="withholding_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['withholding_tax_account']; ?>(Receipt)</label>
                                            <select class="form-control" id="withholding_tax_account_id_receipt" name="withholding_tax_account_id_receipt" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$withholding_tax_account_id_receipt ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="withholding_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['other_tax_account']; ?>(Payment)</label>
                                            <select class="form-control" id="other_tax_account_id" name="other_tax_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$other_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="other_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['other_tax_account']; ?>(Receipt)</label>
                                            <select class="form-control" id="other_tax_account_id_receipt" name="other_tax_account_id_receipt" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$other_tax_account_id_receipt ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="other_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['srb_tax']; ?></label>
                                            <select class="form-control" multiple="multiple" id="srb_tax_account_id" name="srb_tax_account_id[]" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'],$srb_tax_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="srb_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['other_deduction']; ?></label>
                                            <select class="form-control" id="other_deduction_account_id" name="other_deduction_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$other_deduction_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="srb_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
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
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);

        function getURLVar(key) {
            var value = [];

            var query = String(document.location).split('?');

            if (query[1]) {
                var part = query[1].split('&');

                for (i = 0; i < part.length; i++) {
                    var data = part[i].split('=');

                    if (data[0] && data[1]) {
                        value[data[0]] = data[1];
                    }
                }

                if (value[key]) {
                    return value[key];
                } else {
                    return '';
                }
            }
        }

    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>