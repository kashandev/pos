/**
 * Created by Huzaifa on 9/18/15.
 */
function Save() {

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}

$(document).ready(function() {
//    $('#form').valid();
})

$(document).on('click','#btnAddDiscount',function() {
    $html = '';
    $html += '<tr id="grid_row_'+$policy_row+'" data-row_id="'+$policy_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<select class="form-control" id="policy_'+$policy_row+'_product_category_id" name="policies['+$policy_row+'][product_category_id]" onChange="fillProducts(this);">';
    $html += '<option value="0">&nbsp;</option>';
    $.each($product_categories,function($index,$category) {
        $html += '<option value="'+$category['product_category_id']+'">'+$category['name']+'</option>';
    })
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control" id="policy_'+$policy_row+'_product_id" name="policies['+$policy_row+'][product_id]">';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control" name="policies['+$policy_row+'][discount_percent]" id="policy_'+$policy_row+'_discount_percent" value="" />';
    $html += '</td>';
    $html += '</tr>';

    $('#tblSaleDiscount tbody').prepend($html);
    setFieldFormat();
    $policy_row++;

    $('#discount_count').val($('#tblSaleDiscount tbody tr').length)
})

function fillProducts($obj) {
    var $PolicyRow = $($obj).parent().parent().data('row_id');
    var $ProductCategoryId = $('#policy_'+$PolicyRow+'_product_category_id option:selected').val();
    var $Products = $products[$ProductCategoryId];
    $html = '<option value="">&nbsp;</option>';
    if($Products!=undefined) {
        $.each($Products,function($i,$product) {
            $html += '<option value="'+$product['product_id']+'">'+$product['name']+'</option>';
        })
    }
    $('#policy_'+$PolicyRow+'_product_id').html($html).trigger('change');

}

$(document).on('change','#partner_id', function() {
    $("#start_date").removeData("previousValue");
    $("#end_date").removeData("previousValue");
    $("#form").data('validator').element('#start_date'); //retrigger remote call
    $("#form").data('validator').element('#end_date'); //retrigger remote call
})