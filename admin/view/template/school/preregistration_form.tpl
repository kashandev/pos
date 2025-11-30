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
                                <input type="hidden" id="student_id" name="student_id" value="<?php echo $student_id; ?>" />
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['preregistration_no']; ?></label>
                                            <input type="text" readonly id="preregistration_identity" name="preregistration_identity" value="<?php echo $preregistration_identity; ?>" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['preregistration_date']; ?></label>
                                            <input type="text" id="preregistration_date" name="preregistration_date" value="<?php echo $preregistration_date; ?>" class="form-control datepicker"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['interview_time']; ?></label>
                                            <input type="hidden" id="interview_slot_id" name="interview_slot_id" value="<?php echo $interview_slot_id; ?>" />
                                            <select class="form-control select2" id="interview_slot_detail_id" name="interview_slot_detail_id" onchange="updateSlotId();">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($interview_slots as $interview_slot): ?>
                                                <option data-slot_id="<?php echo $interview_slot['interview_slot_id']; ?>" value="<?php echo $interview_slot['interview_slot_detail_id']; ?>" <?php echo ($interview_slot_detail_id == $interview_slot['interview_slot_detail_id']?'selected="true"':''); ?>><?php echo $interview_slot['title']; ?></option>
                                                <?php endforeach; ?>
                                            </select>

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
                                            <input type="text" id="dob" name="dob" value="<?php echo $dob; ?>" class="form-control dtpDate"/>
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
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['registration_amount']; ?></label>
                                            <input type="text" id="preregistration_amount" name="preregistration_amount" value="<?php echo $preregistration_amount; ?>" class="form-control"/>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['remarks']; ?></label>
                                            <input type="text" id="remarks" name="remarks" value="<?php echo $remarks; ?>" class="form-control"/>
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
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);

    function updateSlotId() {
        var $interview_slot_id = $('#interview_slot_detail_id option:selected').data('slot_id');
        // console.log($interview_slot_id);
        $('#interview_slot_id').val($interview_slot_id);
    }

</script>
<?php echo $footer; ?>
</body>
</html>