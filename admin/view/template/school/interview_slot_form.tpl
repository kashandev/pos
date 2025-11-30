<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <!-- Content Wrapper. Contains page content -->
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
                <div class="col-xs-12">
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
                            <form action="<?php echo $action_save; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['slot_date']; ?></label>
                                            <input type="text" id="slot_date" name="slot_date" value="<?php echo $slot_date; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['time_from']; ?></label>
                                            <input type="text" id="time_from" name="time_from" value="<?php echo $time_from; ?>" class="form-control dtpTime"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['time_to']; ?></label>
                                            <input type="text" id="time_to" name="time_to" value="<?php echo $time_to; ?>" class="form-control dtpTime"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['slot_interval']; ?></label>
                                            <select id="slot_interval" name="slot_interval" class="form-control select2" style="width: 100%;">
                                                <option value="0">&nbsp;</option>
                                                <option value="5" <?php echo ($slot_interval == 5?'selected="true"':'')?>><?php echo $lang['5_minutes']; ?></option>
                                                <option value="10" <?php echo ($slot_interval == 10?'selected="true"':'')?>><?php echo $lang['10_minutes']; ?></option>
                                                <option value="15" <?php echo ($slot_interval == 15?'selected="true"':'')?>><?php echo $lang['15_minutes']; ?></option>
                                                <option value="30" <?php echo ($slot_interval == 30?'selected="true"':'')?>><?php echo $lang['30_minutes']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><span class="required">&nbsp;</span>&nbsp;&nbsp;</label>
                                            <button type="button" class="form-control btn btn-primary" id="btnGenerate" onclick="generateSlot();"><?php echo $lang['generate']; ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table id="tbl_time_slot" class="table table-bordered table-stripped">
                                                <thead>
                                                <tr>
                                                    <th class="text-center"><?php echo $lang['sort_order']; ?></th>
                                                    <th class="text-center"><?php echo $lang['time_from']; ?></th>
                                                    <th class="text-center"><?php echo $lang['time_to']; ?></th>
                                                    <th class="text-center"><?php echo $lang['allotted']; ?></th>
                                                    <th class="text-center">&nbsp;</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php foreach($interview_slot_details as $row_no => $detail): ?>
                                                <tr id="row_<?php echo $row_no; ?>">
                                                    <td>
                                                        <input type="hidden" id="interview_slot_details_<?php echo $row_no; ?>_interview_slot_detail_id" name="interview_slot_details[<?php echo $row_no; ?>][interview_slot_detail_id]" value="<?php echo $detail['interview_slot_detail_id']; ?>" />
                                                        <input type="text" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_interview_slot_date" name="interview_slot_details[<?php echo $row_no; ?>][sort_order]" value="<?php echo $detail['sort_order']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_time_from" name="interview_slot_details[<?php echo $row_no; ?>][time_from]" value="<?php echo $detail['time_from']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_time_to" name="interview_slot_details[<?php echo $row_no; ?>][time_to]" value="<?php echo $detail['time_to']; ?>" />
                                                    </td>
                                                    <td>
                                                        <?php if($detail['allotted'] == 'False'): ?>
                                                        <input type="text" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_allotted" name="interview_slot_details[<?php echo $row_no; ?>][allotted]" value="<?php echo $detail['allotted']; ?>" />
                                                        <?php else: ?>
                                                        <input type="hidden" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_allotted" name="interview_slot_details[<?php echo $row_no; ?>][allotted]" value="<?php echo $detail['allotted']; ?>" />
                                                        <input type="hidden" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_preregistration_id" name="interview_slot_details[<?php echo $row_no; ?>][preregistration_id]" value="<?php echo $detail['preregistration_id']; ?>" />
                                                        <input type="text" class="form-control" readonly="true" id="interview_slot_details_<?php echo $row_no; ?>_preregistration_identity" name="interview_slot_details[<?php echo $row_no; ?>][preregistration_identity]" value="<?php echo $detail['preregistration_identity']; ?>" />
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if($detail['allotted'] == 'False'): ?>
                                                        <a onclick="$('#tbl_time_slot #row_<?php echo $row_no; ?>').remove();" title="Delete" data-toggle="tooltip" href="javascript:void(0);" class="btn btn-danger btn-sm"><span class="fa fa-times"></span></a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<script type="text/javascript" src="dist/js/pages/school/interview_slot.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);

    var $UrlGenerateSlot = '<?php echo $url_generate_slot; ?>';
</script>
<?php echo $footer; ?>
</body>
</html>