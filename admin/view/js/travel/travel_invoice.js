/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#partner_id', function() {

    $.ajax({
        url: $UrlGetMemberPax,
        dataType: 'json',
        type: 'post',
        data: 'member_id=' + $(this).val(),
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#tblReceipt').before('<i id="loader" style="float: right;" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#visa_qty_infant').val(json['member_pax']['Infant']);
                $('#visa_qty_child').val(json['member_pax']['Child']);
                $('#visa_qty_adult').val(json['member_pax']['Adult']);
                $('#visa_qty_total').val(json['member_pax']['Total']);
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
});

$(document).on('change','#visa_qty_infant, #visa_qty_child, #visa_qty_adult', function() {
    var $qty_infant = parseInt($('#visa_qty_infant').val());
    var $qty_child = parseInt($('#visa_qty_child').val());
    var $qty_adult = parseInt($('#visa_qty_adult').val());

    var  $qty_total = $qty_infant + $qty_child + $qty_adult;
    $('#visa_qty_total').val($qty_total);
});

$(document).on('click','#tblAccommodation .btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$accommodation_grid_row+'" data-row_id="'+$accommodation_grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow('+$accommodation_grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="getHotel('+$accommodation_grid_row+');" class="form-control" id="travel_accommodation_'+$accommodation_grid_row+'_destination_id" name="travel_accommodations['+$accommodation_grid_row+'][destination_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $destinations.forEach(function($destination) {
        $html += '<option value="'+$destination['destination_id']+'">'+$destination['destination_name']+'</option>';
    })
    $html += '</select>';
    $html += '<label for="travel_accommodation_'+$accommodation_grid_row+'_destination_id" class="error" style="display: none;">&nbsp;</label>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select onchange="getRoomType('+$accommodation_grid_row+');" class="form-control" id="travel_accommodation_'+$accommodation_grid_row+'_hotel_id" name="travel_accommodations['+$accommodation_grid_row+'][hotel_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '<label for="travel_accommodation_'+$accommodation_grid_row+'_hotel_id" class="error" style="display: none;">&nbsp;</label>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateNights('+$accommodation_grid_row+');" type="text" class="form-control dtpDate" name="travel_accommodations['+$accommodation_grid_row+'][check_in]" id="travel_accommodation_'+$accommodation_grid_row+'_check_in" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateNights('+$accommodation_grid_row+');" type="text" class="form-control dtpDate" name="travel_accommodations['+$accommodation_grid_row+'][check_out]" id="travel_accommodation_'+$accommodation_grid_row+'_check_out" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal('+$accommodation_grid_row+');" type="text" class="form-control fPInteger" name="travel_accommodations['+$accommodation_grid_row+'][nights]" id="travel_accommodation_'+$accommodation_grid_row+'_nights" value="1" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" name="travel_accommodations['+$accommodation_grid_row+'][hotel_room_id]" id="travel_accommodation_'+$accommodation_grid_row+'_hotel_room_id" value="1" />';
    $html += '<select onchange="getRoomCharges('+$accommodation_grid_row+');" class="form-control" id="travel_accommodation_'+$accommodation_grid_row+'_room_type_id" name="travel_accommodations['+$accommodation_grid_row+'][room_type_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '<label for="travel_accommodation_'+$accommodation_grid_row+'_hotel_id" class="error" style="display: none;">&nbsp;</label>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal('+$accommodation_grid_row+');" type="text" class="form-control fPDecimal" name="travel_accommodations['+$accommodation_grid_row+'][room_charges]" id="travel_accommodation_'+$accommodation_grid_row+'_room_charges" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal('+$accommodation_grid_row+');" type="text" class="form-control fPInteger" name="travel_accommodations['+$accommodation_grid_row+'][room_qty]" id="travel_accommodation_'+$accommodation_grid_row+'_room_qty" value="1" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fPInteger" name="travel_accommodations['+$accommodation_grid_row+'][room_amount]" id="travel_accommodation_'+$accommodation_grid_row+'_room_amount" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeRow('+$accommodation_grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblAccommodation tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    setFieldFormat();
    $accommodation_grid_row++;
});

