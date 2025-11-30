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
            </div>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-header box-default">
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['from_date']; ?></label>
                                            <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['to_date']; ?></label>
                                            <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <!--    <div class="col-md-4">
                                            <div class="form-group">
                                                <label><?php echo $lang['coa_level1']; ?></label>
                                                <select class="form-control" id="coa_level1_id" name="coa_level1_id" >
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach($coa_levels1 as $coa_level1): ?>
                                                    <option value="<?php echo $coa_level1['coa_level1_id']; ?>"><?php echo $coa_level1['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>-->

                                    <!--<div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['coa_level2']; ?></label>
                                            <select class="form-control" id="coa_level2_id" name="coa_level2_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coa_levels2 as $coa_level2): ?>
                                                <option value="<?php echo $coa_level2['coa_level2_id']; ?>"><?php echo $coa_level2['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>-->
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><?php echo $lang['coa_level3']; ?></label>
                                        <select class="form-control" id="coa_level3_id" name="coa_level3_id" >
                                            <option value="">&nbsp;</option>
                                            <?php foreach($coas as $coa): ?>
                                            <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$coa_level3_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                        </div>
                                <div class="row">
                                    <div class="col-md-offset-6 col-md-3">
                                        <button type="button" id="btnFilter" class="form-control btn btn-primary"><i class="fa fa-filter"></i>&nbsp;<?php echo $lang['filter']; ?></button>
                                    </div>
                                    <div class="col-md-3">
                                        <button onclick="printReport();" type="button" id="btnPrint" class="form-control btn btn-primary"><i class="fa fa-print"></i>&nbsp;<?php echo $lang['print']; ?></button>
                                        <button onclick="printExcel();" type="button" class="form-control btn btn-success"><i class="fa fa-file"></i>&nbsp; Excel</button>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="tblReport" class="table table-striped table-bordered">
                                            <thead class="th-color">
                                            <tr>
                                                <th class="center"><?php echo $lang['document_date']; ?></th>
                                                <th class="center"><?php echo $lang['document_no']; ?></th>
                                                <th class="center"><?php echo $lang['partner_type']; ?></th>
                                                <th class="center"><?php echo $lang['partner_name']; ?></th>
                                                <th class="center"><?php echo $lang['account']; ?></th>
                                                <th class="center"><?php echo $lang['ref_document']; ?></th>
                                                <th class="center"><?php echo $lang['remarks']; ?></th>
                                                <th class="center"><?php echo $lang['debit']; ?></th>
                                                <th class="center"><?php echo $lang['credit']; ?></th>
                                                <th class="center"><?php echo $lang['created_at']; ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
    <script src="plugins/dataTables/jquery.dataTables.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="../admin/view/js/report/ledger_report.js"></script>
    <script type="text/javascript">
        var $UrlGetCOALevel2 = '<?php echo $href_get_coa_level2; ?>';
        var $UrlGetCOALevel3 = '<?php echo $href_get_coa_level3; ?>';
        var $UrlGetReport = '<?php echo $href_get_report; ?>';
        var $UrlPrintReport = '<?php echo $href_print_report; ?>';
        var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';

        $dataTable = $('#tblReport').DataTable();
    </script>
    <script type="text/javascript">
        function printExcel() {
            $('#form').attr('action', $UrlPrintExcel).submit();
        }
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>