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
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <a class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
                                <input type="hidden" value="<?php echo $sale_discount_policy_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group hide">
                                            <label><?php echo $lang['partner_type']; ?></label>
                                            <select class="form-control select2-default" id="partner_type_id" name="partner_type_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($partner_types as $partner_type): ?>
                                                <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_name']; ?></label>
                                            <select class="form-control select2-default" id="partner_id" name="partner_id">
                                                <?php if(count($partners) != 1): ?>
                                                <option value="">&nbsp;</option>
                                                <?php endif; ?>
                                                <?php foreach($partners as $partner): ?>
                                                <option value="<?php echo $partner['partner_id']; ?>" <?php echo ($partner_id == $partner['partner_id']?'selected="true"':''); ?>><?php echo $partner['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="partner_id" class="error" style="display: none;"></label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['start_date']; ?></label>
                                            <input class="form-control dtpDate" id="start_date" type="text" name="start_date" value="<?php echo $start_date; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['end_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" id="end_date" name="end_date" value="<?php echo $end_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <div class="table-responsive form-group">
                                            <table id="tblSaleDiscount" class="table table-striped table-bordered">
                                                <thead>
                                                <tr align="center">
                                                    <td style="width: 3px;"><a id="btnAddDiscount" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td style="width: 100px;"><?php echo $lang['product_category']; ?></td>
                                                    <td style="width: 300px;"><?php echo $lang['product_name']; ?></td>
                                                    <td style="width: 150px;"><?php echo $lang['discount_percent']; ?></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $policy_row = count($sale_discount_policy_details);$policy_row_desc = count($sale_discount_policy_details)-1; ?>
                                                <?php foreach($sale_discount_policy_details as $row_id => $detail): ?>
                                                <tr id="policy_row_<?php echo $policy_row_desc; ?>" data-policy_row="<?php echo $policy_row_desc; ?>">
                                                    <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>
                                                    <td>
                                                        <select class="form-control" id="policy_<?php echo $policy_row_desc; ?>_product_category_id" name="policies[<?php echo $policy_row_desc; ?>][product_category_id]">
                                                            <option value="0">&nbsp;</option>
                                                            <?php foreach($product_categories as $category): ?>
                                                            <option value="<?php echo $category['product_category_id']; ?>" <?php echo ($category['product_category_id']==$detail['product_category_id']?'selected="true"':''); ?>><?php echo $category['name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select class="form-control" id="policy_<?php echo $policy_row_desc; ?>_product_id" name="policies[<?php echo $policy_row_desc; ?>][product_id]">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($products[$detail['product_category_id']] as $product): ?>
                                                            <option value="<?php echo $product['product_id']; ?>" <?php echo ($product['product_id']==$detail['product_id']?'selected="true"':''); ?>><?php echo $product['name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="policies[<?php echo $policy_row_desc; ?>][discount_percent]" id="policy_<?php echo $policy_row_desc; ?>_discount_percent" value="<?php echo $detail['discount_percent']; ?>" />
                                                    </td>
                                                </tr>
                                                <?php $policy_row_desc--; ?>
                                                <?php endforeach; ?>
                                                <?php $row_id = count($sale_discount_policy_details); ?>
                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                            <input type="hidden" id="discount_count" name="discount_count" value="<?php echo count($sale_discount_policy_details)?>" />
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
                                <a  class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
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
    <script type="text/javascript" src="../admin/view/js/inventory/sale_discount_policy.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript" src="../admin/view/js/tool/reminder.js"></script>

    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $lang = <?php echo json_encode($lang) ?>;
        var $policy_row = '<?php echo $policy_row; ?>';
        var $product_categories = <?php echo json_encode($product_categories) ?>;
        var $products = <?php echo json_encode($products) ?>;
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>