function removeRow($row_id) {
    $('#grid_row_'+$row_id).remove();
    $('#total_rooms').val($('#tblHotelRoom tbody tr').length);

    calculateAccommodationTotal();
}

function getHotel($row_id) {
    $destination_id = $('#travel_accommodation_'+$row_id+'_destination_id').val();

    $.ajax({
        url: $UrlGetHotel,
        dataType: 'json',
        type: 'post',
        data: 'destination_id=' + $destination_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#travel_accommodation_'+$row_id+'_hotel_id').before('<i id="loader" style="float: right;" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#travel_accommodation_'+$row_id+'_hotel_id').html(json.html).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function getRoomType($row_id) {
    $hotel_id = $('#travel_accommodation_'+$row_id+'_hotel_id').val();

    $.ajax({
        url: $UrlGetRoomType,
        dataType: 'json',
        type: 'post',
        data: 'hotel_id=' + $hotel_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#travel_accommodation_'+$row_id+'_room_type_id').before('<i id="loader" style="float: right;" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#travel_accommodation_'+$row_id+'_room_type_id').html(json.html).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function getRoomCharges($row_id) {
    var $room_type_id = $('#travel_accommodation_'+$row_id+'_room_type_id').val();
    var $hotel_room_id = $('#travel_accommodation_'+$row_id+'_room_type_id option:selected').data('hotel_room_id');
    var $room_charges = $('#travel_accommodation_'+$row_id+'_room_type_id option:selected').data('room_charges');

    $('#travel_accommodation_'+$row_id+'_room_charges').val($room_charges);
    $('#travel_accommodation_'+$row_id+'_hotel_room_id').val($hotel_room_id);
    calculateRowTotal($row_id)
}

function calculateRowTotal($row_id) {
    var $room_type_id = $('#travel_accommodation_'+$row_id+'_room_type_id').val();
    var $room_charges = $('#travel_accommodation_'+$row_id+'_room_charges').val();
    var $room_qty = $('#travel_accommodation_'+$row_id+'_room_qty').val();
    var $nights = $('#travel_accommodation_'+$row_id+'_nights').val();

    var $adult_qty = $('#visa_qty_adult').val();
    var $child_qty = $('#visa_qty_child').val();
    var $infant_qty = $('#visa_qty_infant').val();

    var $total_qty = $adult_qty + $child_qty;

    if($room_type_id==1) {
        var $total_charges = $total_qty * $room_charges * $nights;
    } else {
        var $total_charges = $room_qty * $room_charges * $nights;
    }

    $('#travel_accommodation_'+$row_id+'_room_amount').val($total_charges);

    calculateAccommodationTotal();
}

function calculateAccommodationTotal() {
    var $accommodation_total = 0;

    $('#tblAccommodation tbody tr').each(function() {
        var $row_id = $(this).data('row_id');

        var $room_amount = $('#travel_accommodation_'+$row_id+'_room_amount').val();

        $accommodation_total += parseFloat($room_amount);
    })

    $('#accommodation_total').val($accommodation_total);

    calculateGrossAmount();
}

$(document).on('click','#tblService .btnAddGrid', function() {
    $html = '';
    $html += '<tr id="service_row_id_'+$service_grid_row+'" data-service_row_id="'+$service_grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeServiceRow('+$service_grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control" id="travel_service_'+$service_grid_row+'_service_id" name="travel_services['+$service_grid_row+'][service_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $services.forEach(function($service) {
        $html += '<option value="'+$service['service_id']+'">'+$service['service_name']+'</option>';
    })
    $html += '</select>';
    $html += '<label for="travel_service_'+$service_grid_row+'_service_id" class="error" style="display: none;">&nbsp;</label>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateServiceRowTotal('+$service_grid_row+');" type="text" class="form-control fPDecimal text-right" name="travel_services['+$service_grid_row+'][adult_charges]" id="travel_service_'+$service_grid_row+'_adult_charges" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateServiceRowTotal('+$service_grid_row+');" type="text" class="form-control fPDecimal text-right" name="travel_services['+$service_grid_row+'][child_charges]" id="travel_service_'+$service_grid_row+'_child_charges" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateServiceRowTotal('+$service_grid_row+');" type="text" class="form-control fPDecimal text-right" name="travel_services['+$service_grid_row+'][infant_charges]" id="travel_service_'+$service_grid_row+'_infant_charges" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal text-right" name="travel_services['+$service_grid_row+'][total_charges]" id="travel_service_'+$service_grid_row+'_total_charges" value="" readonly/>';
    $html += '</td>'
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeServiceRow('+$service_grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    //console.log($service_grid_row, $html);
    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblService tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    setFieldFormat();
    $service_grid_row++;
});

function removeServiceRow($row_id) {
    $('#service_row_id_'+$row_id).remove();

    calculateGrossAmount();
}

function calculateServiceRowTotal($row_id) {
    var $adult_qty = parseInt($('#visa_qty_adult').val()) || 0;
    var $child_qty = parseInt($('#visa_qty_child').val()) || 0;
    var $infant_qty = parseInt($('#visa_qty_infant').val()) || 0;

    var $adult_charges = parseFloat($('#travel_service_'+$row_id+'_adult_charges').val()) || 0.00;
    var $child_charges = parseFloat($('#travel_service_'+$row_id+'_child_charges').val()) || 0.00;
    var $infant_charges = parseFloat($('#travel_service_'+$row_id+'_infant_charges').val()) || 0.00;

    var $total_charges = ($adult_charges*$adult_qty) + ($child_charges*$child_qty) + ($infant_charges*$infant_qty);
    $('#travel_service_'+$row_id+'_total_charges').val($total_charges);

    console.log($adult_qty, $child_qty, $infant_qty);
    console.log($adult_charges, $child_charges, $infant_charges);
    calculateGrossAmount();
}

function calculateGrossAmount() {
    var $total_amount = 0.00;
    var $accommodation_amount = parseFloat($('#accommodation_total').val()) || 0.00;

    $('#tblService tbody tr').each(function() {
        var $row_id = $(this).data('service_row_id');
        var $total_charges = parseFloat($('#travel_service_'+$row_id+'_total_charges').val()) || 0.00;

        $total_amount += parseFloat($total_charges);
    })

    var $gross_amount = $accommodation_amount + $total_amount;

    $('#gross_amount').val($gross_amount);

    calculateNetAmount();
}

$(document).on('change','#adjustment_amount', function() {
    calculateNetAmount();
})

function calculateNetAmount() {
    var $gross_amount = parseFloat($('#gross_amount').val()) || 0.00;
    var $adjustment_amount = parseFloat($('#adjustment_amount').val()) || 0.00;

    var $net_amount = $gross_amount + $adjustment_amount;

    $('#net_amount').val($net_amount);

    calculateBaseAmount();
}

$(document).on('change','#conversion_rate', function() {
    calculateBaseAmount();
})

function calculateBaseAmount() {
    var $net_amount = $('#net_amount').val();
    var $conversion_rate = $('#conversion_rate').val();

    var $base_amount = parseFloat($net_amount) * parseFloat($conversion_rate);

    $('#base_amount').val($base_amount);
}

function calculateNights($row_id) {
    var $check_in_date = $('#travel_accommodation_' + $row_id + "_check_in").val();
    var $check_out_date = $('#travel_accommodation_' + $row_id + "_check_out").val();

    $.ajax({
        url: $UrlGetNight,
        dataType: 'json',
        type: 'post',
        data: 'check_in_date=' + $check_in_date + '&check_out_date=' + $check_out_date,
        mimeType:"multipart/form-data",
        beforeSend: function() {

        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#travel_accommodation_'+$row_id+'_nights').val(json.night).trigger('change');
            }
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })

}