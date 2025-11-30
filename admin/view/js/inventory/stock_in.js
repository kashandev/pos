/**
 * Created by Huzaifa on 9/18/15.
 */
$(document).on('change','#warehouse_id',reset);

function reset() {
    $('#product_id').select2('destroy');
    $('#product_id').html('');
    $('#product_id').select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
            dataType: 'json',
            type: 'post',
            mimeType:"multipart/form-data",
            delay: 100,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 3,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: function(repo) {
            $('#product_code').val(repo['product_code']);
            $('#product_name').val(repo['name']);
            $('#unit_id').val(repo['unit_id']);
            $('#unit').val(repo['unit']);
            return (repo.product_code+'-'+repo.name) || repo.text;
        } // omitted for brevity, see the source of this page                }
    });

    $('#stock_quantity').val('');
    $('#quantity').val('');
    $('#product_id').select2('open');
}

$(document).ready(function() {
    $('#product_id').select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
            dataType: 'json',
            type: 'post',
            mimeType:"multipart/form-data",
            delay: 100,
            data: function (params) {
                return {
                    q: params.term, // search term
                    page: params.page
                };
            },
            processResults: function (data, params) {
                // parse the results into the format expected by Select2
                // since we are using custom formatting functions we do not need to
                // alter the remote JSON data, except to indicate that infinite
                // scrolling can be used
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 3,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: function(repo) {
            $('#product_code').val(repo['product_code']);
            $('#product_name').val(repo['name']);
            $('#unit_id').val(repo['unit_id']);
            $('#unit').val(repo['unit']);
            return (repo.product_code+'-'+repo.name) || repo.text;
        } // omitted for brevity, see the source of this page                }
    });
});

$(document).on('change','#product_id',function() {
    var $product_id = $(this).val();
    var $warehouse_id = $('#warehouse_id').val();
    if($warehouse_id=='') {
        alert('Please Select Warehouse');
        return;
    }
    if($product_id=='') {
        alert('Please Select Product');
        return;
    }
    $.ajax({
        url: $UrlGetProductStock,
        dataType: 'json',
        type: 'post',
        data: 'warehouse_id='+$warehouse_id+'&product_id=' + $product_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#quantity').parent().before('<i id="loader" class="fa fa-refresh fa-spin pull-right"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            $('#stock_quantity').val(json.stock['stock_qty']);
            $('#quantity').val('');
            $('#cog_rate').val(json.stock['avg_stock_rate']);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
})

$(document).on('click','#btnAddStock',function() {
    var $product_code = $('#product_code').val();
    var $product_id = $('#product_id').val();
    var $product_name = $('#product_name').val();
    var $unit_id = $('#unit_id').val();
    var $unit = $('#unit').val();
    var $cog_rate = $('#cog_rate').val()||0;
    var $stock_qty = $('#stock_quantity').val()||0;
    var $qty = $('#quantity').val();
    var $cog_amount = parseFloat($cog_rate) * parseFloat($qty);

    $html='';
    $html+='<tr id="stock_row_'+$stock_row+'" data-stock_row="'+$stock_row+'">';
    $html+='   <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html+='    <td>';
    $html+='        <input class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_product_code" name="stock_in_details['+$stock_row+'][product_code]" value="'+$product_code+'" readonly />';
    $html+='    </td>';
    $html+='    <td>';
    $html+='        <input class="form-control" type="hidden" id="stock_in_detail_'+$stock_row+'_product_id" name="stock_in_details['+$stock_row+'][product_id]" value="'+$product_id+'" />';
    $html+='        <input class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_product_name" name="stock_in_details['+$stock_row+'][product_name]" value="'+$product_name+'" readonly />';
    $html+='    </td>';
    $html+='    <td>';
    $html+='        <input class="form-control" type="hidden" id="stock_in_detail_'+$stock_row+'_unit_id" name="stock_in_details['+$stock_row+'][unit_id]" value="'+$unit_id+'" />';
    $html+='        <input class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_unit" name="stock_in_details['+$stock_row+'][unit]" value="'+$unit+'" readonly />';
    $html+='    </td>';
    $html+='    <td>';
    $html+='        <input onchange="calculateCOGAmount('+$stock_row+');" class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_qty" name="stock_in_details['+$stock_row+'][qty]" value="'+$qty+'" />';
    $html+='    </td>';
    $html+='    <td>';
    $html+='        <input onchange="calculateCOGAmount('+$stock_row+');" class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_cog_rate" name="stock_in_details['+$stock_row+'][cog_rate]" value="'+$cog_rate+'" />';
    $html+='    </td>';
    $html+='    <td>';
    $html+='        <input class="form-control" type="text" id="stock_in_detail_'+$stock_row+'_cog_amount" name="stock_in_details['+$stock_row+'][cog_amount]" value="'+$cog_amount+'" readonly/>';
    $html+='    </td>';
    $html+='   <td><a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html+='</tr>';

    $('#tblStockIn tbody').prepend($html);
    setFieldFormat();
    $stock_row++;

    calculateTotal();

    reset();
})

function calculateCOGAmount($stock_row) {
    var $quantity = $('#stock_in_detail_'+$stock_row+'_qty').val();
    var $cog_rate = $('#stock_in_detail_'+$stock_row+'_cog_rate').val();
    var $cog_amount = parseFloat($quantity) * parseFloat($cog_rate);
    $('#stock_in_detail_'+$stock_row+'_cog_amount').val($cog_amount);

    calculateTotal();
}

function removeRow($obj) {
    //console.log($obj);
    var $stock_row = $($obj).parent().parent().data('stock_row');
    $('#stock_row_'+$stock_row).remove();
    calculateTotal();
}

function calculateTotal() {
    var $total_qty = 0;
    var $total_amount = 0;
    $('#tblStockIn tbody tr').each(function() {
        var $stock_row = $(this).data('stock_row');
        var $qty = $('#stock_in_detail_'+$stock_row+'_qty').val();
        var $amount = $('#stock_in_detail_'+$stock_row+'_cog_amount').val();

        $total_qty += parseFloat($qty);
        $total_amount += parseFloat($amount);
    })

    $('#total_qty').val(roundUpto($total_qty,2));
    $('#total_amount').val(roundUpto($total_amount,2));
}
