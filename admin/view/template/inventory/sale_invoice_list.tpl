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
                    <div id="div_filter" class="dropdown pull-right" style="margin-right: 5px;">
                        <button class="btn btn-warning dropdown-toggle" type="button"><?php echo $lang['filter']; ?>
                            <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu row" style="z-index: 1050;">
                            <div class="col-sm-12">

                                <div class="form-group">
                                    <label><?php echo $lang['customer']; ?></label>
                                    <select name="partner_id" id="partner_id" class="form-control">
                                        <option value="">&nbsp;</option>
                                        <?php foreach($partners as $partner): ?>
                                        <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner['partner_id']==$partner_id?'selected="true"':'');?>><?php echo $partner['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button id="btn_filter_clear" type="button" class="btn btn-sm"><?php echo $lang['clear']; ?></button>
                                <button id="btn_filter_apply" type="button" class="btn btn-sm"><?php echo $lang['apply']; ?></button>



                            </div>
                        </div>
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
                                        <td align="center"><?php echo $lang['action']; ?></td>
                                        <td align="center"><?php echo $lang['document_date']; ?></td>
                                        <td align="center"><?php echo $lang['document_no']; ?></td>
                                        <td align="center"><?php echo $lang['partner_type']; ?></td>
                                        <td align="center"><?php echo $lang['partner_name']; ?></td>
                                        <td align="center"><?php echo $lang['amount']; ?></td>
                                        <td align="center"><?php echo $lang['invoice_status']; ?></td>
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
    var $URLList = "<?php echo $action_list; ?>";

    jQuery(document).ready(function(){
        oTable = jQuery('#dataTable').dataTable( {
            "bProcessing": true,
            "bServerSide": true,
            "bFilter": true,
            "bAutoWidth": false,
            "pageLength": 50,
            "sPaginationType": "full_numbers",
            "sAjaxSource": "<?php echo $action_ajax; ?>"
            ,"aoColumnDefs" : [ {
                'bSortable' : false,
                'aTargets' : [ 0,8 ]
            }, {
                'bSearchable' : false,
                'aTargets' : [ 0,8 ]
            } ]
            , "aaSorting": [[ 7, "desc" ]]
        });

        $('#div_filter .dropdown-toggle').on('click', function (event) {
            $(this).parent().toggleClass('open');
        });
    });

    $(document).on('click','#btn_filter_apply',function() {
        var $partner_id = $('#partner_id').val();

        var $url = $URLList;
        if($partner_id != '') {
            $url += "&partner_id="+$partner_id;
        }


//        console.log($url);
        window.location.href = $url;
    });

    $(document).on('click','#btn_filter_clear',function() {
        var $url = $URLList;
        window.location.href = $url;
    });

</script>
<?php echo $footer; ?>
</body>
</html>