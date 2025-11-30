<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <?php if ($error_warning) { ?>
      <div class="alert alert-danger alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
          <?php echo $error_warning; ?></div>
             <?php } ?>
                <?php  if ($success) { ?>
                    <div class="alert alert-success alert-dismissable">
                        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                            <?php echo $success; ?>
                     </div>
                            <?php  } ?>
    <div class="row">
        <div class="box">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading heading">
                        <?php echo $heading_title; ?>
                        <ul style="float: right;" class="list-nostyle list-inline">
                            <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                            <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="$('#form').submit();"><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                    <div class="panel-body">
                                        <div class="table-responsive">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $entry_account_name; ?></label>
                                                <input type="text" name="account_name" value="<?php echo $account_name; ?>" class="form-control" />
                                                <?php if (isset($error['title'])) { ?>
                                                <span class="error"><?php echo $error['title']; ?></span>
                                                <?php } ?>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $entry_transaction_account_type; ?></label>
                                                <select class="form-control" id="transaction_account_type_id" name="transaction_account_type_id" >
                                                    <?php foreach($transaction_account_types as $id => $value): ?>
                                                    <option value="<?php echo $id; ?>" <?php echo ($id == $transaction_account_type_id ? 'selected="selected"' : ''); ?>><?php echo $value; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $entry_currency; ?></label>
                                                <select class="form-control" id="currency_id" name="currency_id" >
                                                    <?php foreach($currencys as $id => $value): ?>
                                                    <option value="<?php echo $id; ?>" <?php echo ($id == $currency_id ? 'selected="selected"' : ''); ?>><?php echo $value; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label><?php echo $entry_gl_account; ?></label>
                                                <select class="form-control chosen" id="coa_level3_id" name="coa_level3_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach($arrCOAs as $id => $value): ?>
                                                    <option value="<?php echo $id; ?>" <?php echo ($id == $coa_level3_id ? 'selected="selected"' : ''); ?>><?php echo $value; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $entry_bank_name; ?></label>
                                                <input class="form-control" type="text" name="transaction_account_name" value="<?php echo $transaction_account_name; ?>" />
                                                <?php if (isset($error['transaction_account_name'])) { ?>
                                                <span class="error"><?php echo $error['transaction_account_name']; ?></span>
                                                <?php } ?>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $entry_bank_account_number; ?></label>
                                                <input type="text" name="number" value="<?php echo $number; ?>" class="form-control fInteger"/>
                                                <?php if (isset($error['account_number'])) { ?>
                                                <span class="error"><?php echo $error['account_number']; ?></span>
                                                <?php } ?>
                                            </div>
                                                <div class="form-group">
                                                <label><?php echo $entry_bank_address; ?></label>
                                                <textarea class="form-control" name="address" cols="30" rows="3"><?php echo $address; ?></textarea>
                                                <?php if (isset($error['address'])) { ?>
                                                <span class="error"><?php echo $error['address']; ?></span>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <div class="panel-heading heading">
                        &nbsp;
                        <ul style="float: right;" class="list-nostyle list-inline">
                            <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                            <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="$('#form').submit();"><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                        </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery('.chosen').chosen();
</script>
<?php echo $footer; ?>

    <script type="text/javascript" src="view/js/plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
