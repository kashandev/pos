<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
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
                        <?php if(isset($isEdit) && $isEdit==1): ?>
                        <?php if($is_post == 0): ?>
                        <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                            <i class="fa fa-thumbs-up"></i>
                            &nbsp;<?php echo $lang['post']; ?>
                        </a>
                        <?php endif; ?>
                        <!--
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button>
                        -->
                        <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                        <?php endif; ?>
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                        <i class="fa fa-floppy-o"></i>
                        &nbsp;<?php echo $lang['save']; ?>
                        </button>
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
                                <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                                <input type="hidden" value="<?php echo $bom_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="Auto" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['base_currency']; ?></label>
                                            <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                                            <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_currency']; ?></label>
                                            <select class="form-control" id="document_currency_id" name="document_currency_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($currencys as $currency): ?>
                                                <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['conversion_rate']; ?></label>
                                            <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" onchage="calcNetAmount()" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_code']; ?></label>
                                            <input onchange="getMasterProductByCode(this);" class="form-control" type="text" id="product_code" name="product_code" value="<?php echo $product_code; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_name']; ?></label>
                                            <div class="input-group">
                                                <select onchange="getMasterProductById(this);" class="form-control" id="product_id" name="product_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach($products as $product): ?>
                                                    <option value="<?php echo $product['product_id']; ?>"<?php echo ($product['product_id']==$product_id?'selected="true"':''); ?>><?php echo $product['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <span class="input-group-btn ">
                                                    <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="product_id" data-field="product_id" data-callback="setMasterProduct">
                                                        <i class="fa fa-search"></i>
                                                    </button>
                                                </span>
                                            </div>
                                            <label for="product_id" class="error" style="display: none;"></label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['unit']; ?></label>
                                            <input class="form-control" type="text" id="unit" name="unit" value="<?php echo $unit; ?>" readonly/>
                                            <input class="form-control" type="hidden" id="unit_id" name="unit_id" value="<?php echo $unit_id; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <table id="tblBOMDetail" class="table table-striped table-bordered">
                                            <thead>
                                            <tr align="center">
                                                <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                                                <td style="width: 200px;"><?php echo $lang['product_name']; ?></td>
                                                <td style="width: 150px;"><?php echo $lang['unit']; ?></td>
                                                <td style="width: 120px;"><?php echo $lang['quantity']; ?></td>
                                                <td style="width: 3%;"><a id="btnAddGrid" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                            </tr>
                                            </thead>
                                            <tbody >
                                            <?php $grid_row = 0; ?>
                                            <?php foreach($bom_details as $detail): ?>
                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                <td>
                                                    <input onchange="getProductByCode(this);" type="text" class="form-control" name="bom_details[<?php echo $grid_row; ?>][product_code]" id="bom_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <select onchange="getProductById(this);" class="form-control select2" id="bom_detail_product_id_<?php echo $grid_row; ?>" name="bom_details[<?php echo $grid_row; ?>][product_id]" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($products as $product): ?>
                                                            <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':'');?>><?php echo $product['name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <span class="input-group-btn ">
                                                            <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="bom_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                                <i class="fa fa-search"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control" name="bom_details[<?php echo $grid_row; ?>][unit]" id="bom_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly="true" />
                                                    <input type="hidden" class="form-control" name="bom_details[<?php echo $grid_row; ?>][unit_id]" id="bom_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" />
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control fPDecimal" name="bom_details[<?php echo $grid_row; ?>][qty]" id="bom_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                                </td>
                                                <td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
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
                                <?php if(isset($isEdit) && $isEdit==1): ?>
                                <?php if($is_post == 0): ?>
                                <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                                    <i class="fa fa-thumbs-up"></i>
                                    &nbsp;<?php echo $lang['post']; ?>
                                </a>
                                <?php endif; ?>
                                <!--
                                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                                    <i class="fa fa-balance-scale"></i>
                                    &nbsp;<?php echo $lang['ledger']; ?>
                                </button>
                                -->
                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a>
                                <?php endif; ?>
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                                <i class="fa fa-floppy-o"></i>
                                &nbsp;<?php echo $lang['save']; ?>
                                </button>
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
    <script type="text/javascript" src="../admin/view/js/production/bom.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $lang = <?php echo json_encode($lang) ?>;
        var $grid_row = '<?php echo $grid_row; ?>';
        var $products = <?php echo json_encode($products) ?>;
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>
