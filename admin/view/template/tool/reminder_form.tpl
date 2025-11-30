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
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['event_title']; ?></label>
                                            <input class="form-control" type="text" id="event_title" name="event_title" value="<?php echo $event_title; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['event_date_time']; ?></label>
                                            <input class="form-control dtpDateTime" type="text" id="event_date_time" name="event_date_time" value="<?php echo $event_date_time; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['description']; ?></label>
                                            <textarea class="form-control" maxlength="255" id="description" name="description" rows="3" cols="10"><?php echo $description; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['remind_before']; ?></label>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input class="form-control" type="text" id="remind_before_no" name="remind_before_no" value="<?php echo $remind_before_no; ?>" />
                                                </div>
                                                <div class="col-sm-9">
                                                    <select class="form-control" id="remind_before_type" name="remind_before_type">
                                                        <option value="Minute" <?php echo ($remind_before_type=='Minute'?'selected="true"':''); ?>>Minute</option>
                                                        <option value="Hour" <?php echo ($remind_before_type=='Hour'?'selected="true"':''); ?>>Hour</option>
                                                        <option value="Day" <?php echo ($remind_before_type=='Day'?'selected="true"':''); ?>>Day</option>
                                                        <option value="Week" <?php echo ($remind_before_type=='Week'?'selected="true"':''); ?>>Week</option>
                                                        <option value="Month" <?php echo ($remind_before_type=='Month'?'selected="true"':''); ?>>Month</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['repeats']; ?></label>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <input class="form-control" type="text" id="repeat_no" name="repeat_no" value="<?php echo $repeat_no; ?>" />
                                                </div>
                                                <div class="col-sm-9">
                                                    <select class="form-control" id="repeat_type" name="repeat_type">
                                                        <option value="Once">Once</option>
                                                        <option value="Day">Day</option>
                                                        <option value="Week">Week</option>
                                                        <option value="Month">Month</option>
                                                        <option value="Year">Year</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['emails']; ?></label>
                                        <table id="tblReminderEmail" class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th><a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a></th>
                                                <th><?php echo $lang['email_as']; ?></th>
                                                <th><?php echo $lang['receiver_name']; ?></th>
                                                <th><?php echo $lang['receiver_email']; ?></th>
                                                <th><a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $grid_row=0; ?>
                                            <?php foreach($reminder_emails as $email): ?>
                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                <td>
                                                    <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    &nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                </td>
                                                <td>
                                                    <select class="form-control" id="reminder_email_email_as_<?php echo $grid_row; ?>" name="reminder_emails[<?php echo $grid_row; ?>][email_as]" >
                                                        <option value="To">To</option>
                                                        <option value="CC">CC</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="reminder_emails[<?php echo $grid_row; ?>][receiver_name]" id="reminder_email_receiver_name_<?php echo $grid_row; ?>" value="<?php echo $email['receiver_name']; ?>" />
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="reminder_emails[<?php echo $grid_row; ?>][receiver_email]" id="reminder_email_receiver_email_<?php echo $grid_row; ?>" value="<?php echo $email['receiver_email']; ?>" />
                                                </td>
                                                <td>
                                                    <a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    &nbsp;<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                </td>
                                            </tr>
                                            <?php $grid_row++; ?>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
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
    <script type="text/javascript" src="dist/js/pages/tool/reminder.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);

        var $grid_row = <?php echo $grid_row; ?>;
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>