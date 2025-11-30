/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#product_category_id',function () {
    var $data = {
        warehouse_id: $('#warehouse_id').val(),
        product_category_id: $('#product_category_id').val()
    };
    $.ajax({
        url: $UrlGetProducts,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#product_category_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#tblStockAdjustment tbody').html(json.html);
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


function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $qty = parseFloat($('#stock_adjustment_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#stock_adjustment_detail_rate_' + $row_id).val());
    var $amount = roundUpto($qty*$rate,2);

    $('#stock_adjustment_detail_amount_' + $row_id).val($amount);

    calculateTotal();
}


function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;
    $('#tblStockAdjustment tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $qty = $('#stock_adjustment_detail_qty_' + $row_id).val();
        $amount = $('#stock_adjustment_detail_amount_' + $row_id).val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
    })

    console.log($total_qty, $total_amount);
    $('#total_qty').val(roundUpto($total_qty,2));
    $('#net_amount').val(roundUpto($total_amount,2));
}