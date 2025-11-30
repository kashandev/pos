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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang[from_date]; ?></label>
                                            <input type="text" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lang[to_date]; ?></label>
                                            <input type="text" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                        <div class="col-sm-6">
                                            <label><?php echo $lang['user']; ?></label>
                                            <select class="form-control" id="user_id" name="user_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($users as $user): ?>
                                                <option value="<?php echo $user['user_id']; ?>"><?php echo $user['user_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                            <input type="checkbox" class="minimal" name="with_first" value="1">
                                            <label><?php echo $lang['with_first']; ?></label>
                                        </div>
                                </div>
                                <div class="row">
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
                    </div>
                </div>
            </div>
        </section>
    </div>
    <link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
    <script src="plugins/dataTables/jquery.dataTables.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="dist/js/pages/report/collection_register.js"></script>
    <script type="text/javascript">
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