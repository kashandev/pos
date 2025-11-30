/**
 * Created by Huzaifa on 9/18/15.
 */

// $(document).on('change','#product_category_id', function() {
//     $product_category_id = $(this).val();
//     $.ajax({
//         url: $UrlGetProductSubCategory,
//         dataType: 'json',
//         type: 'post',
//         data: 'product_category_id=' + $product_category_id+'&product_sub_category_id='+$product_sub_category_id,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             $('#product_category_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//         },
//         complete: function() {
//             $('#loader').remove();
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 console.log(json.html);
//                 $('#product_sub_category_id').select2('destroy');
//                 $('#product_sub_category_id').html(json.html);
//                 $('#product_sub_category_id').select2({width:'100%'});
//             }
//             else {
//                 alert(json.error);
//             }
//         },
//         error: function(xhr, ajaxOptions, thrownError) {
//             console.log(xhr.responseText);
//         }
//     })
// });

$(document).on('change','#product_category_id, #thickness_id, #width_id, #length_id, #grade_id, #sawmill_id', function() {
    var $product_category = $('#product_category_id option:selected').text();
    var $thickness = $('#thickness_id option:selected').text();
    var $width = $('#width_id option:selected').text();
    var $length = $('#length_id option:selected').text();
    var $grade = $('#grade_id option:selected').text();
    var $sawmill = $('#sawmill_id option:selected').text();

    var $arrProductName=[];
    if($product_category.trim() != '') {
        $arrProductName.push($product_category);
    }
    if($thickness.trim() != '') {
        $arrProductName.push($thickness);
    }
    if($width.trim() != '') {
        $arrProductName.push($width);
    }
    if($length.trim() != '') {
        $arrProductName.push($length);
    }
    if($grade.trim() != '') {
        $arrProductName.push($grade);
    }
    if($sawmill.trim() != '') {
        $arrProductName.push($sawmill);
    }

    var $product_name = $arrProductName.join(' x ');
    $('#name').val($product_name);
});

$(document).on('change','#thickness_id', function() {
    $('#thickness_value').val($('#thickness_id option:selected').text()).trigger('change');
});

$(document).on('change','#width_id', function() {
    $('#width_value').val($('#width_id option:selected').text()).trigger('change');
});

$(document).on('change','#length_id', function() {
    $('#length_value').val($('#length_id option:selected').text()).trigger('change');
});

$(document).on('change','#length_value, #thickness_value, #width_value', function() {
    var $thickness = parseFloat($('#thickness_value').val()) || 0.00;
    var $width = parseFloat($('#width_value').val()) || 0.00;
    var $length = parseFloat($('#length_value').val()) || 0.00;

    var $cubic_meter = $length * ($width * 0.001) * ($thickness * 0.001);
    var $cubic_feet = $cubic_meter * 35.3147;

    $('#cubic_meter').val($cubic_meter.toFixed(4));
    $('#cubic_feet').val($cubic_feet.toFixed(4));

});

function printLabel() {

 $url = '';
 var $print_type = $('#print_type:checked').val();
 if ($print_type){
    $url+=$UrlPrint + '&print_type='+$print_type+'';
 }
 else{
    $url = $UrlPrint;
 }
   window.open($url,'_blank');
}