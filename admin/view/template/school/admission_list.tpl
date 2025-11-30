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
                        <button onclick="ConfirmDelete('<?php echo $lang['confirm_delete']; ?>')" class="btn btn-danger" title="Delete" data-toggle="tooltip" type="button"><i class="fa fa-trash-o"></i></button>
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
                            <form action="<?php echo $action_delete; ?>" method="post" enctype="multipart/form-data" id="form">
                                <table id="dataTable" class="table table-striped table-bordered table-hover" align="center">
                                    <thead>
                                    <tr>
                                        <th align="center"><?php echo $lang['action']; ?></th>
                                        <th align="center"><?php echo $lang['class_name']; ?></th>
                                        <th align="center"><?php echo $lang['section_name']; ?></th>
                                        <th align="center"><?php echo $lang['student_name']; ?></th>
                                        <th align="center"><?php echo $lang['father_name']; ?></th>
                                        <th align="center"><?php echo $lang['sur_name']; ?></th>
                                        <th align="center"><?php echo $lang['gr_no']; ?></th>
                                        <th align="center"><?php echo $lang['roll_no']; ?></th>
                                        <th align="center"><?php echo $lang['house']; ?></th>
                                        <th align="center"><?php echo $lang['created_at']; ?></th>
                                        <th align="center"><?php echo $lang['select']; ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
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
<!-- DataTable -->
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
                'bSearchable' : false,
                'aTargets' : [ 0 ]
            }, {
                'bSearchable' : false,
                'aTargets' : [ 11 ],
                'bVisible' : false
            }, {
                "orderData":[ 11 ],
                "targets": [ 6 ]
            }
            ]
            , "aaSorting": [[ 9, "desc" ]]
        });
    });
</script>
<!-- UI -->
<link rel="stylesheet" href="plugins/jQueryUI/jquery-ui-blue.css">
<script src="plugins/jQueryUI/jquery-ui.min.js"></script>
<script type="text/javascript">
    function ConfirmDelete(message, url) {
        url = url || "";
        if(message) {
            $('#confirmDelete').html(message);
        }
        $( "#confirmDelete" ).dialog({
            dialogClass: "confirmDelete",
            title: 'Confirm Delete',
            resizable: false,
            modal: true,
            buttons:
                    [
                        {
                            text: "Cancel",
                            click: function() {
                                $( this ).dialog( "close" );
                            }
                        },
                        {
                            text: "Delete",
                            click: function() {
                                if(url=='') {
                                    $('#form').submit();
                                } else {
                                    location.href=url;
                                }
                            }
                        }
                    ]
        });

        var buttons = $('.confirmDelete .ui-dialog-buttonset').children();
        $(buttons[0]).addClass('btn');
        $(buttons[1]).addClass('btn').addClass('btn-primary');
    }
</script>
<div id="confirmDelete" style="display: none;"></div>
<?php echo $footer; ?>
</body>
</html>