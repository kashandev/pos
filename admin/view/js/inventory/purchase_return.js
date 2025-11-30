/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#partner_type_id', function() {
    $partner_type_id = $(this).val();
    $.ajax({
        url: $UrlGetPartnerById,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            console.log(  )
            if(json.success)
            {
                $('#partner_id').select2('destroy');
                $('#partner_id').select2({width:'100%'});
                $('#partner_id').html('<option selected="selected" value="'+(json.partner['partner_id']??'')+'">'+(json.partner['name']??'')+'</option>');
                $('#partner_id').select2({
                width: '100%',
                ajax: {
                    url: $UrlGetPartnerJSON,
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
            else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
});

$(document).on('change','#partner_id', function() {
    $partner_id = $(this).val();
    if($partner_id != '') {
        $html = '';
        $html += '<option value="">&nbsp;</option>';
//        if($ref_document_type_id==17) {
//            $html += '<option value="17" selected="true">'+$lang['goods_received']+'</option>';
//        } else {
//            $html += '<option value="17">'+$lang['goods_received']+'</option>';
//        }
        if($ref_document_type_id==1) {
            $html += '<option value="1" selected="true">'+$lang['purchase_invoice']+'</option>';
        } else {
            $html += '<option value="1">'+$lang['purchase_invoice']+'</option>';
        }

        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html($html);
        $('#ref_document_type_id').select2({width: '100%'});
        $('#ref_document_type_id').trigger('change');
    } else {
        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html('');
        $('#ref_document_type_id').select2({width: '100%'});
    }
    $('#ref_document_identity').select2('destroy');
    $('#ref_document_identity').html('<option value="">&nbsp;</option>');
    $('#ref_document_identity').select2({width: '100%'});

});

$(document).on('change','#ref_document_type_id', function() {
    var $data = {
        partner_type_id : $('#partner_type_id').val(),
        partner_id : $('#partner_id').val(),
        ref_document_type_id : $('#ref_document_type_id').val(),
        ref_document_identity : $ref_document_identity,
        document_currency_id : $('#document_currency_id').val()
    };
    $.ajax({
        url: $UrlGetRefDocumentNo,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#ref_document_identity').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#ref_document_identity').select2('destroy');
                $('#ref_document_identity').html(json.html);
                $('#ref_document_identity').select2({width: '100%'});
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

$(document).on('change','#ref_document_identity', function() {
    var $data = {
        partner_type_id : $('#partner_type_id').val(),
        partner_id : $('#partner_id').val(),
        ref_document_type_id : $('#ref_document_type_id').val(),
        ref_document_identity : $('#ref_document_identity').val(),
        document_currency_id : $('#document_currency_id').val()
    };

    var $details = [];
    $.ajax({
        url: $UrlGetRefDocument,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#tblPurchaseInvoice').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $details = json['details'];
                for($i=0;$i<$details.length;$i++) {
                    fillGrid($details[$i]);
                }
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

function fillGrid($obj) {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="purchase_return_details['+$grid_row+'][ref_document_type_id]" id="purchase_return_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['document_type_id']+'" />';
    $html += '<input type="hidden" name="purchase_return_details['+$grid_row+'][ref_document_identity]" id="purchase_return_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['document_identity']+'" />';
    $html += '<a target="_blank" href="'+$obj['href']+'">'+$obj['document_identity']+'</a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details['+$grid_row+'][product_code]" id="purchase_return_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" id="purchase_return_detail_product_id_'+$grid_row+'" name="purchase_return_details['+$grid_row+'][product_id]" value="'+$obj['product_id']+'"/>';
    $html += '<input type="text" class="form-control" value="'+$obj['product_name']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2 warehouse_id" id="purchase_return_detail_warehouse_id_'+$grid_row+'" name="purchase_return_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        if($warehouse['warehouse_id'] == $obj['warehouse_id']) {
            $html += '<option value="'+$warehouse.warehouse_id+'" selected="true">'+$warehouse.name+'</option>';
        } else {
            $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
        }
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][qty]" id="purchase_return_detail_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '<input  style="min-width: 100px;" type="hidden" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][purchase_qty]" id="purchase_return_detail_purchase_qty_'+$grid_row+'" value="'+$obj['qty']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details['+$grid_row+'][unit]" id="purchase_return_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="purchase_return_details['+$grid_row+'][unit_id]" id="purchase_return_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][rate]" id="purchase_return_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][amount]" id="purchase_return_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][discount_percent]" id="purchase_return_detail_discount_percent_'+$grid_row+'" value="'+$obj['discount_percent']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][discount_amount]" id="purchase_return_detail_discount_amount_'+$grid_row+'" value="'+$obj['discount_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][gross_amount]" id="purchase_return_detail_gross_amount_'+$grid_row+'" value="'+$obj['gross_amount']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][tax_percent]" id="purchase_return_detail_tax_percent_'+$grid_row+'" value="'+$obj['tax_percent']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][tax_amount]" id="purchase_return_detail_tax_amount_'+$grid_row+'" value="'+$obj['tax_amount']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][total_amount]" id="purchase_return_detail_total_amount_'+$grid_row+'" value="'+$obj['total_amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][remarks]" id="purchase_return_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblPurchaseInvoice tbody').prepend($html);
    setFieldFormat();
    $('#purchase_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#purchase_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});

    $('#purchase_return_detail_product_id_'+$grid_row).select2({
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
    $('#purchase_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});

    $grid_row++;
    
    calculateTotal();
}

$(document).on('click','#btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '<td>';
    $html += '<input type="hidden" name="purchase_return_details['+$grid_row+'][ref_document_type_id]" id="purchase_return_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" name="purchase_return_details['+$grid_row+'][ref_document_identity]" id="purchase_return_detail_ref_document_identity_'+$grid_row+'" value="" />';
    $html += '&nbsp;';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details['+$grid_row+'][product_code]" id="purchase_return_detail_product_code_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select onchange="getProductById(this);" class="form-control select2" id="purchase_return_detail_product_id_'+$grid_row+'" name="purchase_return_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    // $products.forEach(function($product) {
    //     $html += '<option value="'+$product.product_id+'">'+$product.name+'</option>';
    // });
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_return_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2 warehouse_id" id="purchase_return_detail_warehouse_id_'+$grid_row+'" name="purchase_return_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][qty]" id="purchase_return_detail_qty_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details['+$grid_row+'][unit]" id="purchase_return_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="purchase_return_details['+$grid_row+'][unit_id]" id="purchase_return_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][rate]" id="purchase_return_detail_rate_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][amount]" id="purchase_return_detail_amount_'+$grid_row+'" value="0.00" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][discount_percent]" id="purchase_return_detail_discount_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][discount_amount]" id="purchase_return_detail_discount_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][gross_amount]" id="purchase_return_detail_gross_amount_'+$grid_row+'" value="" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][tax_percent]" id="purchase_return_detail_tax_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][tax_amount]" id="purchase_return_detail_tax_amount_'+$grid_row+'" value="0.00" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_return_details['+$grid_row+'][total_amount]" id="purchase_return_detail_total_amount_'+$grid_row+'" value="" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_return_details['+$grid_row+'][remarks]" id="purchase_return_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblPurchaseInvoice tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }
    setFieldFormat();


    // $('#tblPurchaseInvoice tbody').append($html);
    // setFieldFormat();

    $('#purchase_return_detail_product_id_'+$grid_row).select2({
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

    // $('#purchase_return_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#purchase_return_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
});

function getProductById($obj) {
    $product_id = $($obj).val();
    var $row_id = $($obj).parent().parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'post',
        data: 'product_id=' + $product_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#purchase_return_detail_product_code_'+$row_id).val(json.product['product_code']);
                $('#purchase_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#purchase_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#purchase_return_detail_rate_'+$row_id).val(json.product['cost_price']);
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

function getProductByCode($obj) {
    $product_code = $($obj).val();
    var $row_id = $($obj).parent().parent().data('row_id');
    $.ajax({
        url: $UrlGetProductByCode,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#grid_row_'+$row_id+' .QSearchProduct i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            $('#purchase_return_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {
                // console.log($row_id);
                $('#purchase_return_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#purchase_return_detail_unit_'+$row_id).val(json.product['unit']);
                $('#purchase_return_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                // $('#purchase_return_detail_product_id_'+$row_id).select2('destroy');
                // $('#purchase_return_detail_product_id_'+$row_id).val(json.product['product_id']);
                // $('#purchase_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#purchase_return_detail_rate_'+$row_id).val(json.product['cost_price']);
            }
            else {
                alert(json.error);
                $('#purchase_return_detail_unit_id_'+$row_id).val('');
                $('#purchase_return_detail_unit_'+$row_id).val('');
                // $('#purchase_return_detail_product_id_'+$row_id).select2('destroy');
                $('#purchase_return_detail_product_id_'+$row_id).html('');
                // $('#purchase_return_detail_product_id_'+$row_id).select2({width:'100%'});
                $('#purchase_return_detail_rate_'+$row_id).val('0.00');
            }
             $('#purchase_return_detail_product_id_'+$row_id).select2({
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
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#purchase_return_detail_product_code_'+$row_id).val($data['product_code']);
    $('#purchase_return_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#purchase_return_detail_unit_'+$row_id).val($data['unit']);
    $('#purchase_return_detail_rate_'+$row_id).val($data['cost_price']);
    $('#purchase_return_detail_product_id_'+$row_id).select2('destroy');
    $('#purchase_return_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#purchase_return_detail_product_id_'+$row_id).select2({
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

function removeRow($obj) {
    //console.log($obj);
    var $row_id = $($obj).parent().parent().data('row_id');
    $('#grid_row_'+$row_id).remove();
    calculateTotal();
}

function calculateRowTotal($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');

    var $PurchaseQty = parseFloat($('#purchase_return_detail_purchase_qty_' + $row_id).val());
    var $qty = parseFloat($('#purchase_return_detail_qty_' + $row_id).val());
    var $rate = parseFloat($('#purchase_return_detail_rate_' + $row_id).val());

    if($qty > $PurchaseQty)
    {
        alert("Qty greater than purchase qty");
        $('#purchase_return_detail_qty_' + $row_id).val($PurchaseQty);
        $qty = $PurchaseQty;
    }
    var $amount = $qty * $rate;
    $amount = roundUpto($amount,2);

    var $discount_amount = parseFloat($('#purchase_return_detail_discount_amount_' + $row_id).val());
    var $gross_amount = roundUpto($amount - $discount_amount,2);

    var $tax_amount = parseFloat($('#purchase_return_detail_tax_amount_' + $row_id).val());
    var $total_amount = roundUpto($gross_amount + $tax_amount,2);

    $('#purchase_return_detail_amount_' + $row_id).val($amount);
    $('#purchase_return_detail_gross_amount_' + $row_id).val($gross_amount);
    $('#purchase_return_detail_total_amount_' + $row_id).val($total_amount);

    calculateTotal();
}

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    $('#tblPurchaseInvoice tbody tr').each(function() {
        $row_id = $(this).data('row_id');
        $amount = $('#purchase_return_detail_amount_' + $row_id).val();
        $discount_amount = $('#purchase_return_detail_discount_amount_' + $row_id).val();
        $tax_amount = $('#purchase_return_detail_tax_amount_' + $row_id).val();
        $total_amount = $('#purchase_return_detail_total_amount_' + $row_id).val();

        $item_amount += parseFloat($amount);
        $item_discount += parseFloat($discount_amount);
        $item_tax += parseFloat($tax_amount);
        $item_total += parseFloat($total_amount);
    })

    var $deduction_amount = $('#deduction_amount').val() || 0.00;
    var $net_amount = $item_total - $deduction_amount;
    var $cash_received = $('#cash_received').val() || 0.00;
    var $balance_amount = $net_amount - $cash_received;

    $('#item_amount').val(roundUpto($item_amount,2));
    $('#item_discount').val(roundUpto($item_discount,2));
    $('#item_tax').val(roundUpto($item_tax,2));
    $('#item_total').val(roundUpto($item_total,2));
    $('#deduction_amount').val(roundUpto($deduction_amount,2));
    $('#net_amount').val(roundUpto($net_amount,2));
    $('#cash_received').val(roundUpto($cash_received,2));
    $('#balance_amount').val(roundUpto($balance_amount,2));
}

function calculateDiscountAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#purchase_return_detail_amount_' + $row_id).val() || 0.0000);
    var $discount_amount = roundUpto($amount * $discount_percent / 100,2);
    $('#purchase_return_detail_discount_amount_' + $row_id).val($discount_amount);
    calculateRowTotal($obj);
}

function calculateDiscountPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $discount_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#purchase_return_detail_amount_' + $row_id).val() || 0.0000);
    var $discount_percent = roundUpto($discount_amount / $amount * 100,2);

    $('#purchase_return_detail_discount_percent_' + $row_id).val($discount_percent);
    calculateRowTotal($obj);
}

function calculateTaxAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_percent = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#purchase_return_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_amount = roundUpto($amount * $tax_percent / 100,2);

    $('#purchase_return_detail_tax_amount_' + $row_id).val($tax_amount);
    calculateRowTotal($obj);
}

function calculateTaxPercent($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $tax_amount = parseFloat($($obj).val() || 0.0000);
    var $amount = parseFloat($('#purchase_return_detail_amount_' + $row_id).val() || 0.0000);
    var $tax_percent = roundUpto($tax_amount / $amount * 100,2);

    $('#purchase_return_detail_tax_percent_' + $row_id).val($tax_percent);
    calculateRowTotal($obj);
}

function Save() {
    $('.warehouse_id').each(function() {
        $(this).rules("add", 
            {
                required: true,
                messages: {
                    required: "Warehouse is required",
                  }
            });
    });
    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}
