/**
 * Created by Huzaifa on 9/18/15.
 */
//$(document).on('change','#product_group_id', function() {
//    $product_group_id = $(this).val();
//    $.ajax({
//        url: $UrlGetProductMaster,
//        dataType: 'json',
//        type: 'post',
//        data: 'product_group_id=' + $product_group_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
//            $('#product_master_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
//            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
//                $('#product_master_id').select2('destroy');
//                $('#product_master_id').html(json.html);
//                $('#product_master_id').select2({width:'100%'});
//            }
//            else {
//                alert(json.error);
//            }
//        },
//        error: function(xhr, ajaxOptions, thrownError) {
//            console.log(xhr.responseText);
//        }
//    })
//});
//


function printDetail() {
    if($('#date_from').val() == '' || $('#date_to').val() == '')
    {
        alert('Please select date!');
    }
    else
    {
        $('#form').attr('action', $UrlPrint).submit();
    }
    
}


function printExcel() {
    if($('#date_from').val() == '' || $('#date_to').val() == '')
    {
        alert('Please select date!');
    }
    else
    {
        $('#form').attr('action', $UrlPrintExcel).submit();    
    }
    
}
