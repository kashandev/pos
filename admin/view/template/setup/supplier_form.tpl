<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
<?php echo $page_header; ?>
<?php echo $column_left; ?>
<div class="content-wrapper">
<?php if ($error_warning) { ?>
<div class="warning"><?php echo $error_warning; ?></div>
<?php } ?>
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
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['name']; ?></label>
                                    <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <!-- <div class="form-group">
                                    <label><?php echo $lang['partner_category']; ?></label>
                                    <select class="form-control" id="partner_category_id" name="partner_category_id" >
                                        <option value="">&nbsp;</option>
                                        <?php foreach($partner_categories as $partner_category): ?>
                                        <option value="<?php echo $partner_category['partner_category_id']; ?>" <?php echo ($partner_category_id == $partner_category['partner_category_id']?'selected="selected"':''); ?>><?php echo $partner_category['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div> -->
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['address']; ?></label>
                                    <input  type="text" name="address" value="<?php echo $address; ?>" class="form-control" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['phone']; ?></label>
                                    <input type="text" name="phone" value="<?php echo $phone; ?>" class="form-control fPhone" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['mobile']; ?></label>
                                    <input type="text" name="mobile" value="<?php echo $mobile; ?>" class="form-control fPhone" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['email']; ?></label>
                                    <input type="text" name="email" value="<?php echo $email; ?>" class="form-control fEmail" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['day_limit']; ?></label>
                                    <input type="text" name="day_limit" value="<?php echo $day_limit; ?>" class="form-control fPInteger" />
                                </div>
                            </div>
                             <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['currency']; ?></label>
                                    <select class="form-control" id="document_currency_id" name="document_currency_id" >
                                        <?php foreach($currencies as $currency): ?>
                                        <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['amount_limit']; ?></label>
                                    <input type="text" name="amount_limit" value="<?php echo $amount_limit; ?>" class="form-control fDecimal" />
                                </div>
                            </div> -->
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['gst_no']; ?></label>
                                    <input type="text" name="gst_no" value="<?php echo $gst_no; ?>" class="form-control" />
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['ntn_no']; ?></label>
                                    <input type="text" name="ntn_no" value="<?php echo $ntn_no; ?>"  class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['outstanding_account']; ?> (Use Account "Payable To Supplier")</label>
                                    <select class="form-control" id="outstanding_account_id" name="outstanding_account_id" >
                                        <?php if(count($coas)>1): ?>
                                        <option value="">&nbsp;</option>
                                        <?php endif; ?>
                                        <?php foreach($coas as $coa): ?>
                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($outstanding_account_id == $coa['coa_level3_id']?'selected="selected"':''); ?>"><?php echo $coa['level3_display_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="outstanding_account_id" class="error" style="display: none;"></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['advance_account']; ?> (Use Account "Advance To Supplier")</label>
                                    <select class="form-control" id="advance_account_id" name="advance_account_id" >
                                        <?php if(count($coas)>1): ?>
                                        <option value="">&nbsp;</option>
                                        <?php endif; ?>
                                        <?php foreach($coas as $coa): ?>
                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($advance_account_id == $coa['coa_level3_id']?'selected="selected"':''); ?>"><?php echo $coa['level3_display_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="advance_account_id" class="error" style="display: none;"<?php echo $error['advance_account']?></label>
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
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>