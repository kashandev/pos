/**
 * Created by Huzaifa on 9/18/15.
 */

//$("#loaderjs").removeClass('hide');
$(document).ready(function(){
    $("#loaderjs").addClass('hide');
});

// $(document).on('change','#warehouse_id',function () {
//     var $data = {
//         warehouse_id: $(this).val()
//     };
//     $.ajax({
//         url: $UrlGetWarehouseStocks,
//         dataType: 'json',
//         type: 'post',
//         data: $data,
//         mimeType:"multipart/form-data",
//         beforeSend: function() {
//             $('#warehouse_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//         },
//         complete: function() {
//             $('#loader').remove();
//         },
//         success: function(json) {
//             if(json.success)
//             {
//                 $('#tblStockAdjustment tbody').html(json.html);
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


$(document).on('click','#btnAddGrid, .btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control"id="stock_adjustment_detail_product_code_'+$grid_row+'" value="" autocomplete="off" />';
    $html += '</td>';
    $html += '<td style="min-width: 300px;">';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="stock_adjustment_detail_product_id_'+$grid_row+'">';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="stock_adjustment_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" readonly class="form-control" id="stock_adjustment_detail_unit_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" class="form-control" id="stock_adjustment_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" id="stock_adjustment_detail_stock_qty_'+ $grid_row +'" readonly disabled class="form-control">';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" id="stock_adjustment_detail_hidden_qty_'+ $grid_row +'" value="0">';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" id="stock_adjustment_detail_qty_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" id="stock_adjustment_detail_hidden_rate_'+ $grid_row +'" value="0">';
    $html += '<input onchange="calculateRowTotal(this)" type="text" style="min-width: 100px;" class="form-control fPDecimal" id="stock_adjustment_detail_rate_'+$grid_row+'" value="0.00"  />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" id="stock_adjustment_detail_hidden_amount_'+ $grid_row +'" value="0">';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" id="stock_adjustment_detail_amount_'+$grid_row+'" value="0.00"  />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';
    

    $('#tblStockAdjustment tbody').append($html);
    setFieldFormat();
    // if( $(this).hasClass('btnAddGrid') ){
    //     $(this).parents('tr').after($html);
    // } else {
    //     $('#tblStockAdjustment tbody').prepend($html);
    // }

    $('#stock_adjustment_detail_product_id_'+$grid_row).html('');
    $('#stock_adjustment_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#stock_adjustment_detail_product_id_'+$grid_row).select2({
        width: '100%',
        ajax: {
            url: $UrlGetProductJSON,
            dataType: 'json',
            type: 'post',
            mimeType:"multipart/form-data",
            delay: 250,
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
        minimumInputLength: 2,
        templateResult: formatRepo, // omitted for brevity, see the source of this page
        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
    });

    $grid_row++;
});

function removeRow($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function getProductById($obj) {
    $product_id = $($obj).val();
    $partner_id = $('#partner_id').val();
    $warehouse_id = $('#warehouse_id').val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    if( empty( $warehouse_id ) ) {
        alert('Please Select Warehouse');
        $('#warehouse_id').select2('open');
        return;
    }
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id+'&warehouse_id='+$warehouse_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            console.log( json )
            if(json.success) {
                $('#stock_adjustment_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#stock_adjustment_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#stock_adjustment_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#stock_adjustment_detail_unit_'+$row_id).val(json.product['unit']);

            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });

    getWarehouseStocks($obj)
}

function getProductByCode($obj) {
    $product_code = $($obj).val();
    $partner_id = $('#partner_id').val();
    var $row_id = $($obj).parent().parent().data('row_id');

    $warehouse_id = $('#warehouse_id').val();
    if( empty( $warehouse_id ) ) {
        alert('Please Select Warehouse');
        $('#warehouse_id').select2('open');
        $('#stock_adjustment_detail_product_id_'+$row_id).html('');
        return;
    }

    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code+'&partner_id=' + $partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            $('#stock_adjustment_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {
                $('#stock_adjustment_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#stock_adjustment_detail_unit_'+$row_id).val(json.product['unit']);
                $('#stock_adjustment_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
            } else {
                alert(json.error);
                $('#stock_adjustment_detail_unit_id_'+$row_id).val('');
                $('#stock_adjustment_detail_unit_'+$row_id).val('');
                $('#stock_adjustment_detail_stock_qty_'+$row_id).val('');
                $('#stock_adjustment_detail_product_code_'+$row_id).val('');
                $('#stock_adjustment_detail_product_id_'+$row_id).select2({'width':'100%'});
                $('#stock_adjustment_detail_product_id_'+$row_id).html('');
                $('#stock_adjustment_detail_product_id_'+$row_id).select2({
                    width: '100%',
                    ajax: {
                        url: $UrlGetProductJSON,
                        dataType: 'json',
                        type: 'post',
                        mimeType:"multipart/form-data",
                        delay: 250,
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
                    minimumInputLength: 2,
                    templateResult: formatRepo, // omitted for brevity, see the source of this page
                    templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
                });
            }            
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });

    getWarehouseStocks($('#stock_adjustment_detail_product_id_'+$row_id));
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');

    $warehouse_id = $('#warehouse_id').val();
    if( empty( $warehouse_id ) ) {
        alert('Please Select Warehouse');
        $('#warehouse_id').select2('open');
        return;
    }

    $('#_modal').modal('hide');
    $('#stock_adjustment_detail_product_code_'+$row_id).val($data['product_code']);    
    $('#stock_adjustment_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#stock_adjustment_detail_unit_'+$row_id).val($data['unit']);
    $('#stock_adjustment_detail_product_id_'+$row_id).select2('destroy');
    $('#stock_adjustment_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#stock_adjustment_detail_product_id_'+$row_id).select2({width: '100%'});
    getWarehouseStocks($('#stock_adjustment_detail_product_id_'+$row_id));    
}   



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
    $('#total_amount').val(roundUpto($total_amount,2));
}

