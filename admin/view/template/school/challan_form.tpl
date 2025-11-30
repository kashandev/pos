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
                            <div class="col-md-4">
                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['preregistration_no']; ?></label>
                                <div class="form-group input-group">
                                    <input type="hidden" id="preregistration_id" name="preregistration_id" value="<?php echo $preregistration_id; ?>" class="form-control"/>
                                    <input type="hidden" id="student_id" name="student_id" value="<?php echo $student_id; ?>" class="form-control"/>
                                    <input type="text" readonly id="preregistration_identity" name="preregistration_identity" value="<?php echo $preregistration_identity; ?>" class="form-control"/>
                                    <span class="input-group-btn">
                                        <button id="preregistration_btn" type="button" class="btn btn-default"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['for_academic_year']; ?></label>
                                    <select class="form-control select2" id="for_academic_year_id" name="for_academic_year_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($academic_years as $academic_year): ?>
                                        <option value="<?php echo $academic_year['academic_year_id']; ?>" <?php echo ($for_academic_year_id == $academic_year['academic_year_id']?'selected="true"':''); ?>><?php echo $academic_year['title']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="for_academic_year_id" class="error"></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['for_class']; ?></label>
                                    <select class="form-control select2" id="for_class_id" name="for_class_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($classes as $class): ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php echo ($for_class_id == $class['class_id']?'selected="true"':''); ?>><?php echo $class['class_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="for_class_id" class="error"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['student_name']; ?></label>
                                    <input type="text" id="student_name" name="student_name" value="<?php echo $student_name; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['sur_name']; ?></label>
                                    <input type="text" id="sur_name" name="sur_name" value="<?php echo $sur_name; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['date_of_birth']; ?></label>
                                    <input type="text" id="dob" name="dob" value="<?php echo $dob; ?>" class="form-control datepicker"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['father_name']; ?></label>
                                    <input type="text" id="father_name" name="father_name" value="<?php echo $father_name; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mother_name']; ?></label>
                                    <input type="text" id="mother_name" name="mother_name" value="<?php echo $mother_name; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['phone_no']; ?></label>
                                    <input type="text" id="phone_no" name="phone_no" value="<?php echo $phone_no; ?>" class="form-control"/>
                                </div>
                            </div>
                        </div>
                        <hr />
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['challan_no']; ?></label>
                                    <input type="text" readonly="true" id="challan_identity" name="challan_identity" value="<?php echo $challan_identity; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['challan_title']; ?></label>
                                    <input type="text" id="challan_title" name="challan_title" value="<?php echo $challan_title; ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['due_month']; ?></label>
                                    <input type="text" id="due_month" name="due_month" value="<?php echo $due_month; ?>" class="form-control monthpicker"/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['last_date']; ?></label>
                                    <input type="text" id="last_date" name="last_date" value="<?php echo $last_date; ?>" class="form-control datepicker"/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['validity_date']; ?></label>
                                    <input type="text" id="validity_date" name="validity_date" value="<?php echo $validity_date; ?>" class="form-control datepicker"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['fee_month']; ?></label>
                                    <input type="text" id="fee_month" name="fee_month" value="" class="form-control monthpicker"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['fee']; ?></label>
                                    <select class="form-control" name="fee_id" id="fee_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($fees as $fee): ?>
                                        <option value="<?php echo $fee['fee_id']; ?>"><?php echo $fee['fee_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['fee_amount']; ?></label>
                                    <input type="text" id="fee_amount" name="fee_amount" value="" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="button" class="btn btn-primary form-control" value="Add" id="btnAdd">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <table id="tbl_challan_detail" class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <td class="text-center"><?php echo $lang['fee_month']; ?></td>
                                        <td class="text-center"><?php echo $lang['fee']; ?></td>
                                        <td class="text-center"><?php echo $lang['fee_amount']; ?></td>
                                        <td class="text-center"><?php echo $lang['action']; ?></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $grid_row = 0; ?>
                                    <?php foreach($challan_details as $detail): ?>
                                    <tr id="grid_row_<?php echo $grid_row; ?>">
                                        <td class="text-center">
                                            <input class="form-control" type="hidden" readonly="true" id="fee_challan_details_fee_challan_detail_id_<?php echo $grid_row; ?>" name="fee_challan_details[<?php echo $grid_row; ?>][fee_challan_detail_id]" value="<?php echo $detail['fee_challan_detail_id']; ?>" />
                                            <input class="form-control" type="text" readonly="true" id="fee_challan_details_fee_month_<?php echo $grid_row; ?>" name="fee_challan_details[<?php echo $grid_row; ?>][fee_month]" value="<?php echo $detail['fee_month']; ?>" />
                                        </td>
                                        <td>
                                            <input class="form-control" type="text" readonly="true" id="fee_challan_details_fee_name_<?php echo $grid_row; ?>" name="fee_challan_details[<?php echo $grid_row; ?>][fee_name]" value="<?php echo $detail['fee_name']; ?>" />
                                            <input class="form-control" type="hidden" readonly="true" id="fee_challan_details_fee_id_<?php echo $grid_row; ?>" name="fee_challan_details[<?php echo $grid_row; ?>][fee_id]" value="<?php echo $detail['fee_id']; ?>" />
                                        </td>
                                        <td>
                                            <input class="form-control" type="text" readonly="true" id="fee_challan_details_fee_amount_<?php echo $grid_row; ?>" name="fee_challan_details[<?php echo $grid_row; ?>][fee_amount]" value="<?php echo $detail['fee_amount']; ?>" />
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-danger" onclick="$('#grid_row_<?php echo $grid_row; ?>').remove();"><i class="fa fa-times"></i></button>
                                        </td>
                                    </tr>
                                    <?php $grid_row++; endforeach; ?>
                                    </tbody>
                                </table>
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
<script type="text/javascript" src="dist/js/pages/challan.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    var $grid_row = <?php echo $grid_row; ?>;
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>
<!-- Select2 -->
<link rel="stylesheet" href="plugins/select2/select2.min.css">
<script src="plugins/select2/select2.full.min.js"></script>
<link rel="stylesheet" href="plugins/datepicker/datepicker3.css">
<script type="text/javascript" src="plugins/datepicker/bootstrap-datepicker.js"></script>
<link rel="stylesheet" href="plugins/timepicker/bootstrap-timepicker.min.css">
<script type="text/javascript" src="plugins/timepicker/bootstrap-timepicker.min.js"></script>
<!-- DataTable -->
<link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
<script src="plugins/dataTables/jquery.dataTables.js"></script>
<script src="plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
    <!--
    $UrlGetPreregistration = '<?php echo $url_get_preregistration; ?>';
    -->
</script>
<?php echo $footer; ?>
</body>
</html>