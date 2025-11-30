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
                            <?php foreach ($breadcrumbs as $breadcrumb) : ?>
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
                                <?php if ($success) { ?>
                                    <div class="alert alert-success alert-dismissable">
                                        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                        <?php echo $success; ?>
                                    </div>
                                <?php  } ?>
                            </div><!-- /.box-header -->
                            <div class="box-body">
                                <form action="<?php echo $action_update; ?>" method="post" enctype="multipart/form-data" id="form">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['purchase_discount_account']; ?></label>
                                                <select class="form-control" id="purchase_discount_account_id" name="purchase_discount_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $purchase_discount_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="purchase_discount_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['sale_discount_account']; ?></label>
                                                <select class="form-control" id="sale_discount_account_id" name="sale_discount_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $sale_discount_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="sale_discount_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['sale_tax_account']; ?></label>
                                                <select class="form-control" id="sale_tax_account_id" name="sale_tax_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $sale_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="sale_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['additional_sales_tax_account']; ?></label>
                                                <select class="form-control" id="additional_sale_tax_account_id" name="additional_sale_tax_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $additional_sale_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="additional_sale_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['withholding_tax_account']; ?></label>
                                                <select class="form-control" id="withholding_tax_account_id" name="withholding_tax_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $withholding_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="withholding_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['income_tax_account']; ?></label>
                                                <select class="form-control" id="income_tax_account_id" name="income_tax_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $income_tax_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="income_tax_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['gr_ir_account']; ?></label>
                                                <select class="form-control" id="gr_ir_account_id" name="gr_ir_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $gr_ir_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="gr_ir_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['inventory_account']; ?></label>
                                                <select class="form-control" multiple="multiple" id="inventory_account_id" name="inventory_account_id[]">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'], $inventory_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="inventory_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['revenue_account']; ?></label>
                                                <select class="form-control" multiple="multiple" id="revenue_account_id" name="revenue_account_id[]">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'], $revenue_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="revenue_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['cogs_account']; ?></label>
                                                <select class="form-control" multiple="multiple" id="cogs_account_id" name="cogs_account_id[]">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'], $cogs_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="cogs_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['adjustment_account']; ?></label>
                                                <select class="form-control" multiple="multiple" id="adjustment_account_id" name="adjustment_account_id[]">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo (in_array($coa['coa_level3_id'], $adjustment_account_id) ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="adjustment_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['pl_account']; ?></label>
                                                <select class="form-control" id="pl_account_id" name="pl_account_id[]">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $pl_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="pl_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['sale_cartage_account']; ?></label>
                                                <select class="form-control" id="cartage_account_id" name="cartage_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $cartage_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="cartage_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['purchase_cartage_account']; ?></label>
                                                <select class="form-control" id="purchase_cartage_account_id" name="purchase_cartage_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $purchase_cartage_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="purchase_cartage_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['sale_return_account']; ?></label>
                                                <select class="form-control" id="sale_return_account_id" name="sale_return_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $sale_return_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="sale_return_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['purchase_return_account']; ?></label>
                                                <select class="form-control" id="purchase_return_account_id" name="purchase_return_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $purchase_return_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="purchase_return_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['custom_duty_account']; ?></label>
                                                <select class="form-control" id="custom_duty_account_id" name="custom_duty_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $custom_duty_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="custom_duty_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['additional_custom_duty_account']; ?></label>
                                                <select class="form-control" id="additional_custom_duty_account_id" name="additional_custom_duty_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $additional_custom_duty_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="additional_custom_duty_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['regulatory_duty_account']; ?></label>
                                                <select class="form-control" id="regulatory_duty_account_id" name="regulatory_duty_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $regulatory_duty_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="regulatory_duty_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['freight_account']; ?></label>
                                                <select class="form-control" id="freight_account_id" name="freight_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $freight_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="freight_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['contra_account']; ?></label>
                                                <select class="form-control" id="contra_account_id" name="contra_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $contra_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="contra_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 hide">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['cost_center_account']; ?></label>
                                                <select class="form-control" id="cost_center_account_id" name="cost_center_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $cost_center_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="cost_center_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row hide">
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['labour_charges_account']; ?></label>
                                                <select class="form-control" id="labour_charges_account_id" name="labour_charges_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $labour_charges_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="labour_charges_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['misc_charges_account']; ?></label>
                                                <select class="form-control" id="misc_charges_account_id" name="misc_charges_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $misc_charges_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="misc_charges_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['rent_charges_account']; ?></label>
                                                <select class="form-control" id="rent_charges_account_id" name="rent_charges_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $rent_charges_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="rent_charges_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_payable_account']; ?></label>
                                                <select class="form-control" id="branch_payable_account_id" name="branch_payable_account_id">
                                                    <option value="">&nbsp;</option>
                                                    <?php foreach ($coas as $coa) : ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $branch_payable_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <label for="branch_payable_account_id" style="display: none;" class="error">&nbsp;</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['auto_generate_product_code']; ?></label>
                                                <input name="auto_generate_product_code" id="auto_generate_product_code" value="1" type="checkbox" <?php echo ($auto_generate_product_code == 1 ? 'checked="true"' : ''); ?>>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label><span class="required">*</span>&nbsp;<?php echo $lang['allow_out_of_stock']; ?></label>
                                                <input name="allow_out_of_stock" id="allow_out_of_stock" value="1" type="checkbox" <?php echo ($allow_out_of_stock == 1 ? 'checked="true"' : ''); ?>>
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
        <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
        <script>
            jQuery('#form').validate(<?php echo $strValidation; ?>);
        </script>
        <?php echo $page_footer; ?>
        <?php echo $column_right; ?>
    </div><!-- ./wrapper -->
    <?php echo $footer; ?>
</body>

</html>