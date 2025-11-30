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
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
                            <i class="fa fa-floppy-o"></i>
                            &nbsp;<?php echo $lang['save']; ?>
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
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-lg-12">
                                            <table id="tbl_mapping_coa" class="table table-bordered table-striped">
                                                <thead>
                                                <tr align="center">
                                                    <td style="width: 3%;"><a onclick="addGridRow();" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td width="49%"><?php echo $lang['mapping_type_name']; ?></td>
                                                    <td width="49%"><?php echo $lang['level3']; ?></td>
                                                    <td style="width: 3%;"><a onclick="addGridRow();" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row = 0; ?>
                                                <?php foreach($mapping_coas as $mapping_coa): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                    <td>
                                                        <select class="" name="mapping_coas[<?php echo $grid_row; ?>][mapping_type_id]">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($mapping_types as $type): ?>
                                                            <option value="<?php echo $type['mapping_type_id']; ?>" <?php echo ($mapping_coa['mapping_type_id'] == $type['mapping_type_id']?'selected="selected"':''); ?>><?php echo $type['name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="" name="mapping_coas[<?php echo $grid_row; ?>][coa_level3_id]" style="width: auto;">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($coas as $coa): ?>
                                                            <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($mapping_coa['coa_level3_id'] == $coa['coa_level3_id']?'selected="selected"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-sm btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $grid_row++; ?>
                                                <?php endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
                                    <i class="fa fa-floppy-o"></i>
                                    &nbsp;<?php echo $lang['save']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
    <script type="text/javascript">
        var grid_row = '<?php echo $grid_row; ?>';
        function addGridRow() {
            $html = '<tr id="grid_row_'+grid_row+'" data-row_id="'+grid_row+'">';
            $html += '<td>';
            $html += '<a href="javascript:void(0);" class="btn btn-sm btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>';
            $html += '</td>';
            $html += '<td>';
            $html += '<select class="form-control select2" name="mapping_coas['+grid_row+'][mapping_type_id]">';
            $html += '<option value="">&nbsp;</option>';
            <?php foreach($mapping_types as $type): ?>
            $html += '<option value="<?php echo $type['mapping_type_id']; ?>">';
            $html += '<?php echo $type['name']; ?>';
            $html += '</option>';
            <?php endforeach; ?>
            $html += '</select>';
            $html += '</td>';
            $html += '<td>';
            $html += '<select class="form-control select2" name="mapping_coas['+grid_row+'][coa_level3_id]" style="width: auto;">';
            $html += '<option value="">&nbsp;</option>';
            <?php foreach($coas as $coa): ?>
            $html += '<option value="<?php echo $coa['coa_level3_id']; ?>">';
            $html += '<?php echo $coa['level3_display_name']; ?>';
            $html += '</option>';
            <?php endforeach; ?>
            $html += '</select>';
            $html += '</td>';
            $html += '<td>';
            $html += '<a href="javascript:void(0);" class="btn btn-sm btn-danger" title="Remove" onclick="removeRow(this);"><i class="fa fa-times"></i></a>';
            $html += '</td>';
            $html += '</tr>';

            $('#tbl_mapping_coa tbody').prepend($html);

            jQuery('#grid_row_'+grid_row+' .select2').select2({width: '100%'});
            grid_row++;
        };

        function removeRow($obj) {
            var row_id = $($obj).parent().parent().data('row_id');
            $('#grid_row_'+row_id).remove();
        }
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>