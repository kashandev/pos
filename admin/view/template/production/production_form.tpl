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
                <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="validateForm();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
        <input type="hidden" value="<?php echo $production_id; ?>" name="document_id" id="document_id" />
        <input type="hidden" value="<?php echo $restrict_out_of_stock; ?>" name="restrict_out_of_stock" id="restrict_out_of_stock" />
        <div class="row">
            <div class="col-sm-3 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['document_no']; ?></label>
                    <input class="form-control" type="text" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="Auto" />
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="form-group">
                    <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
                    <input class="form-control" type="text" name="document_date" value="<?php echo $document_date; ?>" readonly />
                </div>
            </div>
            <div class="col-sm-3 col-xs-6">
                <div class="form-group">
                    <label><span class="required">*</span>&nbsp;<?php echo $lang['warehouse'].$warehouse_id; ?></label>
                    <select class="form-control" id="warehouse_id" name="warehouse_id" >
                        <option value="">&nbsp;</option>
                        <?php foreach($warehouses as $warehouse): ?>
                        <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse['warehouse_id']==$warehouse_id?'selected="true"':'');?>><?php echo $warehouse['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="warehouse_id" class="error" style="display: none;"></label>
                </div>
            </div>
        </div>
        <div class="row hide">
            <div class="col-sm-3 col-xs-4">
                <div class="form-group">
                    <label><?php echo $lang['base_currency']; ?></label>
                    <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                    <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
                </div>
            </div>
            <div class="col-sm-3 col-xs-4">
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
            <div class="col-sm-3 col-xs-4">
                <div class="form-group">
                    <label><?php echo $lang['conversion_rate']; ?></label>
                    <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" onchage="calcNetAmount()" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-4">
                <div class="form-group">
                    <label><?php echo $lang['product_code']; ?></label>
                    <input onchange="getMasterProductByCode(this);" class="form-control" type="text" id="product_code" name="product_code" value="<?php echo $product_code; ?>" />
                </div>
            </div>
            <div class="col-sm-3 col-xs-4">
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
            <div class="col-sm-3 col-xs-4">
                <div class="form-group">
                    <label><?php echo $lang['unit']; ?></label>
                    <input class="form-control" type="text" id="unit" name="unit" value="<?php echo $unit; ?>" readonly/>
                    <input class="form-control" type="hidden" id="unit_id" name="unit_id" value="<?php echo $unit_id; ?>" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-3 col-xs-12">
                <div class="form-group">
                    <label><?php echo $lang['expected_quantity']; ?></label>
                    <input onchange="calculateQuantity(this);" class="form-control fPDecimal text-right" type="text" id="expected_quantity" name="expected_quantity" value="<?php echo $expected_quantity; ?>" />
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="form-group">
                    <label><?php echo $lang['actual_quantity']; ?></label>
                    <input onchange="calculateRate();" class="form-control fPDecimal text-right" type="text" id="actual_quantity" name="actual_quantity" value="<?php echo $actual_quantity; ?>" />
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="form-group">
                    <label><?php echo $lang['total_amount']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" id="amount" name="amount" value="<?php echo $amount; ?>" readonly="true"/>
                </div>
            </div>
            <div class="col-sm-3 col-xs-12">
                <div class="form-group">
                    <label><?php echo $lang['rate']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" id="rate" name="rate" value="<?php echo $rate; ?>" readonly="true"/>
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
                <div class="table-responsive">
                    <table id="tblProductionDetail" class="table table-striped table-bordered">
                        <thead>
                        <tr align="center">
                            <td style="width: 120px;"><?php echo $lang['warehouse']; ?></td>
                            <td style="width: 120px;"><?php echo $lang['product_code']; ?></td>
                            <td style="width: 200px;"><?php echo $lang['product_name']; ?></td>
                            <td  style="width: 150px;"><?php echo $lang['unit']; ?></td>
                            <td hidden="hidden" style="width: 120px;"><?php echo $lang['unit_quantity']; ?></td>
                            <td style="width: 120px;"><?php echo $lang['expected_quantity']; ?></td>
                            <td style="width: 120px;"><?php echo $lang['actual_quantity']; ?></td>
                            <td hidden="hidden" style="width: 120px;"><?php echo $lang['cog_rate']; ?></td>
                            <td hidden="hidden" style="width: 120px;"><?php echo $lang['cog_amount']; ?></td>
                        </tr>
                        </thead>
                        <tbody >
                        <?php $row_id = 0; ?>
                        <?php foreach($production_details as $row): ?>
                        <tr id="row_id_<?php echo $row_id; ?>" data-row_id="<?php echo $row_id; ?>">
                            <td>
                                <select class="form-control" id="production_detail_warehouse_id_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][warehouse_id]" >
                                    <option value="">&nbsp;</option>
                                    <?php foreach($warehouses as $warehouse): ?>
                                    <option value="<?php echo $warehouse['warehouse_id']; ?>"<?php echo ($warehouse['warehouse_id']==$row['warehouse_id']?'selected="true"':'')?>><?php echo $warehouse['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" class="form-control" id="production_detail_product_code_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][product_code]" value="<?php echo $row['product_code'];?>" readonly="true"/></td>
                            <td>
                                <input type="hidden" class="form-control" id="production_detail_product_id_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][product_id]" value="<?php echo $row['product_id'];?>"/>
                                <input type="text" class="form-control" id="production_detail_product_name_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][product_name]" value="<?php echo $row['product_name'];?>" readonly="true"/>
                            </td>
                            <td>
                                <input type="hidden" class="form-control" id="production_detail_unit_id_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][unit_id]" value="<?php echo $row['unit_id'];?>"/>
                                <input type="text" class="form-control" id="production_detail_unit_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][unit]" value="<?php echo $row['unit'];?>" readonly="true"/>
                            </td>
                            <td hidden="hidden">
                                <input type="text" class="form-control text-right" id="production_detail_unit_quantity_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][unit_quantity]" value="<?php echo $row['unit_quantity'];?>" readonly="true"/>
                            </td>
                            <td>
                                <input type="text" class="form-control text-right" id="production_detail_expected_quantity_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][expected_quantity]" value="<?php echo $row['expected_quantity'];?>" readonly="true"/>
                            </td>
                            <td><input onchange="calculateRowTotal(<?php echo $row_id; ?>);" type="text" class="form-control text-right" id="production_detail_actual_quantity_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][actual_quantity]" value="<?php echo $row['actual_quantity'];?>" /></td>
                            <td hidden="hidden"><input type="text" class="form-control text-right" id="production_detail_cog_rate_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][cog_rate]" value="<?php echo $row['cog_rate'];?>" readonly="true"/></td>
                            <td hidden="hidden"><input type="text" class="form-control text-right" id="production_detail_cog_amount_<?php echo $row_id; ?>" name="production_details[<?php echo $row_id; ?>][cog_amount]" value="<?php echo $row['cog_amount'];?>" readonly="true" /></td>
                        </tr>
                        <?php $row_id++; ?>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['viscosity']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="viscosity" value="<?php echo $viscosity; ?>" />
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['t_max']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="t_max" value="<?php echo $t_max; ?>" />
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['stablizer']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="stablizer" value="<?php echo $stablizer; ?>" />
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['gel_time']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="gel_time" value="<?php echo $gel_time; ?>" />
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['coure_time']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="coure_time" value="<?php echo $coure_time; ?>" />
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="form-group">
                    <label><?php echo $lang['water']; ?></label>
                    <input class="form-control fPDecimal text-right" type="text" name="water" value="<?php echo $water; ?>" />
                </div>
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
        <button type="button" class="btn btn-primary" href="javascript:void(0);" onclick="validateForm();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
<script type="text/javascript" src="plugins/dataTables/jquery.dataTables.js"></script>
<script type="text/javascript" src="plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="../admin/view/js/production/production.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
    var $lang = <?php echo json_encode($lang); ?>;
    var $grid_row = '<?php echo $grid_row; ?>';
    var $UrlGetBOM = '<?php echo $href_get_bom; ?>';
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>
