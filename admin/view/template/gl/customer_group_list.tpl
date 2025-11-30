<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <!--    <div class="breadcrumb">
            <?php foreach ($breadcrumbs as $breadcrumb) { ?>
            <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
            <?php } ?>
        </div>-->
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading heading">
                    <?php echo $heading_title; ?>
                    <a href="<?php echo $action_insert; ?>"  class="insert-btn"><span><?php echo $button_insert; ?></span></a>
                </div>
                <div class="panel-body">
                    <form action="#" method="post" enctype="multipart/form-data" id="form">
                        <div class="table-responsive">
                            <div class="col-lg-12">
                                <table class="table table-striped table-bordered table-hover" id="dataTable" style="margin-top:20px;" width="700px;" align="center">
                                    <thead>
                                    <tr>
                                        <td aligns="center"><?php echo $column_action; ?></td>
                                        <td align="center"><?php echo $column_name; ?></td>
                                        <td align="center"><?php echo $column_status; ?></td>
                                        <td align="center"><?php echo $column_created_at; ?></td>

                                    </tr>
                                    </thead>
                                    <tbody>
                                </table>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
    var adelete = '<?php echo html_entity_decode($action_delete); ?>';
    var afilter = '<?php echo html_entity_decode($action_filter); ?>';

    $('#btnDelete').click(function() {
        $('#form').attr('action', adelete);
        $('#form').submit();
    });

    $('#btnFilter').click(function() {
        $('#form').attr('action', afilter);
        $('#form').submit();
    });

    $(document).ready(function() {
        $('.dpDate').datepicker({dateFormat: 'yy-mm-dd'});
    });
    //--></script>
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
                'aTargets' : [ 0 ]
            }, {
                'bSearchable' : false,
                'aTargets' : [ 0 ]
            } ]
        });
    });
</script>
<script src="view/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="view/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<?php echo $footer; ?>