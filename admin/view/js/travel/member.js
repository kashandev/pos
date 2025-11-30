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
    $html += '<input type="text" class="form-control" name="family_members['+$grid_row+'][member_name]" id="family_member_'+$grid_row+'_member_name" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control fPDecimal" name="family_members['+$grid_row+'][mobile_no]" id="family_member_'+$grid_row+'_mobile_no" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input onchange="calculateAge(this, \'family_member_'+$grid_row+'_age\');" type="text" class="form-control dtpDate" name="family_members['+$grid_row+'][dob]" id="family_member_'+$grid_row+'_dob" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="family_members['+$grid_row+'][age]" id="family_member_'+$grid_row+'_age" value="" readonly />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="family_members['+$grid_row+'][cnic_no]" id="family_member_'+$grid_row+'_cnic_no" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="family_members['+$grid_row+'][passport_no]" id="family_member_'+$grid_row+'_passport_no" value="" />';
    $html += '</td>'
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;<a onclick="removeRow('+$grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblFamilyMember tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    setFieldFormat();
    $('#family_member_'+$grid_row+'_member_name').rules("add", "required");
    $('#family_member_'+$grid_row+'_dob').rules("add", "required");

    $grid_row++;
});

function removeRow($row_id) {
    $('#grid_row_'+$row_id).remove();
}

function getAge($objDate) {
    var today = new Date();
    var birthDate = $objDate;
    var age = today.getFullYear() - birthDate.getFullYear();
    var m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function calculateAge($src, $dest) {
    $dString = $($src).val();
    if($dString == '') {
        $objDate = new Date();
    } else {
        $dDay = Number($dString.substr(0,2));
        $dMonth = Number($dString.substr(3,2))-1;
        $dYear = Number($dString.substr(6,4));

        $objDate = new Date($dYear, $dMonth, $dDay);
    }

    $age = getAge($objDate);

    $('#'+$dest).val($age);
}