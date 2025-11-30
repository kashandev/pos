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
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="printPDF();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                        <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();">
                            <i class="fa fa-print"></i>
                            &nbsp;Excel
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
                            <form target="_blank" action="<?php echo $action_print; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['coa_level1']; ?></label>
                                            <select class="form-control" id="coa_level1_id" name="coa_level1_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coa_levels1 as $coa_level1): ?>
                                                <option value="<?php echo $coa_level1['coa_level1_id']; ?>"><?php echo $coa_level1['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['coa_level2']; ?></label>
                                            <select class="form-control" id="coa_level2_id" name="coa_level2_id">
                                                <option value="">&nbsp;</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['coa_level3']; ?></label>
                                            <select class="form-control" id="coa_level3_id" name="coa_level3_id" >
                                                <option value="">&nbsp;</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['display_level']; ?></label>
                                            <select class="form-control" name="display_level" >
                                                <option value="3"><?php echo $lang['complete_level']; ?></option>
                                                <option value="2"><?php echo $lang['level12']; ?></option>
                                                <option value="1"><?php echo $lang['level1']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="printPDF();">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a>
                                <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();"><i class="fa fa-print"></i>&nbsp;Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript" src="../admin/view/js/report/coa.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';
        var $UrlPrintPDF = '<?php echo $action_print; ?>';
        var $UrlGetCOALevel2  = '<?php echo $href_get_coa_level2; ?>';
        var $UrlGetCOALevel3  = '<?php echo $href_get_coa_level3; ?>';
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>