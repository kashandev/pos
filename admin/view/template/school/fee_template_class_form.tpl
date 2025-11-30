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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['class_name']; ?></label>
                                            <select class="form-control" id="class_id" name="class_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($classes as $class): ?>
                                                <option value="<?php echo $class['class_id']; ?>" <?php echo ($class['class_id']==$class_id?'selected="true"':'');?>><?php echo $class['class_name'] ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['section_name']; ?></label>
                                            <select class="form-control" id="class_section_id" name="class_section_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($sections as $section): ?>
                                                <option value="<?php echo $section['class_section_id']; ?>" <?php echo ($section['class_section_id']==$class_section_id?'selected="true"':'');?>><?php echo $section['section_name'] ?></option>
                                                <?php endforeach ?>
                                            </select>
                                            <label for="class_section_id" class="error" style="display: none;">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="tblTemplateDetail" class="table table-bordered">
                                                <thead>
                                                <tr data-grid_row="H">
                                                    <th style="width: 5%;" class="text-center"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></th>
                                                    <th><?php echo $lang['due_month']; ?></th>
                                                    <th><?php echo $lang['fee_month']; ?></th>
                                                    <th><?php echo $lang['fee_title']; ?></th>
                                                    <th><?php echo $lang['fee_amount']; ?></th>
                                                    <th style="width: 5%;" class="text-center"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row = 0; ?>
                                                <?php foreach($template_details as $detail): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-grid_row="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                    <td><input type="text" class="form-control" id="template_detail_due_month_<?php echo $grid_row; ?>" name="template_details[<?php echo $grid_row; ?>][due_month]" value="<?php echo $detail['due_month']; ?>" /></td>
                                                    <td><input type="text" class="form-control" id="template_detail_fee_month_<?php echo $grid_row; ?>" name="template_details[<?php echo $grid_row; ?>][fee_month]" value="<?php echo $detail['fee_month']; ?>" /></td>
                                                    <td>
                                                        <select class="form-control" id="template_detail_fee_id_<?php echo $grid_row; ?>" name="template_details[<?php echo $grid_row; ?>][fee_id]">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($fees as $fee): ?>
                                                            <option value="<?php echo $fee['fee_id']; ?>" <?php echo ($fee['fee_id']==$detail['fee_id']?'selected="true"':''); ?>><?php echo $fee['fee_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control" id="template_detail_fee_amount_<?php echo $grid_row; ?>" name="template_details[<?php echo $grid_row; ?>][fee_amount]" value="<?php echo $detail['fee_amount']; ?>" /></td>
                                                    <td>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $grid_row++; ?>
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
<script type="text/javascript" src="dist/js/pages/school/fee_template_class.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);

    var $UrlGetClassSection = '<?php echo $href_get_class_section; ?>';
    var $grid_row = <?php echo $grid_row; ?>;
    var $fees = <?php echo json_encode($fees); ?>;
</script>
<?php echo $footer; ?>
</body>
</html>