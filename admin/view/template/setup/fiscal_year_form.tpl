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
        <div class="col-sm-12">
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
                        <div class="col-sm-6">
                            <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $entry_company; ?></label>
                                    <select class="form-control" id="company_id" name="company_id" >
                                        <?php foreach($companys as $company): ?>
                                        <option value="<?php echo $company['company_id']; ?>" <?php echo ($company['company_id'] == $company_id ? 'selected="selected"': ''); ?>><?php echo $company['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($error['company_id'])) { ?>
                                    <span class="error"><?php echo $error['company_id']; ?></span>
                                    <?php } ?>
                                </div>

                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $entry_name; ?></label>
                                    <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" />
                                    <?php if (isset($error['name'])) { ?>
                                    <span class="error"><?php echo $error['name']; ?></span>
                                    <?php } ?>
                                </div>
                            
                                <div class="form-group">
                                    <label><?php echo $entry_date_from; ?></label>
                                    <input  type="text" name="date_from" value="<?php echo $date_from; ?>" class="dtpDate form-control" />
                                    <?php if (isset($error['date_from'])) { ?>
                                    <span class="error"><?php echo $error['date_from']; ?></span>
                                    <?php } ?>
                                </div>
                                                    
                                <div class="form-group">
                                    <label><?php echo $entry_date_to; ?></label>
                                    <input type="text" name="date_to" value="<?php echo $date_to; ?>" class="dtpDate form-control" />
                                    <?php if (isset($error['date_to'])) { ?>
                                    <span class="error"><?php echo $error['date_to']; ?></span>
                                    <?php } ?>
                                </div>
                            
                                <div class="form-group">
                                    <label><?php echo $entry_status; ?></label>
                            
                                    <select class="form-control" name="status">
                                        <option value="1" <?php echo ($status == 1 ? 'selected="selected"' :''); ?>><?php echo $text_enabled; ?></option>
                                        <option value="2" <?php echo ($status == 2 ? 'selected="selected"' :''); ?>><?php echo $text_disabled; ?></option>
                                    </select>
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
<?php echo $footer; ?>

<script type="text/javascript" src="view/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>