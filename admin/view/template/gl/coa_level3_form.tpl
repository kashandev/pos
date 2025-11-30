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
                                    <div class="col-sm-5">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['level1']; ?></label>
                                            <select class="form-control select2" id="coa_level1_id" name="coa_level1_id" <?php echo ($isEdit?'disabled="true"':''); ?>>
                                            <option value="">&nbsp;</option>
                                            <?php foreach($coa_level1s as $coa_level1): ?>
                                            <option gl_type_id="<?php echo $coa_level1['gl_type_id']; ?>" code="<?php echo $coa_level1['level1_code']; ?>" value="<?php echo $coa_level1['coa_level1_id']; ?>" <?php echo ($coa_level1['coa_level1_id'] == $coa_level1_id ? 'selected="selected"' : ''); ?>><?php echo $coa_level1['name']; ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['level2']; ?></label>
                                            <select class="form-control select2" id="coa_level2_id" name="coa_level2_id" <?php echo($isEdit?'disabled="true"' : '')?>>
                                            <option value="">&nbsp;</option>
                                            <?php foreach($coa_level2s as $coa_level2): ?>
                                            <option value="<?php echo $coa_level2['coa_level2_id']; ?>" <?php echo ($coa_level2['coa_level2_id'] == $coa_level2_id ? 'selected="selected"' : ''); ?>><?php echo $coa_level2['name']; ?></option>
                                            <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['code']; ?></label>
                                            <input type="text" id="level3_code" name="level3_code" <?php echo($isEdit?'readonly="true"':'')?> value="<?php echo $level3_code; ?>" class="form-control fInteger" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['name']; ?></label>
                                            <input type="text" name="name" value="<?php echo $name; ?>" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div class="table-responsive form-group" style="max-height: 300px;">
                                            <table id="tblList" class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th><?php echo $lang['level1']; ?></th>
                                                    <th><?php echo $lang['level2']; ?></th>
                                                    <th><?php echo $lang['code']; ?></th>
                                                    <th><?php echo $lang['name']; ?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
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
    <script type="text/javascript" src="../admin/view/js/gl/coa_level3.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        var $coa_level2_id = '<?php echo $coa_level2_id; ?>';
        var $UrlGetCOALevel2 = '<?php echo $href_get_coa_level2; ?>';
        var $UrlGetLevelData = '<?php echo $href_get_level_data; ?>';
        $('#coa_level1_id').trigger('change');
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>