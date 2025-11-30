<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <div class="content-wrapper">
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
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button>
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
        <section class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="box-header">
                            <?php if ($error_warning): ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $error_warning; ?>
                            </div>
                            <?php elseif ($success): ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="box-body">
                            <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                            <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />
                            <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" id="document_identity" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="auto" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['manual_ref_no']; ?></label>
                                            <input class="form-control" type="text" id="manual_ref_no" name="manual_ref_no" value="<?php echo $manual_ref_no;?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['customer_name']; ?></label>
                                            <select class="form-control" id="customer_id" name="customer_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($customers as $customer): ?>
                                                <option value="<?php echo $customer['customer_id']; ?>" <?php echo ($customer_id == $customer['customer_id']?'selected="selected"':''); ?>"><?php echo $customer['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="customer_id" class="error" style="display: none;">This field is required.</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['ref_document']; ?></label>
                                            <input class="form-control" type="text" id="ref_document_id" name="ref_document_id" onChange="getDocumentData();" value="<?php echo $ref_document_id;?>"  />
                                            <label for="ref_document_id" class="error" style="display: none;">This field is required.</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['gate_in']; ?></label>
                                            <input class="form-control dtpDateTime" type="text" name="gate_in" id="gate_in" value="<?php echo $gate_in;?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['gate_out']; ?></label>
                                            <input class="form-control dtpDateTime" type="text" name="gate_out" id="gate_out" value="<?php echo $gate_out;?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['vehicle_no']; ?></label>
                                            <input class="form-control" type="text" id="vehicle_no" name="vehicle_no" value="<?php echo $vehicle_no;?>" />
                                        </div>
                                    </div>

                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['vehicle_type']; ?></label>
                                            <select class="form-control" name="vehicle_type" id="vehicle_type">
                                                <option value="">&nbsp;</option>
                                                <option value="Mazda" <?php echo ($vehicle_type == 'Mazda' ? 'selected="selected"' :''); ?>><?php echo $lang['Mazda']; ?></option>
                                                <option value="20ft" <?php echo ($vehicle_type == '20ft' ? 'selected="selected"' :''); ?>><?php echo $lang['20ft']; ?></option>
                                                <option value="40ft" <?php echo ($vehicle_type == '40ft' ? 'selected="selected"' :''); ?>><?php echo $lang['40ft']; ?></option>
                                                <option value="20ftF/B" <?php echo ($vehicle_type == '20ftF/B' ? 'selected="selected"' :''); ?>><?php echo $lang['20ft_FB']; ?></option>
                                                <option value="40ftF/B" <?php echo ($vehicle_type == '40ftF/B' ? 'selected="selected"' :''); ?>><?php echo $lang['40ft_FB']; ?></option>
                                                <option value="50ft" <?php echo ($vehicle_type == '50ft' ? 'selected="selected"' :''); ?>><?php echo $lang['50ft']; ?></option>
                                                <option value="10 Wheeler" <?php echo ($vehicle_type == '10 wheeler' ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheeler']; ?></option>
                                                <option value="22 Wheeler" <?php echo ($vehicle_type == '22 Wheeler' ? 'selected="selected"' :''); ?>><?php echo $lang['22_wheeler']; ?></option>
                                                <option value="Hiwa" <?php echo ($vehicle_type == 'Hiwa' ? 'selected="selected"' :''); ?>><?php echo $lang['Hiwa']; ?></option>
                                                <option value="LowBed" <?php echo ($vehicle_type == 'LowBed' ? 'selected="selected"' :''); ?>><?php echo $lang['LowBed']; ?></option>
                                            </select>
                                            <label for="vehicle_id" class="error" style="display: none;">This field is required.</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['driver_name']; ?></label>
                                            <input class="form-control" type="text" id="driver_name" name="driver_name" value="<?php echo $driver_name;?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['driver_cell']; ?></label>
                                            <input class="form-control" type="text" id="driver_cell" name="driver_cell" value="<?php echo $driver_cell;?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['broker_name']; ?></label>
                                            <select class="form-control" id="supplier_id" name="supplier_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($suppliers as $supplier): ?>
                                                <option value="<?php echo $supplier['supplier_id']; ?>" <?php echo ($supplier_id == $supplier['supplier_id']?'selected="selected"':''); ?> data-mobile_no="<?php echo $supplier['mobile']; ?>"><?php echo $supplier['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['broker_cell']; ?></label>
                                            <input class="form-control" type="text" id="broker_cell" name="broker_cell" value="<?php echo $broker_cell;?>" readonly />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['broker_freight']; ?></label>
                                            <input class="form-control fDecimal" type="text" id="broker_freight" name="broker_freight" value="<?php echo $broker_freight;?>" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['commodity']; ?></label>
                                            <select class="form-control" name="commodity" id="commodity">
                                                <option value="">&nbsp;</option>
                                                <option value="Bulk" <?php echo ($commodity == 'Bulk' ? 'selected="selected"' :''); ?>><?php echo $lang['Bulk']; ?></option>
                                                <option value="Containers" <?php echo ($commodity == 'Containers' ? 'selected="selected"' :''); ?>><?php echo $lang['Containers']; ?></option>
                                                <option value="Bags" <?php echo ($commodity == 'Bags' ? 'selected="selected"' :''); ?>><?php echo $lang['Bags']; ?></option>
                                                <option value="LCL" <?php echo ($commodity == 'LCL' ? 'selected="selected"' :''); ?>><?php echo $lang['LCL']; ?></option>
                                                <option value="Packing" <?php echo ($commodity == 'Packing' ? 'selected="selected"' :''); ?>><?php echo $lang['Packing']; ?></option>
                                                <option value="Pallets" <?php echo ($commodity == 'Pallets' ? 'selected="selected"' :''); ?>><?php echo $lang['Pallets']; ?></option>
                                            </select>
                                            <label for="commodity" class="error" style="display: none;">This field is required.</label>
                                        </div>
                                    </div>
                                </div>

                                <div id="CommodityBulk" class="row commodity">
                                    <div class="col-md-12">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">Bulk</div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['project_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_project_name" name="woc[Bulk][project_name]" value="<?php echo $woc['Bulk']['project_name'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['bilty_no']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Bulk][bilty_no]" value="<?php echo $woc['Bulk']['bilty_no'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['vehicle_size']; ?></label>
                                                            <select class="form-control"  id="woc_bulk_vehicle_size" name="woc[Bulk][vehicle_size]" >
                                                                <option value="">&nbsp;</option>
                                                                <option value="<?php echo $lang['22_wheelers']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['22_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['22_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['10_wheelers']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['10_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['h_wall']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['h_wall'] ? 'selected="selected"' :''); ?>><?php echo $lang['h_wall']; ?></option>
                                                            </select>
                                                            <label for="vehicle_size" class="error" style="display: none;">This field is required.</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['commodity']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_commodity" name="woc[Bulk][commodity]" value="<?php echo $woc['Bulk'][commodity];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['fpo']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_po_no" name="woc[Bulk][po_no]" value="<?php echo $woc['Bulk'][po_no];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['vessel_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_vessel_name" name="woc[Bulk][vessel_name]" value="<?php echo $woc['Bulk'][vessel_name];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_point']; ?></label>
                                                            <select class="form-control select2" name="woc[Bulk][loading_point_id]" id="woc_bulk_loading_point_id" >
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Bulk']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['destination']; ?></label>
                                                            <select class="form-control select2" name="woc[Bulk][destination_id]" id="woc_bulk_destination_id">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Bulk']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_net_weight" name="woc[Bulk][net_weight]" value="<?php echo $woc['Bulk']['net_weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['tare_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_tare_weight" name="woc[Bulk][tare_weight]" value="<?php echo $woc['Bulk']['tare_weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['gross_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_gross_weight" name="woc[Bulk][gross_weight]" value="<?php echo $woc['Bulk']['gross_weight'];?>" readonly/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['factory_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_factory_weight" name="woc[Bulk][factory_weight]" value="<?php echo $woc['Bulk']['factory_weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['diff_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_diff_weight" name="woc[Bulk][diff_weight]" value="<?php echo $woc['Bulk']['diff_weight'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['qty']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_qty" name="woc[Bulk][qty]" value="<?php echo $woc['Bulk'][qty];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['clearing_agent']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_clearing_agent" name="woc[Bulk][clearing_agent]" value="<?php echo $woc['Bulk']['clearing_agent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['region']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bulk_region" name="woc[Bulk][region]" value="<?php echo $woc['Bulk']['region'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row ">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['rate_per_ton']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_rate_per_ton" name="woc[Bulk][rate_per_ton]" value="<?php echo $woc['Bulk']['rate_per_ton'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_freight" name="woc[Bulk][freight]" value="<?php echo $woc['Bulk']['freight'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total_frieght']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_total_frieght" name="woc[Bulk][total_frieght]" value="<?php echo $woc['Bulk']['total_frieght'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row ">
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_sales_tax_percent" name="woc[Bulk][sales_tax_percent]" value="<?php echo $woc['Bulk']['sales_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_sales_tax_amount" name="woc[Bulk][sales_tax_amount]" value="<?php echo $woc['Bulk']['sales_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['grand_total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_grand_total" name="woc[Bulk][grand_total]" value="<?php echo $woc['Bulk']['grand_total'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_income_tax_percent" name="woc[Bulk][income_tax_percent]" value="<?php echo $woc['Bulk']['income_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_income_tax_amount" name="woc[Bulk][income_tax_amount]" value="<?php echo $woc['Bulk']['income_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class= "col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_net_freight" name="woc[Bulk][net_freight]" value="<?php echo $woc['Bulk']['net_freight'];?>" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['adv_payment']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_adv_payment" name="woc[Bulk][adv_payment]" value="<?php echo $woc['Bulk']['adv_payment'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['balance']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bulk_balance" name="woc[Bulk][balance]" value="<?php echo $woc['Bulk']['balance'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="CommodityContainers" class="row commodity">
                                <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div class="panel-heading">Containers</div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['project_name']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_project_name" name="woc[Containers][project_name]" value="<?php echo $woc['Containers']['project_name'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['commodity']; ?></label>
                                                    <select class="form-control" id="woc_containers_commodity" name="woc[Containers][commodity]">
                                                        <option value="">&nbsp;</option>
                                                        <option value="import" <?php echo ($woc['Containers']['commodity'] == 'import' ? 'selected="selected"' :''); ?>><?php echo $lang['import']; ?></option>
                                                        <option value="export" <?php echo ($woc['Containers']['commodity'] == 'export' ? 'selected="selected"' :''); ?>><?php echo $lang['export']; ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">

                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['bilty_no']; ?></label>
                                                    <input class="form-control" type="text" name="woc[Bulk][bilty_no]" value="<?php echo $woc['Bulk']['bilty_no'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['vehicle_size']; ?></label>
                                                    <select class="form-control" id="vehicle_size" name="vehicle_size" >
                                                        <option value="">&nbsp;</option>
                                                        <option value="<?php echo $lang['22_wheelers']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['22_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['22_wheelers']; ?></option>
                                                        <option value="<?php echo $lang['10_wheelers']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['10_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheelers']; ?></option>
                                                        <option value="<?php echo $lang['h_wall']; ?>" <?php echo ($woc['Bulk']['vehicle_size'] == $lang['h_wall'] ? 'selected="selected"' :''); ?>><?php echo $lang['h_wall']; ?></option>
                                                    </select>
                                                    <label for="vehicle_size" class="error" style="display: none;">This field is required.</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['agent']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_agent" name="woc[Containers][agent]"  value="<?php echo $woc['Containers']['agent'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['container1']; ?></label>
                                                    <input class="form-control" type="text" name="woc[Containers][container1]" value="<?php echo $woc['Containers']['container1'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['container2']; ?></label>
                                                    <input class="form-control" type="text" name="woc[Containers][container2]" value="<?php echo $woc['Containers']['container2'];?>" />
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo $lang['loading_point']; ?></label>
                                                    <select class="form-control select2" id="woc_containers_loading_point_id" name="woc[Containers][loading_point_id]" >
                                                        <option value="">&nbsp;</option>
                                                        <?php foreach($destinations as $destination): ?>
                                                        <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Containers']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label><?php echo $lang['destination']; ?></label>
                                                    <select class="form-control select2" id="woc_containers_destination_id" name="woc[Containers][destination_id]">
                                                        <option value="">&nbsp;</option>
                                                        <?php foreach($destinations as $destination): ?>
                                                        <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Containers']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['free_days']; ?></label>
                                                    <input class="form-control dtpDate" type="text" name="woc[Containers][free_days]" value="<?php echo $woc['Containers']['free_days'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['empty_status']; ?></label>
                                                    <input class="form-control" type="text" name="woc[Containers][empty_status]" value="<?php echo $woc['Containers']['empty_status'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['depositer']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_depositor" name="woc[Containers][depositor]" value="<?php echo $woc['Containers']['depositor'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['bl']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_bl" name="woc[Containers][bl]" value="<?php echo $woc['Containers']['bl'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['container_size']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_size" name="woc[Containers][container_size]" value="<?php echo $woc['Containers']['container_size'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['weight']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_weight" name="woc[Containers][weight]" value="<?php echo $woc['Containers']['weight'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['shipping_line']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_shipping_line" name="woc[Containers][shipping_line]" value="<?php echo $woc['Containers']['shipping_line'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['empty_yard']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_empty_yard" name="woc[Containers][empty_yard]" value="<?php echo $woc['Containers']['empty_yard'];?>" />
                                                </div>
                                                <div class="form-group">
                                                    <label><?php echo $lang['empty_date']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_empty_date" name="woc[Containers][empty_date]" value="<?php echo $woc['Containers']['empty_date'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label><?php echo $lang['po']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_po_no" name="woc[Containers][po_no]" value="<?php echo $woc['Containers']['po_no'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label><?php echo $lang['40ft_std']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_40ft_std" name="woc[Containers][40ft_std]" value="<?php echo $woc['Containers']['40ft_std'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label><?php echo $lang['20ft_std']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_20ft_std" name="woc[Containers][20ft_std]" value="<?php echo $woc['Containers']['20ft_std'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-3">
                                                <div class="form-group">
                                                    <label><?php echo $lang['direct_shifting']; ?></label>
                                                    <input class="form-control" type="text" id="woc_containers_direct_shifting" name="woc[Containers][direct_shifting]" value="<?php echo $woc['Containers']['direct_shifting'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row ">
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['freight']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_freight" name="woc[Containers][freight]" value="<?php echo $woc['Containers']['freight'];?>" />
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['sales_tax']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_sales_tax_percent" name="woc[Containers][sales_tax_percent]" value="<?php echo $woc['Containers']['sales_tax_percent'];?>" />
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['sales_tax_amount']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_sales_tax_amount" name="woc[Containers][sales_tax_amount]" value="<?php echo $woc['Containers']['sales_tax_amount'];?>" readonly/>
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['grand_total']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_grand_total" name="woc[Containers][grand_total]" value="<?php echo $woc['Containers']['grand_total'];?>" readonly/>
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['income_tax']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_income_tax_percent" name="woc[Containers][income_tax_percent]" value="<?php echo $woc['Containers']['income_tax_percent'];?>" />
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['income_tax_amount']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_income_tax_amount" name="woc[Containers][income_tax_amount]" value="<?php echo $woc['Containers']['income_tax_amount'];?>" readonly/>
                                                </div>
                                            </div>
                                            <div class= "col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['net_freight']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_net_freight" name="woc[Containers][net_freight]" value="<?php echo $woc['Containers']['net_freight'];?>" readonly />
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['adv_payment']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_adv_payment" name="woc[Containers][adv_payment]" value="<?php echo $woc['Containers']['adv_payment'];?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-4">
                                                <div class="form-group">
                                                    <label><?php echo $lang['balance']; ?></label>
                                                    <input class="form-control fDecimal" type="text" id="woc_containers_balance" name="woc[Containers][balance]" value="<?php echo $woc['Containers']['balance'];?>" readonly />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                </div>

                                <div id="CommodityBags" class="row commodity">
                                    <div class="col-md-12">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">Bags</div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['project_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bags_project_name" name="woc[Bags][project_name]" value="<?php echo $woc['Bags']['project_name'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['bilty_no']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Bags][bilty_no]" value="<?php echo $woc['Bags']['bilty_no'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['vehicle_size']; ?></label>
                                                            <select class="form-control" id="vehicle_size" name="vehicle_size" >
                                                                <option value="">&nbsp;</option>
                                                                <option value="<?php echo $lang['22_wheelers']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['22_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $labg['22_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['10_wheelers']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['10_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['h_wall']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['h_wall'] ? 'selected="selected"' :''); ?>><?php echo $lang['h_wall']; ?></option>
                                                            </select>
                                                            <label for="vehicle_size" class="error" style="display: none;">This field is required.</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['commodity']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bags_commodity" name="woc[Bags][commodity]" value="<?php echo $woc['Bags'][commodity];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_point']; ?></label>
                                                            <select class="form-control select2" id="woc_bags_loading_point_id" name="woc[Bags][loading_point_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Bags']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['destination']; ?></label>
                                                            <select class="form-control select2" id="woc_bags_destination_id" name="woc[Bags][destination_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Bags']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['qty']; ?></label>
                                                            <input class="form-control" type="text"  id="woc_bags_qty" name="woc[Bags][qty]" value="<?php echo $woc['Bags']['qty'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_net_weight" name="woc[Bags][net_weight]" value="<?php echo $woc['Bags']['net_weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_bags']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bags_no_of_bags" name="woc[Bags][no_of_bags]" value="<?php echo $woc['Bags']['no_of_bags'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['packing']; ?></label>
                                                            <input class="form-control" type="text" id="woc_bags_packing" name="woc[Bags][packing]" value="<?php echo $woc['Bags']['packing'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row ">
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_freight" name="woc[Bags][freight]" value="<?php echo $woc['Bags']['freight'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total_frieght']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_total_frieght" name="woc[Bags][total_frieght]" value="<?php echo $woc['Bags']['total_frieght'];?>"  />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_sales_tax_percent" name="woc[Bags][sales_tax_percent]" value="<?php echo $woc['Bags']['sales_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_sales_tax_amount" name="woc[Bags][sales_tax_amount]" value="<?php echo $woc['Bags']['sales_tax_amount'];?>" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['grand_total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_grand_total" name="woc[Bags][grand_total]" value="<?php echo $woc['Bags']['grand_total'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_income_tax_percent" name="woc[Bags][income_tax_percent]" value="<?php echo $woc['Bags']['income_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_income_tax_amount" name="woc[Bags][income_tax_amount]" value="<?php echo $woc['Bags']['income_tax_amount'];?>" readonly />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_net_freight" name="woc[Bags][net_freight]" value="<?php echo $woc['Bags']['net_freight'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['adv_payment']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_adv_payment" name="woc[Bags][adv_payment]" value="<?php echo $woc['Bags']['adv_payment'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['balance']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_bags_balance" name="woc[Bags][balance]" value="<?php echo $woc['Bags']['balance'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="CommodityLCL" class="row commodity">
                                    <div class="col-md-12">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">LCL</div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['project_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_lcl_project_name" name="woc[LCL][project_name]" value="<?php echo $woc['LCL']['project_name'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['bilty_no']; ?></label>
                                                            <input class="form-control" type="text" name="woc[LCL][bilty_no]" value="<?php echo $woc['LCL']['bilty_no'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['vehicle_size']; ?></label>
                                                            <select class="form-control" id="vehicle_size" name="vehicle_size" >
                                                                <option value="">&nbsp;</option>
                                                                <option value="<?php echo $text_22_wheelers; ?>" <?php echo ($woc['LCL']['vehicle_size'] == $lang['22_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['22_wheelers']; ?></option>
                                                                <option value="<?php echo $text_10_wheelers; ?>" <?php echo ($woc['LCL']['vehicle_size'] == $lang['10_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheelers']; ?></option>
                                                                <option value="<?php echo $text_h_wall; ?>" <?php echo ($woc['LCL']['vehicle_size'] == $lang['h_wall'] ? 'selected="selected"' :''); ?>><?php echo $lang['h_wall']; ?></option>
                                                            </select>
                                                            <label for="vehicle_size" class="error" style="display: none;">This field is required.</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['commodity']; ?></label>
                                                            <input class="form-control" type="text" id="woc_lcl_commodity" name="woc[LCL][commodity]" value="<?php echo $woc['LCL'][commodity];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_point']; ?></label>
                                                            <select class="form-control select2"  id="woc_lcl_loading_point_id" name="woc[LCL][loading_point_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['LCL']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_net_weight" name="woc[LCL][net_weight]" value="<?php echo $woc['LCL']['net_weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['destination']; ?></label>
                                                            <select class="form-control select2" id="woc_lcl_destination_id" name="woc[LCL][destination_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['LCL']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_pkg']; ?></label>
                                                            <input class="form-control" type="text" id="woc_lcl_no_of_pkg" name="woc[LCL][no_of_pkg]" value="<?php echo $woc['LCL']['no_of_pkg'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['shipping_line']; ?></label>
                                                            <input class="form-control" type="text" id="woc_lcl_shipping_line" name="woc[LCL][shipping_line]" value="<?php echo $woc['LCL']['shipping_line'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['depositer_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_lcl_depositor_name" name="woc[LCL][depositor_name]" value="<?php echo $woc['LCL']['depositor_name'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['clearing_agent']; ?></label>
                                                            <input class="form-control" type="text " id="woc_lcl_clearing_agent" name="woc[LCL][clearing_agent]" value="<?php echo $woc['LCL']['clearing_agent'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row ">
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_freight" name="woc[LCL][freight]" value="<?php echo $woc['LCL']['freight'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total_frieght']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_total_frieght" name="woc[LCL][total_frieght]" value="<?php echo $woc['LCL']['total_frieght'];?>"  />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_sales_tax_percent" name=woc[LCL][sales_tax_percent]" value="<?php echo $woc['LCL']['sales_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_sales_tax_amount" name=woc[LCL][sales_tax_amount]" value="<?php echo $woc['LCL']['sales_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['grand_total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_grand_total" name="woc[LCL][grand_total]" value="<?php echo $woc['LCL']['grand_total'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_income_tax_percent" name="woc[LCL][income_tax_percent]" value="<?php echo $woc['LCL']['income_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_income_tax_amount" name="woc[LCL][income_tax_amount]" value="<?php echo $woc['LCL']['income_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_net_freight" name="woc[LCL][net_freight]" value="<?php echo $woc['LCL']['net_freight'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['adv_payment']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_adv_payment" name="woc[LCL][adv_payment]" value="<?php echo $woc['LCL']['adv_payment'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['balance']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_lcl_balance" name="woc[LCL][balance]" value="<?php echo $woc['LCL']['balance'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="CommodityPacking" class="row commodity">
                                    <div class="col-md-12">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">Packages/Cottons</div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['project_name']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Packing][project_name]" value="<?php echo $woc['Packing']['project_name'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_pkg']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Packing][no_of_pkg]" value="<?php echo $woc['Packing']['no_of_pkg'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['weight']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Packing][weight]" value="<?php echo $woc['Packing']['weight'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_point']; ?></label>
                                                            <select class="form-control select2" name="woc[Packing][loading_point_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Packing']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['destination']; ?></label>
                                                            <select class="form-control select2" name="woc[Packing][destination_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Packing']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row ">
                                                    <div class="col-sm-offset-9 col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_freight" name="woc[Packing][freight]" value="<?php echo $woc['Packing']['freight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_charges']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_loading_charges" name="woc[Packing][loading_charges]" value="<?php echo $woc['Packing']['loading_charges'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['excess_wgt_charges']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_excess_wgt_charges" name="woc[Packing][excess_wgt_charges]" value="<?php echo $woc['Packing']['excess_wgt_charges'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['weighbride_charges']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_weighbride_charges" name="woc[Packing][weighbride_charges]" value="<?php echo $woc['Packing']['weighbride_charges'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['detention']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_detention" name="woc[Packing][detention]" value="<?php echo $woc['Packing']['detention'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['deposit_charges']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_deposit_charges" name="woc[Packing][deposit_charges]" value="<?php echo $woc['Packing']['deposit_charges'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['other_charges']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_other_charges" name="woc[Packing][other_charges]" value="<?php echo $woc['Packing']['other_charges'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_total" name="woc[Packing][total]" value="<?php echo $woc['Packing']['total'];?>" readonly/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_sales_tax" name="woc[Packing][sales_tax_amount]" value="<?php echo $woc['Packing']['sales_tax'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['grand_total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_grand_total" name="woc[Packing][grand_total]" value="<?php echo $woc['Packing']['grand_total'];?>" readonly/>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_income_tax" name="woc[Packing][income_tax_amount]" value="<?php echo $woc['Packing']['income_tax'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_packing_net_freight" name="woc[Packing][net_freight]" value="<?php echo $woc['Packing']['net_freight'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="CommodityPallets" class="row commodity">
                                    <div class="col-md-12">
                                        <div class="panel panel-info">
                                            <div class="panel-heading">Pallets/Skids/Drums</div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['project_name']; ?></label>
                                                            <input class="form-control" type="text" id="woc_pallets_project_name" name="woc[Pallets][project_name]" value="<?php echo $woc['Pallets']['project_name'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_pallets']; ?></label>
                                                            <input class="form-control" type="text" id="woc_pallets_no_of_pallets" name="woc[Pallets][no_of_pallets]" value="<?php echo $woc['Pallets']['no_of_pallets'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['bilty_no']; ?></label>
                                                            <input class="form-control" type="text" name="woc[Pallets][bilty_no]" value="<?php echo $woc['Bags']['bilty_no'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['vehicle_size']; ?></label>
                                                            <select class="form-control" id="vehicle_size" name="vehicle_size" >
                                                                <option value="">&nbsp;</option>
                                                                <option value="<?php echo $lang['22_wheelers']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['22_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['22_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['10_wheelers']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['10_wheelers'] ? 'selected="selected"' :''); ?>><?php echo $lang['10_wheelers']; ?></option>
                                                                <option value="<?php echo $lang['h_wall']; ?>" <?php echo ($woc['Bags']['vehicle_size'] == $lang['h_wall'] ? 'selected="selected"' :''); ?>><?php echo $lang['h_wall']; ?></option>
                                                            </select>
                                                            <label for="vehicle_size" class="error" style="display: none;">This field is required.</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['loading_point']; ?></label>
                                                            <select class="form-control select2" id="woc_pallets_loading_point_id" name="woc[Pallets][loading_point_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Pallets']['loading_point_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['destination']; ?></label>
                                                            <select class="form-control select2" id="woc_pallets_destination_id" name="woc[Pallets][destination_id]">
                                                                <option value="">&nbsp;</option>
                                                                <?php foreach($destinations as $destination): ?>
                                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($woc['Pallets']['destination_id']==$destination['destination_id']?'selected="true"':'')?>><?php echo $destination['name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_stop']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_no_of_stop" name="woc[Pallets][no_of_stop]" value="<?php echo $woc['Pallets']['no_of_stop'];?>" />
                                                        </div>

                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['weight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_weight" name="woc[Pallets][weight]" value="<?php echo $woc['Pallets']['weight'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['disp_note_no']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_disp_note_no" name="woc[Pallets][disp_note_no]" value="<?php echo $woc['Pallets']['disp_note_no'];?>" />
                                                        </div>
                                                        <div class="form-group">
                                                            <label><?php echo $lang['no_of_cases']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_no_of_cases" name="woc[Pallets][no_of_cases]" value="<?php echo $woc['Pallets']['no_of_cases'];?>" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_freight" name="woc[Pallets][freight]" value="<?php echo $woc['Pallets']['freight'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_sales_tax_percent" name="woc[Pallets][sales_tax_percent]" value="<?php echo $woc['Pallets']['sales_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['sales_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_sales_tax_amount" name="woc[Pallets][sales_tax_amount]" value="<?php echo $woc['Pallets']['sales_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['grand_total']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_grand_total" name="woc[Pallets][grand_total]" value="<?php echo $woc['Pallets']['grand_total'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_income_tax_percent" name="woc[Pallets][income_tax_percent]" value="<?php echo $woc['Pallets']['income_tax_percent'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['income_tax_amount']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_income_tax_amount" name="woc[Pallets][income_tax_amount]" value="<?php echo $woc['Pallets']['income_tax_amount'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['net_freight']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_net_freight" name="woc[Pallets][net_freight]" value="<?php echo $woc['Pallets']['net_freight'];?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['adv_payment']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_adv_payment" name="woc[Pallets][adv_payment]" value="<?php echo $woc['Pallets']['adv_payment'];?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['balance']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="woc_pallets_balance" name="woc[Pallets][balance]" value="<?php echo $woc['Pallets']['balance'];?>" readonly />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="transaction" class="row">
                                    <div class="col-md-12">
                                        <div class="box box-info">
                                            <div class="box-header text-center"><h3>Transactions</h3></div>
                                            <div class="box-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <table id="tblTransaction" class="table table-bordered table-striped">
                                                            <thead>
                                                            <tr align="center" data-row_id="H">
                                                                <td style="width: 6%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                                <th class="text-center">Account</th>
                                                                <th class="text-center">Description</th>
                                                                <th class="text-center">Debit</th>
                                                                <th class="text-center">Credit</th>
                                                                <td style="width: 6%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php $grid_row=0; ?>
                                                            <?php foreach($work_order_details as $detail): ?>
                                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id = '<?php echo $grid_row; ?>'>
                                                                <td>
                                                                    <a onclick="removeRow(<?php echo $grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                                    &nbsp;
                                                                    <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                                </td>
                                                                <td>
                                                                    <select class="form-control select2" id="grid_row_<?php echo $grid_row; ?>_coa_id" name="work_order_details[<?php echo $grid_row; ?>][coa_id]">
                                                                        <option value="">&nbsp;</option>
                                                                        <?php foreach($coas as $coa): ?>
                                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $detail['coa_id']?'selected="true"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" id="grid_row_<?php echo $grid_row; ?>_remarks" name="work_order_details[<?php echo $grid_row; ?>][remarks]"  value="<?php echo $detail['remarks']; ?>" />
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control fDecimal" id="grid_row_<?php echo $grid_row; ?>_debit" name="work_order_details[<?php echo $grid_row; ?>][debit]"  value="<?php echo $detail['debit']; ?>" onchange="calcTotal();"/>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control fDecimal" id="grid_row_<?php echo $grid_row; ?>_credit" name="work_order_details[<?php echo $grid_row; ?>][credit]" value="<?php echo $detail['credit']; ?>" onchange="calcTotal();" />
                                                                </td>
                                                                <td>
                                                                    <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                                    &nbsp;
                                                                    <a onclick="removeRow(<?php echo $grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                                </td>
                                                            </tr>
                                                            <?php $grid_row++; ?>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-6 hidden-xs">&nbsp;</div>
                                                    <div class="col-sm-3 col-xs-12">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total_debit']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="total_debit" name="total_debit" value="<?php echo $total_debit;?>" readonly/>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12 col-sm-3">
                                                        <div class="form-group">
                                                            <label><?php echo $lang['total_credit']; ?></label>
                                                            <input class="form-control fDecimal" type="text" id="total_credit" name="total_credit" value="<?php echo $total_credit;?>" readonly/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                                    <i class="fa fa-balance-scale"></i>
                                    &nbsp;<?php echo $lang['ledger']; ?>
                                </button>
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
    <script src="dist/js/pages/vehicle/dispatch_invoice.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $grid_row = '<?php echo $grid_row; ?>';
        var $coas = <?php echo json_encode($coas); ?>;
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>
