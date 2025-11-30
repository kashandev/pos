<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
        <?php echo $error_warning; ?></div>
    <?php } ?>
    <?php  if ($success) { ?>
    <div class="alert alert-success alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
        <?php echo $success; ?></div>
    <?php  } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading heading">
                    <?php echo $heading_title; ?>
                    <ul class="list-nostyle list-inline pull-right">
                        <li><a class="btn btn-outline btn-primary btn-sm" href="<?php echo $action_insert; ?>"><i class="fa fa-plus"></i><?php echo $button_insert; ?></a></li>
                    </ul>
                </div>
                <div class="panel-body">
                    <form action="#" method="post" enctype="multipart/form-data">
                        <div class="table-responsive">
                            <div class="col-lg-12">
                                <table style="margin-top:20px;"  id="dataTable" class="table table-striped table-bordered table-hover"
                                       align="center">
                                    <thead>
                                    <tr>

                                        <td align="center"><?php echo $column_action; ?></td>
                                        <td align="center"><?php echo $sorts_invoice_no; ?></td>
                                        <td align="center"><?php echo $sorts_invoice_date; ?></td>
                                        <td align="center"><?php echo $sorts_supplier; ?></td>
                                        <!--<td align="center"><?php echo $sorts_amount; ?></td>-->


                                    </tr>
                                    </thead>
                                    <tbody>
                                    <!--    <tr class="filter" align="center">
                                            <td>&nbsp;</td>
                                            <td><a id="btnFilter" class="btn btn-default"><span><?php echo $button_filter; ?></span></a></td>
                                            <td><?php echo $filter['title']; ?></td>
                                            <td><?php echo $filter['status']; ?></td>
                                            <td><?php echo $filter['created_at']; ?></td>

                                        </tr>
                                        <?php if ($results) { ?>
                                        <?php foreach ($results as $result) { ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <?php if ($result['selected']) { ?>
                                                <input type="checkbox" name="selected[]" value="<?php echo $result['brand_id']; ?>" checked="checked" />
                                                <?php } else { ?>
                                                <input type="checkbox" name="selected[]" value="<?php echo $result['brand_id']; ?>" />
                                                <?php } ?>
                                            </td>
                                            <td align="center">
                                                <?php foreach ($result['action'] as $action) { ?>
                                                 <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a>
                                                <?php } ?>
                                            </td>
                                            <td align="left"><?php echo $result['title']; ?></td>

                                            <td align="left"><?php echo $result['status']; ?></td>
                                            <td align="left"><?php echo $result['created_at']; ?></td>

                                        </tr>
                                        <?php } ?>
                                        <?php } else { ?>
                                        <tr>
                                            <td class="center" colspan="7"><?php echo $text_no_results; ?></td>
                                        </tr>
                                        <?php } ?> !-->
                                    </tbody>
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
        $('.dpDate').datepicker({dateFormat: 'dd-mm-yy'});
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
            , "aaSorting": [[ 1, "desc" ]]

        });

//        jQuery('#btn_filter').click(function() {
//            
////            oTable.fnFilter(jQuery('#filter_name').val(),1 );
////            oTable.fnFilter(jQuery('#filter_last_name').val(),3 );
////            oTable.fnFilter(jQuery('#filter_email').val(),4 );
////            oTable.fnFilter(jQuery('#filter_email').val(),4 );
//            
//            oTable.fnAdjustColumnSizing();
//        })

        oTable.fnAdjustColumnSizing();
    });
</script>
<script src="view/js/plugins/dataTables/jquery.dataTables.js"></script>
<script src="view/js/plugins/dataTables/dataTables.bootstrap.js"></script>
<script>
    $(document).ready(function() {
        $('#dataTables-example').dataTable();
    });
</script>
<?php echo $footer; ?> 