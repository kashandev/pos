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
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_name']; ?></label>
                                            <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_code']; ?></label>
                                            <input type="text" name="currency_code" value="<?php echo $currency_code; ?>" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['entry_symbol_left']; ?></label>
                                            <input type="text" name="symbol_left" value="<?php echo $symbol_left; ?>" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['entry_symbol_right']; ?></label>
                                            <input type="text" name="symbol_right" value="<?php echo $symbol_right; ?>" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['entry_decimal_place']; ?></label>
                                            <input type="text" name="decimal_place" value="<?php echo $decimal_place; ?>" class="form-control fPInteger"/>
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['entry_value']; ?></label>
                                            <input type="text" name="value" value="<?php echo $value; ?>" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            &nbsp;&nbsp;
                                            <div class="table-responsive table-bordered tbl_grid">
                                                <table id="currency" class="table form-grid table-striped table-hover flat-grid">
                                                    <thead>
                                                    <tr align="center">
                                                        <td style="width: 15%;"><?php echo $lang['column_date']; ?></td>
                                                        <td style="width: 30%;"><?php echo $lang['column_rate']; ?></td>
                                                        <td style="width: 5%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a></td>
                                                    </tr>
                                                    </thead>
                                                    <?php $grid_row = 0; ?>
                                                    <?php foreach($currency_rates as $detail): ?>
                                                    <tbody id="grid_row_<?php echo $grid_row; ?>" row_id="<?php echo $grid_row; ?>">
                                                    <tr>
                                                        <td><input type="text" class="dtpDate" id="date_<?php echo $grid_row; ?>" name="currency_rates[<?php echo $grid_row; ?>][date]" value="<?php echo $detail['date']; ?>" /></td>
                                                        <td><input type="text" class="fDecimal" id="rate_<?php echo $grid_row; ?>" name="currency_rates[<?php echo $grid_row; ?>][rate]" value="<?php echo $detail['rate']; ?>" /></td>
                                                        <td><a href="javascript:void(0);" onclick="removeGridRow(<?php echo $grid_row; ?>);"><span class="fa fa-times"></span></a></td>
                                                    </tr>
                                                    </tbody>
                                                    <?php $grid_row++; ?>
                                                    <?php endforeach; ?>
                                                    <tfoot>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
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
    <script type="text/javascript">
        var grid_row = '<?php echo $grid_row; ?>';
        function addGridRow() {
            html = '<tbody id="grid_row_' + grid_row + '" row_id="'+ grid_row +'">';
            html +='<tr>';
            html +='<td><input type="text" class="dtpDate" id="date_'+ grid_row +'" name="currency_rates['+grid_row+'][date]" value="" /></td>';
            html +='<td><input type="text" class="fDecimal" id="rate_'+ grid_row +'" name="currency_rates['+grid_row+'][rate]" value="" /></td>';
            html +='<td><a href="javascript:void(0);" onclick="removeGridRow('+grid_row+');"><span class="fa fa-times"></span></a></td>';
            html +='</tr>';
            html +='</tbody>';
            $('#currency thead').after(html);
            grid_row++;
            setFieldFormat();
            addRowButton();
        };


        function addRowButton() {
            row_id = $('#currency tbody:last').attr('row_id');
            var btnAdd = '<a id="btnAddGrid" title="Add" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a>';
            var btnRemove = '<a title="text_delete" href="javascript:void(0);" onclick="removeGridRow('+row_id+');"><span class="fa fa-times"></span></a>';
            $('#btnAddGrid').remove();
            if(row_id) {
                $('#currency tbody:first td:last').html(btnAdd+btnRemove);
                $('#currency tbody:first input:first').focus();
            } else {
                $('#currency thead td:last').html(btnAdd);
            }
        }

        function removeGridRow(grid_row) {
            $('#grid_row_'+grid_row).remove();
            if($('#currency tbody').length > 0) {
                $('#supplier_id').attr('disabled','true');
            } else {
                $('#supplier_id').prop('disabled',false);
            }
            addRowButton();
            updatePeopleCombo();
        }
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>