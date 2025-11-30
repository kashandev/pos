<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
        <?php echo $error_warning; ?>
    </div>
    <?php } ?>
    <?php  if ($success) { ?>
    <div class="alert alert-success alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
        <?php echo $success; ?>
    </div>
    <?php  } ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading heading">
                    <?php echo $heading_title; ?>
                    <ul style="float: right;" class="list-nostyle list-inline">
                        <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                    </ul>
                </div>
                <div class="panel-body">
                    <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $text_expense_name; ?></label>
                                    <input class="form-control" type="text" id="expense_name" name="expense_name" value="<?php echo $expense_name; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $text_account_head; ?></label>
                                    <select class="select2" style="width: 100%;" id="coa_id" name="coa_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($coas as $coa): ?>
                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa_id == $coa['coa_level3_id']?'selected="selected"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="coa_id" class="error" style="display: none">&nbsp;</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="panel-heading heading">
                    &nbsp;
                    <ul style="float: right;" class="list-nostyle list-inline">
                        <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="view/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>
<script src="view/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="view/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script src="view/js/plugins/dataTables/jquery.dataTables.columnFilter.js"></script>
<script>
    var $grid_row = <?php echo count($bom_details); ?>;
    var $products = eval(<?php echo $json_products; ?>);
    var $units = eval(<?php echo $json_units; ?>);
    $('document').ready(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
        $('document').on('change','.select2', function() {
            $(this).valid();
        });
    });
</script>
<script src="view/js/pages/production/bom.js"></script>
<?php echo $footer; ?>
