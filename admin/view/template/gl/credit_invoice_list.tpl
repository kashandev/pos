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
                        <a class="btn btn-primary" title="Add New" data-toggle="tooltip" href="<?php echo $action_insert; ?>"><i class="fa fa-plus"></i></a>
                        <button onclick="ConfirmDelete('<?php echo $action_delete; ?>',1);" class="btn btn-danger" title="Delete" data-toggle="tooltip" type="button"><i class="fa fa-trash-o"></i></button>
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
                            <form action="#" method="post" enctype="multipart/form-data" id="form">
                                <table id="dataTable" class="table table-striped table-bordered table-hover" align="center">
                                    <thead>
                                    <tr>
                                        <td aligns="center"><?php echo $lang['action']; ?></td>
                                        <td aligns="center"><?php echo $lang['document_date']; ?></td>
                                        <td aligns="center"><?php echo $lang['document_no']; ?></td>
                                        <td aligns="center"><?php echo $lang['partner_type']; ?></td>
                                        <td aligns="center"><?php echo $lang['partner']; ?></td>
                                        <td aligns="center"><?php echo $lang['remarks']; ?></td>
                                        <td aligns="center"><?php echo $lang['total_amount']; ?></td>
                                        <td align="center"><?php echo $lang['created_at']; ?></td>
                                        <td align="center"><?php echo $lang['delete']; ?></td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
<script src="plugins/dataTables/jquery.dataTables.js"></script>
<script src="plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function(){
        oTable = jQuery('#dataTable').dataTable( {
            "bProcessing": true,
            "bServerSide": true,
            "bFilter": true,
            "bAutoWidth": false,
            "sPaginationType": "full_numbers",
            "sAjaxSource": "<?php echo $action_ajax; ?>"
            ,"aoColumnDefs" : [ {
                'bSortable' : false,
                'aTargets' : [ 0, 8 ]
            }, {
                'bSearchable' : false,
                'aTargets' : [ 0, 8 ]
            } ]
            , "aaSorting": [[ 7, "desc" ]]
        });
    });
</script>
<?php echo $footer; ?>
</body>
</html>