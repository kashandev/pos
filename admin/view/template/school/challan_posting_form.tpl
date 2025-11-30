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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['post_date']; ?></label>
                                            <input type="text" class="form-control dtpDate" id="post_date" name="post_date" value="<?php echo $post_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['challan_no']; ?></label>
                                            <input type="text" class="form-control" id="challan_no" name="challan_no" value="" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" class="form-control btn btn-primary" id="btnGetChallan" name="btnChallan" ><?php echo $lang['add']; ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="tblTemplateDetail" class="table table-bordered">
                                                <thead>
                                                <tr data-grid_row="H">
                                                    <th style="width: 5%;" class="text-center">&nbsp;</th>
                                                    <th><?php echo $lang['challan_no']; ?></th>
                                                    <th><?php echo $lang['gr_no']; ?></th>
                                                    <th><?php echo $lang['student_name']; ?></th>
                                                    <th><?php echo $lang['amount']; ?></th>
                                                    <th style="width: 5%;" class="text-center">&nbsp;</th>
                                                </tr>
                                                </thead>
                                                <tbody>
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
<script type="text/javascript" src="dist/js/pages/school/challan_posting.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);

    var $UrlGetClassSection = '<?php echo $href_get_class_section; ?>';
    var $grid_row = 0;
    var $fees = <?php echo json_encode($fees); ?>;
</script>
<?php echo $footer; ?>
</body>
</html>