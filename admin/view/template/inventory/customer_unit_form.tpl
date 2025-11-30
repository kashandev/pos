<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <div class="content-wrapper">
        <?php if ($error_warning) { ?>
        <div class="warning"><?php echo $error_warning; ?></div>
        <?php } ?>
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
                                <input type="hidden" value="<?php echo $customer_unit_id; ?>" name="customer_unit" id="customer_unit_id" />

                               <!-- <div class="row">
                                    <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $lang['partner_type']; ?></label>
                                    <select class="form-control" id="partner_type_id" name="partner_type_id">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($partner_types as $partner_type): ?>
                                        <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                </div>
                                    </div>
                                    <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_name']; ?></label>
                                            <select class="form-control" id="partner_id" name="partner_id">
                                                <option value="">&nbsp;</option>


                                            </select>
                                            <label for="partner_id" class="error" style="display: none;"></label>
                                        </div>
                                    </div>
                                </div>-->
                            <div class="row">
                                <div class="col-sm-6">
                                <div class="form-group">
                                    <label><span class="required">&nbsp;&nbsp;</span>&nbsp;<?php echo $lang['customer_unit']; ?></label>
                                    <input class="form-control" type="text" name="customer_unit" value="<?php echo $customer_unit; ?>" />
                                </div>
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
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript" src="../admin/view/js/inventory/customer_unit.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);

        <?php if($this->request->get['customer_unit_id']): ?>
        $(document).ready(function() {
            $('#partner_type_id').trigger('change');
        });
        <?php endif; ?>
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>