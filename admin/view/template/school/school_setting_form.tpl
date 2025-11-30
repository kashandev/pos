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
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_name']; ?></label>
                                            <input class="form-control" id="bank_name" name="bank_name" value="<?php echo $bank_name; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_address']; ?></label>
                                            <textarea class="form-control" id="bank_address" name="bank_address"><?php echo $bank_address; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_account_title']; ?></label>
                                            <input class="form-control" id="bank_account_title" name="bank_account_title" value="<?php echo $bank_account_title; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_account_no']; ?></label>
                                            <input class="form-control" id="bank_account_no" name="bank_account_no" value="<?php echo $bank_account_no; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['bank_account']; ?></label>
                                            <select class="form-control" id="bank_account_id" name="bank_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($bank_account_id == $coa['coa_level3_id'] ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="bank_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['student_account']; ?></label>
                                            <select class="form-control" id="student_receivable_account_id" name="student_receivable_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($student_receivable_account_id == $coa['coa_level3_id'] ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="student_receivable_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['late_fee_title']; ?></label>
                                            <select class="form-control" id="late_fee_id" name="late_fee_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($fees as $fee): ?>
                                                <option value="<?php echo $fee['fee_id']; ?>" <?php echo ($late_fee_id == $fee['fee_id'] ? 'selected="selected"' : ''); ?>><?php echo $fee['fee_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="late_fee_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['late_fee_amount']; ?></label>
                                            <input class="form-control" type="text" id="late_fee_amount" name="late_fee_amount" value="<?php echo $late_fee_amount; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['write_off_account']; ?></label>
                                            <select class="form-control" id="write_off_account_id" name="write_off_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($write_off_account_id == $coa['coa_level3_id'] ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="write_off_account_id" style="display: none;" class="error">&nbsp;</label>
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