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
                                            <input type="text" name="class_name" value="<?php echo $class_name; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['sort_order']; ?></label>
                                            <input type="text" name="sort_order" value="<?php echo $sort_order; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['status']; ?></label>
                                            <select id="status" name="status" class="form-control select2" style="width: 100%;">
                                                <option value="Inactive" <?php echo ($status == 'Inactive'?'selected="true"':'')?>><?php echo $lang['inactive']; ?></option>
                                                <option value="Active" <?php echo ($status == 'Active'?'selected="true"':'')?>><?php echo $lang['active']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="table-responsive">
                                            <table id="class_section" class="table table-bordered table-striped">
                                                <thead>
                                                <tr>
                                                    <td class="text-center"><?php echo $lang['section_name']; ?></td>
                                                    <td class="text-center"><?php echo $lang['sort_order']; ?></td>
                                                    <td class="text-center">
                                                        <a class="btn btn-default btn-sm" href="javascript:void(0);" onclick="addRow();">
                                                            <i class="fa fa-plus"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row = 0; ?>
                                                <?php foreach($sections as $section): ?>
                                                <tr id="row_<?php echo $grid_row; ?>">
                                                    <td>
                                                        <input class="form-control text-left" type="text" name="class_sections[<?php echo $grid_row; ?>][section_name]" value="<?php echo $section['section_name']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input class="form-control text-right" type="text" name="class_sections[<?php echo $grid_row; ?>][sort_order]" value="<?php echo $section['sort_order']; ?>" />
                                                    </td>
                                                    <td class="text-center">
                                                        <a class="btn btn-default btn-sm" href="javascript:void(0);" onclick="$('#row_<?php echo $grid_row; ?>').remove();">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
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
<script type="text/javascript" src="dist/js/pages/setup/class.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>
<?php echo $footer; ?>
</body>
</html>