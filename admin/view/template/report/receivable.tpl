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
                            <form action="<?php echo $href_print_report; ?>" target="_blank" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['receivable_type']; ?></label>
                                            <select class="form-control" id="receivable_type" name="receivable_type">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($receivable_types as $receivable_type): ?>
                                                <option value="<?php echo $receivable_type; ?>"><?php echo $receivable_type; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_type']; ?></label>
                                            <select class="form-control" id="partner_type" name="partner_type">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($partner_types as $partner_type): ?>
                                                <option value="<?php echo $partner_type; ?>"><?php echo $partner_type; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <label><?php echo $lang['partner']; ?></label>
                                        <select class="form-control" id="partner_id" name="partner_id">
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-offset-8 col-sm-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button id="btnGetReport" class="btn btn-info form-control" type="button">
                                                <i class="fa fa-filter"></i>
                                                &nbsp;Filter
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button class="btn btn-info form-control" type="button" onclick="$('#form').submit();">
                                                <i class="fa fa-print"></i>
                                                &nbsp;Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="table-responsive">
                                        <table id="tblReport" class="table table-striped table-bordered">
                                            <thead class="th-color">
                                            <tr>
                                                <th class="center"><?php echo $lang['receivable_type']; ?></th>
                                                <th class="center"><?php echo $lang['partner_type']; ?></th>
                                                <th class="center"><?php echo $lang['partner']; ?></th>
                                                <th class="center"><?php echo $lang['receivable_date']; ?></th>
                                                <th class="center"><?php echo $lang['balance_amount']; ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
    <script src="plugins/dataTables/jquery.dataTables.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="dist/js/pages/report/receivable.js"></script>
    <script type="text/javascript">
        var $UrlGetPartner = '<?php echo $href_get_partner; ?>';
        var $UrlGetReport = '<?php echo $href_get_report; ?>';
        $dataTable = $('#tblReport').DataTable();
        $('#btnGetReport').trigger('click');
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>