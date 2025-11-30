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
                                    <div class="col-md-12">
                                        <div class="nav-tabs-custom">
                                            <ul class="nav nav-tabs">
                                                <li class="active"><a data-toggle="tab" href="#student"><?php echo $lang['student_information']; ?></a></li>
                                                <li><a data-toggle="tab" href="#section"><?php echo $lang['section_information']; ?></a></li>
                                                <li><a data-toggle="tab" href="#parent"><?php echo $lang['parent_information']; ?></a></li>
                                                <li><a data-toggle="tab" href="#siblings"><?php echo $lang['siblings_information']; ?></a></li>
                                            </ul>
                                            <div class="tab-content">
                                                <!-- Student -->
                                                <div id="student" class="tab-pane active">
                                                    <div class="row">
                                                        <div class="col-md-9">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['preregistration_no']; ?></label>
                                                                        <div class="form-group input-group">
                                                                            <input type="text" readonly="true" id="preregistration_identity" name="preregistration_identity" value="<?php echo $preregistration_identity; ?>" class="form-control"/>
                                                                            <span class="input-group-btn">
                                                                                <button type="button" class="btn btn-default" id="btnSearchPreregistration">
                                                                                    <i class="fa fa-search"></i>
                                                                                </button>
                                                                            </span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['for_academic_year']; ?></label>
                                                                        <select class="form-control select2" id="for_academic_year_id" name="for_academic_year_id">
                                                                            <option value="">&nbsp;</option>
                                                                            <?php foreach($academic_years as $year): ?>
                                                                            <option value="<?php echo $year['academic_year_id']; ?>"><?php echo $year['title']; ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                        <label for="student_gender" class="error"></label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['gr_no']; ?></label>
                                                                        <input type="text" id="gr_no" name="gr_no" value="<?php echo $gr_no; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
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
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['gender']; ?></label>
                                                                        <select class="form-control select2" id="student_gender" name="student_gender">
                                                                            <option value="">&nbsp;</option>
                                                                            <option value="Male" <?php echo ($student_gender=='Male'?'selected="true"':''); ?>><?php echo $lang['male']; ?></option>
                                                                            <option value="Female" <?php echo ($student_gender=='Female'?'selected="true"':''); ?>><?php echo $lang['female']; ?></option>
                                                                        </select>
                                                                        <label for="student_gender" class="error"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['dob']; ?></label>
                                                                        <input type="text" id="student_dob" name="student_dob" value="<?php echo $student_dob; ?>" class="form-control datepicker"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['place_of_birth']; ?></label>
                                                                        <input type="text" id="place_of_birth" name="place_of_birth" value="<?php echo $place_of_birth; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['country']; ?></label>
                                                                        <select class="form-control select2" id="country_id" name="country_id">
                                                                            <option value="">&nbsp;</option>
                                                                            <?php foreach($countries as $country): ?>
                                                                            <option value="<?php echo $country['country_id']; ?>" <?php echo ($country['country_id']==$country_id?'selected="true"':''); ?>><?php echo $country['country_name']; ?></option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                        <label for="country_id" class="error"></label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['city']; ?></label>
                                                                        <input type="text" id="city" name="city" value="<?php echo $city; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['post_code']; ?></label>
                                                                        <input type="text" id="post_code" name="post_code" value="<?php echo $post_code; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['phone_no']; ?></label>
                                                                        <input type="text" id="phone_no" name="phone_no" value="<?php echo $phone_no; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <div class="form-group">
                                                                        <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['address']; ?></label>
                                                                        <input type="text" id="address" name="address" value="<?php echo $address; ?>" class="form-control"/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <!-- Image Goes Here -->
                                                            <div class="form-group">
                                                                <label><span class="required">&nbsp;&nbsp;</span>&nbsp;<?php echo $lang['student_image']; ?></label>
                                                                <br />
                                                                <a href="javascript:void(0);" id="a_student_image"  data-toggle="image" class="img-thumbnail" data-src_image="src_student_image" data-src_input="file_student_image" data-width="300" data-height="300">
                                                                    <img alt="Company Logo" src="<?php echo $src_student_image; ?>"  id="src_student_image" alt="" title="" data-placeholder="<?php echo $no_image; ?>" class="profile-user-img img-responsive"/>
                                                                </a>
                                                                <input type="hidden" name="student_image" value="<?php echo $student_image; ?>" id="file_student_image" />
                                                                <br />
                                                                <a class="btn btn-primary btn-xs" onclick="jQuery('#src_student_image').attr('src', '<?php echo $no_image; ?>'); jQuery('#file_student_image').attr('value', '');"><?php echo $lang['clear']; ?></a>
                                                                <br />&nbsp;
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.tab-pane Student -->
                                                <!-- /.tab-pane Section-->
                                                <div id="section" class="tab-pane">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['class']; ?></label>
                                                                <select class="form-control select2" id="class_id" name="class_id" style="width: 100%">
                                                                    <option value="">&nbsp;</option>
                                                                    <?php foreach($classes as $class): ?>
                                                                    <option value="<?php echo $class['class_id']; ?>" <?php echo ($class['class_id']==$class_id?'selected="true"':''); ?>><?php echo $class['class_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <label for="class_id" class="error"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['section']; ?></label>
                                                                <select class="form-control select2" id="class_section_id" name="class_section_id" style="width: 100%">
                                                                    <option value="">&nbsp;</option>
                                                                    <?php foreach($class_sections as $section): ?>
                                                                    <option value="<?php echo $section['class_section_id']; ?>" <?php echo ($section['class_section_id']==$class_section_id?'selected="true"':''); ?>><?php echo $section['section_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <label for="class_section_id" class="error"></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['house']; ?></label>
                                                                <select class="form-control select2" id="house_id" name="house_id" style="width: 100%">
                                                                    <option value="">&nbsp;</option>
                                                                    <?php foreach($houses as $house): ?>
                                                                    <option value="<?php echo $house['house_id']; ?>" <?php echo ($house['house_id']==$house_id?'selected="true"':''); ?>><?php echo $house['house_name']; ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['roll_no']; ?></label>
                                                                <input type="text" id="roll_no" name="roll_no" value="<?php echo $roll_no; ?>" class="form-control"/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.tab-pane Section -->
                                                <!-- tab-pane Parent-->
                                                <div id="parent" class="tab-pane">
                                                    <div class="row">
                                                        <!-- Father Information -->
                                                        <div class="col-md-6">
                                                            <div class="panel panel-default">
                                                                <div class="panel-heading">
                                                                    <h3 class="panel-title"><?php echo $lang['father_information'];?></h3>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['father_name']; ?></label>
                                                                                <input type="text" id="father_name" name="father_name" value="<?php echo $father_name; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['father_cnic']; ?></label>
                                                                                <input type="text" id="father_cnic" name="father_cnic" value="<?php echo $father_cnic; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['father_occupation']; ?></label>
                                                                                <select class="form-control select2" id="father_occupation_id" name="father_occupation_id" style="width: 100%">
                                                                                    <option value="">&nbsp;</option>
                                                                                    <?php foreach($occupations as $occupation): ?>
                                                                                    <option value="<?php echo $occupation['occupation_id']; ?>" <?php echo ($occupation['occupation_id']==$father_occupation_id?'selected="true"':''); ?>><?php echo $occupation['occupation_name']; ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['father_qualification']; ?></label>
                                                                                <select class="form-control select2" id="father_qualification_id" name="father_qualification_id" style="width: 100%">
                                                                                    <option value="">&nbsp;</option>
                                                                                    <?php foreach($qualifications as $qualification): ?>
                                                                                    <option value="<?php echo $qualification['qualification_id']; ?>" <?php echo ($qualification['qualification_id']==$father_occupation_id?'selected="true"':''); ?>><?php echo $qualification['qualification_name']; ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['father_contact_no']; ?></label>
                                                                                <input type="text" id="father_contact_no" name="father_contact_no" value="<?php echo $father_contact_no; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['father_email']; ?></label>
                                                                                <input type="text" id="father_email" name="father_email" value="<?php echo $father_email; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- END Father Information -->
                                                        <!-- Mother Information -->
                                                        <div class="col-md-6">
                                                            <div class="panel panel-default">
                                                                <div class="panel-heading">
                                                                    <h3 class="panel-title"><?php echo $lang['mother_information'];?></h3>
                                                                </div>
                                                                <div class="panel-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['mother_name']; ?></label>
                                                                                <input type="text" id="mother_name" name="mother_name" value="<?php echo $mother_name; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mother_cnic']; ?></label>
                                                                                <input type="text" id="mother_cnic" name="mother_cnic" value="<?php echo $mother_cnic; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mother_occupation']; ?></label>
                                                                                <select class="form-control select2" id="mother_occupation_id" name="mother_occupation_id" style="width: 100%">
                                                                                    <option value="">&nbsp;</option>
                                                                                    <?php foreach($occupations as $occupation): ?>
                                                                                    <option value="<?php echo $occupation['occupation_id']; ?>" <?php echo ($occupation['occupation_id']==$mother_occupation_id?'selected="true"':''); ?>><?php echo $occupation['occupation_name']; ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mother_qualification']; ?></label>
                                                                                <select class="form-control select2" id="mother_qualification_id" name="mother_qualification_id" style="width: 100%">
                                                                                    <option value="">&nbsp;</option>
                                                                                    <?php foreach($qualifications as $qualification): ?>
                                                                                    <option value="<?php echo $qualification['qualification_id']; ?>" <?php echo ($qualification['qualification_id']==$mother_occupation_id?'selected="true"':''); ?>><?php echo $qualification['qualification_name']; ?></option>
                                                                                    <?php endforeach; ?>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['mother_contact_no']; ?></label>
                                                                                <input type="text" id="mother_contact_no" name="mother_contact_no" value="<?php echo $mother_contact_no; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mother_email']; ?></label>
                                                                                <input type="text" id="mother_email" name="mother_email" value="<?php echo $mother_email; ?>" class="form-control"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- END Mother Information -->
                                                    </div>
                                                </div>
                                                <!-- /.tab-pane Parent-->
                                                <!-- tab-pane Siblings -->
                                                <div id="siblings" class="tab-pane">
                                                    <div class="table-responsive no-padding">
                                                        <table id="tbl_sibling" class="table table-striped">
                                                            <thead>
                                                            <th><?php echo $lang['gr_no']; ?></th>
                                                            <th><?php echo $lang['student_name']; ?></th>
                                                            <th><?php echo $lang['sur_name']; ?></th>
                                                            <th><?php echo $lang['class']; ?></th>
                                                            <th><button id="btn_add_sibling" type="button" class="btn btn-primary"><?php echo $lang['add']; ?></button></th>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div><!-- /.tab-pane Siblings -->
                                            </div><!-- /.tab-content -->
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
<link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
<script src="plugins/dataTables/jquery.dataTables.js"></script>
<script src="plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="dist/js/pages/school/admission.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $UrlGetPreRegistration = '<?php echo $href_get_pre_registration; ?>';
    var $UrlGetSection = '<?php echo $href_get_section; ?>';
    var $UrlGetStudent = '<?php echo $href_get_student; ?>';
</script>
<?php echo $footer; ?>
</body>
</html>