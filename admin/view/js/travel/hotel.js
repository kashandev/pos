/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('click','.btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow('+$grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control" id="hotel_room_'+$grid_row+'_room_type_id" name="hotel_rooms['+$grid_row+'][room_type_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $room_types.forEach(function($room_type) {
        $html += '<option value="'+$room_type['room_type_id']+'">'+$room_type['room_type']+'</option>';
    })
    $html += '</select>';
    $html += '<label for="hotel_room_'+$grid_row+'_room_type_id" class="error" style="display: none;">&nbsp;</label>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal text-right" name="hotel_rooms['+$grid_row+'][room_charges]" id="hotel_room_'+$grid_row+'_room_charges" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeRow('+$grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblHotelRoom tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    setFieldFormat();
    $('#hotel_room_'+$grid_row+'_room_type_id').rules("add", "required");
    $('#hotel_room_'+$grid_row+'_room_charges').rules("add", "required");
    $('#hotel_room_'+$grid_row+'_room_type_id').select2({width: '100%'}).select2('open');

    $grid_row++;

    $('#total_rooms').val($('#tblHotelRoom tbody tr').length);
});

function removeRow($row_id) {
    $('#grid_row_'+$row_id).remove();
    $('#total_rooms').val($('#tblHotelRoom tbody tr').length);
}

$(document).on('change','#meal_available',function() {
    var $meal_available = $(this).val();

    $('#avg_meal_charges').val('0');
    if($meal_available=='Yes') {
        $('#avg_meal_charges').prop('readonly','');
    } else {
        $('#avg_meal_charges').prop('readonly','true');
    }
})