function Save() {

    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}

function getWarehouseStocks($obj){

    setTimeout(function(){
        var $row_id = $($obj).parent().parent().parent().data('row_id');
        var $warehouse_id = $('#warehouse_id').val();
        var $product_id = $('#stock_adjustment_detail_product_id_'+$row_id).val();

        $.ajax({
            url: $UrlGetWarehouseStocks,
            dataType: 'json',
            type: 'post',
            data: 'product_id='+$product_id+'&warehouse_id='+$warehouse_id,
            mimeType:"multipart/form-data",
            beforeSend: function() {
                $('#stock_adjustment_detail_stock_qty_'+$row_id).val('Loading...');
                $('#stock_adjustment_detail_qty_'+ $row_id).val('Loading...');
                $('#stock_adjustment_detail_rate_'+ $row_id).val('Loading...');
                $('#stock_adjustment_detail_amount_'+ $row_id).val('Loading...');
            },
            complete: function() {
                $('#loader').remove();
            },
            success: function(json) {
                if(json.success)
                {
                    $stock_qty = parseFloat(json.stock['stock_qty']) || 0;
                    $stock_rate = parseFloat(json.stock['avg_stock_rate']) || 0;
                    $stock_amount = parseFloat(json.stock['stock_amount']);

                    $('#stock_adjustment_detail_stock_qty_'+$row_id).val($stock_qty);
                    
                    // Average Stock Qty
                    $('#stock_adjustment_detail_qty_'+ $row_id).val($stock_qty);
                    $('#stock_adjustment_detail_hidden_qty_'+ $row_id).val($stock_qty);
                    
                    // Average Stock Rate
                    $('#stock_adjustment_detail_rate_'+ $row_id).val($stock_rate);
                    $('#stock_adjustment_detail_hidden_rate_'+ $row_id).val($stock_rate);
                    
                    // Average Stock Amount
                    $('#stock_adjustment_detail_amount_'+ $row_id).val($stock_amount);
                    $('#stock_adjustment_detail_hidden_amount_'+ $row_id).val($stock_amount);

                    $('#stock_adjustment_detail_rate_'+ $row_id).trigger('change');

                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(xhr.responseText);
            }
        });
    },1000);
}

function Save() {
    if($('#form').valid() == true){
        // $warehouse_validate = 0;
        // $('.warehouse_id').each(function() {
        //     if( $(this).val() == '' ){
        //         $(this).siblings('span.validate-error').remove();
        //         $(this).parent('td').append('<span class="validate-error" style="color:red; font-size:small;font-weight:700;display:inline-block;margin-bottom:5px;max-width:100%;font-size:small">This field is required.</span>');
        //         $warehouse_validate++;
        //     }
        // });
        // if( $warehouse_validate > 0 ){
        //     return false;
        // } else {
            $('.btnsave').attr('disabled','disabled');
            $("#loaderjs").removeClass('hide');
            $('#tblStockAdjustment tbody tr').each(function(sort_order) {
                var $row_id = $(this).data('row_id');
                $data = [];
                $data['row_id'] = $row_id;
                $data['sort_order'] = sort_order;
                $data['form_key'] = $('#form_key').val();

                $(this).children('td').find('input, select').each(function(){
                    $name = $(this).attr('id').replace('stock_adjustment_detail_', '');
                    $name = $name.replace(/_[0-9]+/, '');
                    $data[$name] = $(this).val();
                });
                $.ajax({
                    url: $UrlAddRecords,
                    dataType: 'json',
                    type: 'post',
                    async: false,
                    data: $.extend({}, $data), // Convert data array into object
                    mimeType:"multipart/form-data",
                    beforeSend: function() {
                        // $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
                    },
                    complete: function() {
                        //$('#loader').remove();
                    },
                    success: function(json) {

                        if(json.success)
                        {
                            console.log( json )
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        console.log(xhr.responseText);
                    }
                });                
            });

            $('#form').submit();
            //console.log("Form Submitted");
        // }
    } else{
        $('.btnsave').removeAttr('disabled');
        $("#loaderjs").addClass('hide');
    }
}

function getLedger() {
    var $document_type_id = $('#document_type_id').val();
    var $document_id = $('#document_id').val();
    $.ajax({
        url: $UrlGetLedger,
        dataType: 'json',
        type: 'post',
        data: 'document_type_id=' + $document_type_id+'&document_id='+$document_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
        },
        complete: function() {
        },
        success: function(json) {
            if(json.success)
            {
                $('#_modal .modal-title').html(json.title);
                $('#_modal .modal-body').html(json.html);
                $('#_modal').modal();
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}