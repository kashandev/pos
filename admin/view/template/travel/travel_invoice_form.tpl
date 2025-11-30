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
                        <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a>
                        <?php endif; ?>
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
                        <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                        <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />
                        <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" id="document_date" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" id="document_identity" name="document_identity" value="<?php echo $document_identity; ?>" readonly/>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['booking_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" id="booking_date" name="booking_date" value="<?php echo $booking_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['booking_no']; ?></label>
                                            <input class="form-control" type="text" id="customer_booking_no" name="customer_booking_no" value="<?php echo $customer_booking_no; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <input type="hidden" id="partner_type_id" name="partner_type_id" value="2" />
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['member']; ?></label>
                                            <select class="form-control" id="partner_id" name="partner_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($members as $member): ?>
                                                <option value="<?php echo $member['member_id']; ?>" <?php echo ($member['member_id']==$partner_id?'selected="true"':''); ?>><?php echo $member['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['adult']; ?></label>
                                            <input class="form-control fPInteger text-right" type="text" id="visa_qty_adult" name="visa_qty_adult" value="<?php echo $visa_qty_adult; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['child']; ?></label>
                                            <input class="form-control fPInteger text-right" type="text" id="visa_qty_child" name="visa_qty_child" value="<?php echo $visa_qty_child; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['infant']; ?></label>
                                            <input class="form-control fPInteger text-right" type="text" id="visa_qty_infant" name="visa_qty_infant" value="<?php echo $visa_qty_infant; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['total']; ?></label>
                                            <input class="form-control fPInteger text-right" type="text" id="visa_qty_total" name="visa_qty_total" value="<?php echo $visa_qty_total; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <label class="text-center form-control"><?php echo $lang['accommodation']; ?></label>
                                            <table id="tblAccommodation" class="table table-bordered">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td><?php echo $lang['destination_name']; ?></td>
                                                    <td><?php echo $lang['hotel_name']; ?></td>
                                                    <td><?php echo $lang['check_in']; ?></td>
                                                    <td><?php echo $lang['check_out']; ?></td>
                                                    <td><?php echo $lang['nights']; ?></td>
                                                    <td><?php echo $lang['room_type']; ?></td>
                                                    <td><?php echo $lang['room_charges']; ?></td>
                                                    <td><?php echo $lang['room_qty']; ?></td>
                                                    <td><?php echo $lang['room_amount']; ?></td>
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $accommodation_grid_row =0; ?>
                                                <?php foreach($travel_accommodations as $accommodation): ?>
                                                <tr id="grid_row_<?php echo $accommodation_grid_row; ?>" data-row_id="<?php echo $accommodation_grid_row; ?>">
                                                    <td>
                                                        <a onclick="removeRow(<?php echo $accommodation_grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                        &nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                    <td>
                                                        <select onchange="getHotel(<?php echo $accommodation_grid_row; ?>);" class="form-control" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_destination_id" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][destination_id]" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($destinations as $destination): ?>
                                                            <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($destination['destination_id']==$accommodation['destination_id']?'selected="true"':''); ?>><?php echo $destination['destination_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="travel_accommodation_<?php echo $accommodation_grid_row; ?>_destination_id" class="error" style="display: none;">&nbsp;</label>
                                                    </td>
                                                    <td>
                                                        <select onchange="getRoomType(<?php echo $accommodation_grid_row; ?>);" class="form-control" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_hotel_id" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][hotel_id]" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($hotels[$accommodation['destination_id']] as $hotel): ?>
                                                            <option value="<?php echo $hotel['hotel_id']; ?>" <?php echo ($hotel['hotel_id']==$accommodation['hotel_id']?'selected="true"':''); ?>><?php echo $hotel['hotel_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="travel_accommodation_<?php echo $accommodation_grid_row; ?>_hotel_id" class="error" style="display: none;">&nbsp;</label>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control dtpDate" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][check_in]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_check_in" value="<?php echo $accommodation['check_in']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control dtpDate" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][check_out]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_check_out" value="<?php echo $accommodation['check_out']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateRowTotal(<?php echo $accommodation_grid_row; ?>);" type="text" class="form-control fPInteger" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][nights]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_nights" value="<?php echo $accommodation['nights']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="hidden" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][hotel_room_id]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_hotel_room_id" value="<?php echo $accommodation['hotel_room_id']; ?>" />
                                                        <select onchange="getRoomCharges(<?php echo $accommodation_grid_row; ?>);" class="form-control" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_room_type_id" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][room_type_id]" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($room_types[$accommodation['hotel_id']] as $room_type): ?>
                                                            <option data-hotel_room_id="<?php echo $room_type['hotel_room_id']; ?>" data-room_charges="<?php echo $room_type['room_charges']; ?>" value="<?php echo $room_type['room_type_id']; ?>" <?php echo ($room_type['room_type_id']==$accommodation['room_type_id']?'selected="true"':''); ?>><?php echo $room_type['room_type']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="travel_accommodation_<?php echo $accommodation_grid_row; ?>_hotel_id" class="error" style="display: none;">&nbsp;</label>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateRowTotal(<?php echo $accommodation_grid_row; ?>);" type="text" class="form-control fPDecimal" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][room_charges]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_room_charges" value="<?php echo $accommodation['room_charges']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateRowTotal(<?php echo $accommodation_grid_row; ?>);" type="text" class="form-control fPInteger" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][room_qty]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_room_qty" value="<?php echo $accommodation['room_qty']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control fPDecimal" name="travel_accommodations[<?php echo $accommodation_grid_row; ?>][room_amount]" id="travel_accommodation_<?php echo $accommodation_grid_row; ?>_room_amount" value="<?php echo $accommodation['room_amount']; ?>" />
                                                    </td>
                                                    <td>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        &nbsp;<a onclick="removeRow(<?php echo $accommodation_grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $accommodation_grid_row++; ?>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-offset-10 col-sm-2">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['accommodation_total']; ?></label>
                                            <input class="form-control fPDecimal text-right" type="text" id="accommodation_total" name="accommodation_total" value="<?php echo $accommodation_total; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <label class="text-center form-control"><?php echo $lang['services']; ?></label>
                                            <table id="tblService" class="table table-bordered">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td><?php echo $lang['service_name']; ?></td>
                                                    <td><?php echo $lang['adult']; ?></td>
                                                    <td><?php echo $lang['child']; ?></td>
                                                    <td><?php echo $lang['infant']; ?></td>
                                                    <td><?php echo $lang['total_amount']; ?></td>
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $service_grid_row =0; ?>
                                                <?php foreach($travel_services as $service): ?>
                                                <tr id="service_row_id_<?php echo $service_grid_row; ?>" data-service_row_id="<?php echo $service_grid_row; ?>">
                                                    <td>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        &nbsp;<a onclick="removeServiceRow(<?php echo $service_grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                    <td>
                                                        <select class="form-control" id="travel_service_<?php echo $service_grid_row; ?>_service_id" name="travel_services[<?php echo $service_grid_row; ?>][service_id]" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($services as $sv): ?>
                                                            <option value="<?php echo $sv['service_id']; ?>" <?php echo ($sv['service_id']==$service['service_id']?'selected="true"':''); ?>><?php echo $sv['service_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="travel_service_<?php echo $service_grid_row; ?>_service_id" class="error" style="display: none;">&nbsp;</label>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateServiceRowTotal(<?php echo $service_grid_row; ?>);" type="text" class="form-control fPDecimal text-right" name="travel_services[<?php echo $service_grid_row; ?>][adult_charges]" id="travel_service_<?php echo $service_grid_row; ?>_adult_charges" value="<?php echo $service['adult_charges']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateServiceRowTotal(<?php echo $service_grid_row; ?>);" type="text" class="form-control fPDecimal text-right" name="travel_services[<?php echo $service_grid_row; ?>][child_charges]" id="travel_service_<?php echo $service_grid_row; ?>_child_charges" value="<?php echo $service['child_charges']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateServiceRowTotal(<?php echo $service_grid_row; ?>);" type="text" class="form-control fPDecimal text-right" name="travel_services[<?php echo $service_grid_row; ?>][infant_charges]" id="travel_service_<?php echo $service_grid_row; ?>_infant_charges" value="<?php echo $service['infant_charges']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control fPDecimal text-right" name="travel_services[<?php echo $service_grid_row; ?>][total_charges]" id="travel_service_<?php echo $service_grid_row; ?>_total_charges" value="<?php echo $service['total_charges']; ?>" readonly/>
                                                    </td>
                                                    <td>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        &nbsp;<a onclick="removeServiceRow(<?php echo $service_grid_row; ?>);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $service_grid_row++; ?>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['booking_note']; ?></label>
                                            <textarea rows="4" class="form-control" name="booking_note" id="booking_note"><?php echo $booking_note; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['voucher_note']; ?></label>
                                            <textarea rows="4" class="form-control" name="voucher_note" id="voucher_note"><?php echo $voucher_note; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['gross_amount']; ?></label>
                                            <input class="form-control fPDecimal text-right" type="text" id="gross_amount" name="gross_amount" value="<?php echo $gross_amount; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['adjustment_amount']; ?></label>
                                            <input class="form-control fDecimal text-right" type="text" id="adjustment_amount" name="adjustment_amount" value="<?php echo $adjustment_amount; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['adjustment_remarks']; ?></label>
                                            <input class="form-control" type="text" id="adjustment_remarks" name="adjustment_remarks" value="<?php echo $adjustment_remarks; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['net_amount']; ?></label>
                                            <input class="form-control fPDecimal text-right" type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" readonly />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_currency']; ?></label>
                                            <select class="form-control" id="document_currency_id" name="document_currency_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($currencys as $currency): ?>
                                                <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['conversion_rate']; ?></label>
                                            <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" onchage="calcNetAmount()" />
                                        </div>
                                        <div class="form-group">
                                            <label><?php echo $lang['base_amount']; ?></label>
                                            <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                                            <input type="text" class="form-control" id="base_amount" name="base_amount" readonly="true" value="<?php echo $base_amount; ?>" />
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
                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a>
                                <?php endif; ?>
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
        var $UrlGetMemberPax = '<?php echo $href_get_member_pax; ?>';
        var $UrlGetHotel = '<?php echo $href_get_hotel; ?>';
        var $UrlGetNight = '<?php echo $href_get_night; ?>';
        var $UrlGetRoomType = '<?php echo $href_get_room_type; ?>';
        var $destinations = <?php echo json_encode($destinations); ?>;
        var $services = <?php echo json_encode($services); ?>;
        var $accommodation_grid_row = <?php echo $accommodation_grid_row; ?>;
        var $service_grid_row = <?php echo $service_grid_row; ?>;
    </script>
    <script type="text/javascript" src="../admin/view/js/travel/travel_invoice.js"></script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>