<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <!--<div class="breadcrumb">
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
        <div class="box">
            <!--<div class="heading">
                <h1><img src="view/image/user.png" alt="" /> <?php echo $heading_title; ?></h1>
                <div class="buttons">
                    <a href="<?php echo $action_insert; ?>" class="button"><span><?php echo $button_insert; ?></span></a>
                    <a id="btnDelete" class="button"><span><?php echo $button_delete; ?></span></a>
                </div>
            </div>-->
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <?php echo $heading_title; ?>
                        </div>
                        <form action="#" method="post" enctype="multipart/form-data" id="form">
                            <div class="table-responsive">
                                <div class="col-lg-12">
                                    <table class="table table-striped table-bordered table-hover" style="margin-top:20px;">
                                        <thead>
                                        <tr>
                                            <td width="1" style="text-align: center;">
                                                <input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" />
                                            </td>
                                            <td aligns="center"><?php echo $column_action; ?></td>
                                            <td align="center"><?php echo $column_name; ?></td>
                                            <td align="center"><?php echo $column_gst_no; ?></td>
                                            <td align="center"><?php echo $column_currency; ?></td>
                                            <td align="center"><?php echo $column_industry; ?></td>
                                            <td align="center"><?php echo $column_debit_limit; ?></td>
                                            <td align="center"><?php echo $column_status; ?></td>
                                            <td align="center"><?php echo $column_created_at; ?></td>

                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="filter">
                                            <td>&nbsp;</td>
                                            <<<<<<< .mine
                                            <td align="center"><a id="btnFilter"  class="btn btn-default"><span><?php echo $button_filter; ?></span></a></td>
                                            =======
                                            >>>>>>> .r595
                                            <td><?php echo $filter['name']; ?></td>
                                            <td><?php echo $filter['gst_no']; ?></td>
                                            <td><?php echo $filter['currency_id']; ?></td>
                                            <td><?php echo $filter['industry_id']; ?></td>
                                            <td><?php echo $filter['debit_limit']; ?></td>
                                            <td><?php echo $filter['status']; ?></td>
                                            <td><?php echo $filter['created_at']; ?></td>

                                        </tr>
                                        <?php if ($results) { ?>
                                        <?php foreach ($results as $result) { ?>
                                        <tr>
                                            <td style="text-align: center;">
                                                <?php if ($result['selected']) { ?>
                                                <input type="checkbox" name="selected[]" value="<?php echo $result['supplier_id']; ?>" checked="checked" />
                                                <?php } else { ?>
                                                <input type="checkbox" name="selected[]" value="<?php echo $result['supplier_id']; ?>" />
                                                <?php } ?>
                                            </td>
                                            <td align="center">
                                                <?php foreach ($result['action'] as $action) { ?>
                                                <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a>
                                                <?php } ?>
                                            </td>
                                            <td align="left"><?php echo $result['name']; ?></td>
                                            <td align="left"><?php echo $result['gst_no']; ?></td>
                                            <td align="left"><?php echo $result['currency']; ?></td>
                                            <td align="left"><?php echo $result['industry']; ?></td>
                                            <td align="right"><?php echo number_format($result['debit_limit']); ?></td>
                                            <td align="left"><?php echo $result['status']; ?></td>
                                            <td align="left"><?php echo $result['created_at']; ?></td>
                                        </tr>
                                        <?php } ?>
                                        <?php } else { ?>
                                        <tr>
                                            <td class="center" colspan="9"><?php echo $text_no_results; ?></td>
                                        </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>

                                    <div class="buttons">
                                        <a href="<?php echo $action_insert; ?>"  class="btn btn-default"><span><?php echo $button_insert; ?></span></a>
                                        <a id="btnDelete"  class="btn btn-default"><span><?php echo $button_delete; ?></span></a>
                                    </div>

                                </div>
                            </div>
                        </form>
                        <div class="pagination"><?php echo $pagination; ?></div>
                    </div>
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
<?php echo $footer; ?> 