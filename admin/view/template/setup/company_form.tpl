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
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="tab-pane fade in active" id="general">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['company_name']; ?></label>
                                                <input type="text" name="company_name" value="<?php echo $company_name; ?>" class="form-control"/>
                                            </div>
                                            <hr />
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_code']; ?></label>
                                                <input type="text" name="branch_code" value="<?php echo $branch_code; ?>" class="form-control"/>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_name']; ?></label>
                                                <input type="text" name="branch_name" value="<?php echo $branch_name; ?>" class="form-control"/>
                                            </div>
                                            <hr />
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['default_currency']; ?></label>
                                                <input type="text" name="default_currency" value="<?php echo $default_currency; ?>" class="form-control"/>
                                            </div>
                                            <hr />
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['fiscal_year_code']; ?></label>
                                                <input type="text" name="fiscal_year_code" value="<?php echo $fiscal_year_code; ?>" class="form-control"/>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['fiscal_year_title']; ?></label>
                                                <input type="text" name="fiscal_year_title" value="<?php echo $fiscal_year_title; ?>" class="form-control"/>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['date_from']; ?></label>
                                                <input type="text" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['date_to']; ?></label>
                                                <input type="text" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                            </div>
                                            <hr />
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['user_name']; ?></label>
                                                <input type="text" name="user_name" value="<?php echo $user_name; ?>" class="form-control"/>
                                            </div>
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['user_password']; ?></label>
                                                <input type="text" name="user_password" value="<?php echo $user_password; ?>" class="form-control"/>
                                            </div>
                                            <hr />
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['partner_type']; ?></label>
                                            <?php foreach($partner_types as $row_no => $partner_type): ?>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="partner_types[<?php echo $row_no; ?>][selected]" value="1" <?php echo $partner_type['selected']==1?'checked="checked"':'' ?>>&nbsp;<?php echo $partner_type['name']; ?>
                                                    <input type="hidden" name="partner_types[<?php echo $row_no; ?>][partner_type_id]" value="<?php echo $partner_type['partner_type_id']; ?>" />
                                                    <input type="hidden" name="partner_types[<?php echo $row_no; ?>][name]" value="<?php echo $partner_type['name']; ?>" />
                                                </label>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="row">
                                        <?php foreach($forms as $form => $permission): ?>
                                            <div class="col-xs-6">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="form_access[<?php echo $form; ?>]" value="1" <?php echo $permission==1?'checked="checked"':'' ?>>&nbsp;<?php echo $form; ?>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
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
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>