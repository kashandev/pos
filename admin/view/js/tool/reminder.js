/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('click','.btnAddGridRow', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control" id="reminder_email_email_as_'+$grid_row+'" name="reminder_emails['+$grid_row+'][email_as]" >';
    $html += '  <option value="To">To</option>';
    $html += '  <option value="CC">CC</option>';
    $html += '</select>';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="reminder_emails['+$grid_row+'][receiver_name]" id="reminder_email_receiver_name_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="reminder_emails['+$grid_row+'][receiver_email]" id="reminder_email_receiver_email_'+$grid_row+'" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGridRow" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblReminderEmail tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    $('#tblReminderEmail #grid_row_'+$grid_row+' select').select2({width: '100%'});
    $('#tblReminderEmail #grid_row_'+$grid_row+' input:first').focus();
    $grid_row++;
});

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
}
