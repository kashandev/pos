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
                                <form action="<?php echo $action_print; ?>" target="_blank" method="post" enctype="multipart/form-data" id="form">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['from_date']; ?></label>
                                                <input required="true"  type="text" name="date_from" id="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate" autocomplete="off"/>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['to_date']; ?></label>
                                                <input required="true" type="text" name="date_to" id="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate" autocomplete="off"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><?php echo $lang['group_by']; ?></label>
                                                <select class="form-control" name="display_level" id="level" >
                                                    <option value="3"><?php echo $lang['level3']; ?></option>
                                                    <option value="2"><?php echo $lang['level2']; ?></option>
                                                    <option value="1"><?php echo $lang['level1']; ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label> Columns </label><br>
                                                <input type="radio" name="col" value="2"> 2 Cols
                                                &nbsp;<input checked="checked" type="radio" name="col" value="6"> 6 Cols 
                                            </div>
                                        </div>
                                    </div>
                                <!-- <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['branch']; ?></label>
                                            <select class="form-control" id="branch_id" name="branch_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($branchs as $branch): ?>
                                                <option value="<?php echo $branch['company_branch_id']; ?>" <?php echo ($branch_id == $branch['company_branch_id']?'selected="selected"':''); ?>"><?php echo $branch['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                            </form>
                        </div><!-- /.box-header -->
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
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
    <script type="text/javascript">
        var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';
        var $UrlPrintPDF = '<?php echo $action_print; ?>';
        function printExcel() {
            // if($('#date_from').val() == '' || $('#date_to').val() == '')
            // {
            //     alert('Please select From and To date');
            // }
            // else
            // {
                $('#form').attr('action', $UrlPrintExcel).submit();    
            // }
            
        }
        function printPDF() {
            // if($('#date_from').val() == '' || $('#date_to').val() == '')
            // {
            //     alert('Please select From and To date');
            // }
            // else
            // {
                $('#form').attr('action', $UrlPrintPDF).submit();
            // }
        }
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>