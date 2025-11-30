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
                    url: $UrlGetPartnerJSON + '&partner_type_id='+$('#partner_type_id').val(),
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
      //  $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html('<option value="">&nbsp;</option><option value="17">'+$lang['goods_received']+'</option>');
        $('#ref_document_type_id').select2({width: '100%'});
    } else {
        $('#ref_document_type_id').select2('destroy');
        $('#ref_document_type_id').html('');
        $('#ref_document_type_id').select2({width: '100%'});
    }
   // $('#ref_document_identity').select2('destroy');
    $('#ref_document_identity').html('<option value="">&nbsp;</option>');
    $('#ref_document_identity').select2({width: '100%'});

});

$(document).on('change','#ref_document_type_id', function() {
    var $partner_type_id = $('#partner_type_id').val();
    var $partner_id = $('#partner_id').val();
    var $ref_document_type_id = $(this).val();
    $.ajax({
        url: $UrlGetRefDocumentNo,
        dataType: 'json',
        type: 'post',
        data: 'ref_document_type_id=' + $ref_document_type_id + '&partner_type_id=' + $partner_type_id + '&partner_id=' + $partner_id,
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

$(document).on('click','#addRefDocument', function() {
    var $data = {
        partner_type_id : $('#partner_type_id').val(),
        partner_id : $('#partner_id').val(),
        ref_document_type_id : $('#ref_document_type_id').val(),
        ref_document_identity : $('#ref_document_identity').val()
    };

    var $details = [];
    $.ajax({
        url: $UrlGetRefDocument,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#ref_document_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $.each(json.data['products'], function($i,$product) {
                    fillGrid($product);
                });
                calculateTotal();

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
    $html += '<td>';
    $html += '  <a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_type_id]" id="purchase_invoice_detail_ref_document_type_id_'+$grid_row+'" value="'+$obj['ref_document_type_id']+'" />';
    $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_identity]" id="purchase_invoice_detail_ref_document_identity_'+$grid_row+'" value="'+$obj['ref_document_identity']+'" />';
    $html += '<a target="_blank" href="'+$obj['href']+'">'+$obj['ref_document_identity']+'</a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][product_code]" id="purchase_invoice_detail_product_code_'+$grid_row+'" value="'+$obj['product_code']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="purchase_invoice_details['+$grid_row+'][product_id]" id="purchase_invoice_detail_product_id_'+$grid_row+'" value="'+$obj['product_id']+'" readonly/>';
    $html += '<input type="text" class="form-control" name="purchase_invoice_details['+$grid_row+'][product_name]" id="purchase_invoice_detail_product_name_'+$grid_row+'" value="'+$obj['product_name']+'" readonly/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="hidden" class="form-control" name="purchase_invoice_details['+$grid_row+'][warehouse_id]" id="purchase_invoice_detail_warehouse_id_'+$grid_row+'" value="'+$obj['warehouse_id']+'" readonly/>';
    $html += '<input type="text" class="form-control" name="purchase_invoice_details['+$grid_row+'][warehouse_name]" id="purchase_invoice_detail_warehouse_name_'+$grid_row+'" value="'+$obj['warehouse_name']+'" readonly/>';
    $html += '</td>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][qty]" id="purchase_invoice_detail_qty_'+$grid_row+'" value="'+$obj['balanced_qty']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit]" id="purchase_invoice_detail_unit_'+$grid_row+'" value="'+$obj['unit']+'" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit_id]" id="purchase_invoice_detail_unit_id_'+$grid_row+'" value="'+$obj['unit_id']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateRowTotal(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][rate]" id="purchase_invoice_detail_rate_'+$grid_row+'" value="'+$obj['rate']+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][amount]" id="purchase_invoice_detail_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_percent]" id="purchase_invoice_detail_discount_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_amount]" id="purchase_invoice_detail_discount_amount_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][gross_amount]" id="purchase_invoice_detail_gross_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_percent]" id="purchase_invoice_detail_tax_percent_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_amount]" id="purchase_invoice_detail_tax_amount_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][total_amount]" id="purchase_invoice_detail_total_amount_'+$grid_row+'" value="'+$obj['amount']+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][remarks]" id="purchase_invoice_detail_remarks_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td><a onclick="removeRow(this);" title="Remove" class="btn btn-sm btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a></td>';
    $html += '</tr>';


    $('#tblPurchaseInvoice tbody').prepend($html);
//    setFieldFormat();
//    $('#purchase_invoice_detail_product_id_'+$grid_row).select2({
//        width: '100%',
//        ajax: {
//            url: $UrlGetProductJSON,
//            dataType: 'json',
//            type: 'post',
//            mimeType:"multipart/form-data",
//            delay: 250,
//            data: function (params) {
//                return {
//                    q: params.term, // search term
//                    page: params.page
//                };
//            },
//            processResults: function (data, params) {
//                // parse the results into the format expected by Select2
//                // since we are using custom formatting functions we do not need to
//                // alter the remote JSON data, except to indicate that infinite
//                // scrolling can be used
//                params.page = params.page || 1;
//
//                return {
//                    results: data.items,
//                    pagination: {
//                        more: (params.page * 30) < data.total_count
//                    }
//                };
//            },
//            cache: true
//        },
//        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
//        minimumInputLength: 2,
//        templateResult: formatRepo, // omitted for brevity, see the source of this page
//        templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
//    });
//    $('#purchase_invoice_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
    
    calculateTotal();
}

$(document).on('click','.btnAddGrid', function() {
    $html = '';
    //EnterLKey(this);
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    //$html += '<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a>&nbsp;';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_type_id]" id="purchase_invoice_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    // $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_identity]" id="purchase_invoice_detail_ref_document_identity_'+$grid_row+'" value="" />';
    // $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" onchange="getProductByCode(this);" style="min-width: 100px;" class="form-control code1" name="purchase_invoice_details['+$grid_row+'][product_code]" id="purchase_invoice_detail_product_code_'+$grid_row+'" />';
    $html += '</td>';
    $html += '<td style="min-width: 300px;" id="exist_name_div_'+$grid_row+'">';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2 code1" id="purchase_invoice_detail_product_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][product_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_invoice_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';

    $html += '<td style="min-width: 300px;" class="hide" id="new_name_div_'+$grid_row+'">';
    $html += '<input type="text" style="min-width: 100px;" class="form-control product_name" name="purchase_invoice_details['+$grid_row+'][product_name]" id="purchase_invoice_detail_product_name_'+$grid_row+'" />';
    $html += '</td>';

    $html += '<td id="exist_category_div_'+$grid_row+'">';
    $html += '<div class="input-group">';
    $html += '<input type="hidden" class="form-control select2 product_category" id="purchase_invoice_detail_product_category_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][product_category_id]">';
    $html += '<input style="min-width: 100px;" class="form-control product_category" id="purchase_invoice_detail_product_category_'+$grid_row+'" readonly>';
    $html += '</div>';
    $html += '</td>';

    $html += '<td class="hide" id="new_category_div_'+$grid_row+'">';
    $html += '<select style="min-width: 100px;" class="form-control select2 new_product_category_id" id="purchase_invoice_detail_new_product_category_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][product_category_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $product_categories.forEach(function($category,$index) {
        $html += '<option value="'+$category.product_category_id+'" >'+$category.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';

    $html += '<td class="hide">';
    $html += '<select class="form-control select2 warehouse_id" id="purchase_invoice_detail_warehouse_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][warehouse_id]" >';
    $warehouses.forEach(function($warehouse,$index) {
        $index == 0 ? $selected = 'selected="true"' : $selected = '';
        $html += '<option value="'+$warehouse.warehouse_id+'" '+$selected+'>'+$warehouse.name+'</option>';
    });
    $html += '</select>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][qty]" id="purchase_invoice_detail_qty_'+$grid_row+'" value="12" />';
    $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit]" id="purchase_invoice_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    // $html += '<input type="hidden" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit_id]" id="purchase_invoice_detail_unit_id_'+$grid_row+'" value="" readonly/>';
    // $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][rate]" id="purchase_invoice_detail_rate_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][sale_rate]" id="purchase_invoice_detail_sale_rate_'+$grid_row+'" value="0" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][amount]" id="purchase_invoice_detail_amount_'+$grid_row+'" value="0" readonly="true" />';
    $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_percent]" id="purchase_invoice_detail_discount_percent_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_amount]" id="purchase_invoice_detail_discount_amount_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][gross_amount]" id="purchase_invoice_detail_gross_amount_'+$grid_row+'" value="" readonly="true"/>';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_percent]" id="purchase_invoice_detail_tax_percent_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_amount]" id="purchase_invoice_detail_tax_amount_'+$grid_row+'" value="0" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][total_amount]" id="purchase_invoice_detail_total_amount_'+$grid_row+'" value="" readonly="true" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][remarks]" id="purchase_invoice_detail_remarks_'+$grid_row+'" value="" />';
    // $html += '</td>';
    $html += '<td>';
    // $html += '<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a>&nbsp;';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblPurchaseInvoice tbody').append($html);
    } else {
        $(this).parent().parent().after($html);
    }
    // setFieldFormat();
    //$('#purchase_invoice_detail_product_id_'+$grid_row).select2({width: '100%'});
    $('#purchase_invoice_detail_product_id_'+$grid_row).select2({
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
    $('#purchase_invoice_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $('#purchase_invoice_detail_warehouse_id_'+$grid_row).trigger('change');

    $grid_row++;
});

$(document).on('click','.btnAddDuplicate', function() {
    var $row_id = $(this).parent().parent().data('row_id');
    var $warehouse_id = $('#purchase_invoice_detail_warehouse_id_'+$row_id+' option:selected').val();
    var $product_code = $('#purchase_invoice_detail_product_code_'+$row_id).val();
    var $product_id = $('#purchase_invoice_detail_product_id_'+$row_id+' option:selected').val();
    var $product_name = $('#purchase_invoice_detail_product_id_'+$row_id+' option:selected').text();
    var $container_no = $('#purchase_invoice_detail_container_no_'+$row_id).val();
    var $batch_no = $('#purchase_invoice_detail_batch_no_'+$row_id).val();
    var $unit_id = $('#purchase_invoice_detail_unit_id_'+$row_id).val();
    var $qty = $('#purchase_invoice_detail_qty_'+$row_id).val();
    var $total_cubic_meter = $('#purchase_invoice_detail_total_cubic_meter_'+$row_id).val();
    var $total_cubic_feet = $('#purchase_invoice_detail_total_cubic_feet_'+$row_id).val();
    var $rate = $('#purchase_invoice_detail_rate_'+$row_id).val();
    var $amount = $('#purchase_invoice_detail_amount_'+$row_id).val();
    var $discount_percent = $('#purchase_invoice_detail_discount_percent_'+$row_id).val();
    var $discount_amount = $('#purchase_invoice_detail_discount_amount_'+$row_id).val();
    var $gross_amount = $('#purchase_invoice_detail_gross_amount_'+$row_id).val();
    var $tax_percent = $('#purchase_invoice_detail_tax_percent_'+$row_id).val();
    var $tax_amount = $('#purchase_invoice_detail_tax_amount_'+$row_id).val();
    var $total_amount = $('#purchase_invoice_detail_total_amount_'+$row_id).val();
    var $remarks = $('#purchase_invoice_detail_remarks_'+$row_id).val();
    //console.log($product_id, $product_name);

    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a>&nbsp;';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    //$html += '<td>';
    $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_type_id]" id="purchase_invoice_detail_ref_document_type_id_'+$grid_row+'" value="" />';
    $html += '<input type="hidden" name="purchase_invoice_details['+$grid_row+'][ref_document_identity]" id="purchase_invoice_detail_ref_document_identity_'+$grid_row+'" value="" />';
    //$html += '&nbsp;';
    //$html += '</td>';
    $html += '<td>';
    $html += '<input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][product_code]" id="purchase_invoice_detail_product_code_'+$grid_row+'" value="'+$product_code+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<div class="input-group">';
    $html += '<select style="min-width: 100px;" onchange="getProductById(this);" class="form-control select2" id="purchase_invoice_detail_product_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][product_id]" >';
    $html += '<option selected="true" value="'+$product_id+'">'+$product_name+'</option>';
    $html += '</select>';
    $html += '<span class="input-group-btn ">';
    $html += '<button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="purchase_invoice_detail_product_id_'+$grid_row+'" data-field="product_id">';
    $html += '<i class="fa fa-search"></i>';
    $html += '</button>';
    $html += '</span>';
    $html += '</div>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2 warehouse_id" id="purchase_invoice_detail_warehouse_id_'+$grid_row+'" name="purchase_invoice_details['+$grid_row+'][warehouse_id]" >';
    $html += '<option value="">&nbsp;</option>';
    $warehouses.forEach(function($warehouse) {
        if($warehouse_id==$warehouse.warehouse_id) {
            $html += '<option value="'+$warehouse.warehouse_id+'" selected="true">'+$warehouse.name+'</option>';
        } else {
            $html += '<option value="'+$warehouse.warehouse_id+'">'+$warehouse.name+'</option>';
        }
    });
    $html += '</select>';
    $html += '</td>';


    // $html += '<td>';
    // $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][cubic_meter]" id="purchase_invoice_detail_cubic_meter_'+$grid_row+'" value="'+$cubic_meter+'" readonly/>';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][cubic_feet]" id="purchase_invoice_detail_cubic_feet_'+$grid_row+'" value="'+$cubic_feet+'" readonly/>';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][container_no]" id="purchase_invoice_detail_container_no_'+$grid_row+'" value="'+$container_no+'" />';
    // $html += '</td>';
    // $html += '<td>';
    // $html += '<input style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][batch_no]" id="purchase_invoice_detail_batch_no_'+$grid_row+'" value="'+$batch_no+'" />';
    // $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][qty]" id="purchase_invoice_detail_qty_'+$grid_row+'" value="'+$qty+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit]" id="purchase_invoice_detail_unit_'+$grid_row+'" value="" readonly="true" />';
    $html += '<input type="hidden" class="form-control" name="purchase_invoice_details['+$grid_row+'][unit_id]" id="purchase_invoice_detail_unit_id_'+$grid_row+'" value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][rate]" id="purchase_invoice_detail_rate_'+$grid_row+'" value="'+$rate+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][amount]" id="purchase_invoice_detail_amount_'+$grid_row+'" value="'+$amount+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_percent]" id="purchase_invoice_detail_discount_percent_'+$grid_row+'" value="'+$discount_percent+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateDiscountPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][discount_amount]" id="purchase_invoice_detail_discount_amount_'+$grid_row+'" value="'+$discount_amount+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][gross_amount]" id="purchase_invoice_detail_gross_amount_'+$grid_row+'" value="'+$gross_amount+'" readonly="true"/>';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxAmount(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_percent]" id="purchase_invoice_detail_tax_percent_'+$grid_row+'" value="'+$tax_percent+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input onchange="calculateTaxPercent(this);" type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][tax_amount]" id="purchase_invoice_detail_tax_amount_'+$grid_row+'" value="'+$tax_amount+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="purchase_invoice_details['+$grid_row+'][total_amount]" id="purchase_invoice_detail_total_amount_'+$grid_row+'" value="'+$total_amount+'" readonly="true" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" style="min-width: 100px;" class="form-control" name="purchase_invoice_details['+$grid_row+'][remarks]" id="purchase_invoice_detail_remarks_'+$grid_row+'" value="'+$remarks+'" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Duplicate" class="btn btn-xs btn-primary btnAddDuplicate" href="javascript:void(0);"><i class="fa fa-clone"></i></a>&nbsp;';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>&nbsp;';
    $html += '<a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>';


    $('#tblPurchaseInvoice tbody #grid_row_' + $row_id).after($html);
    setFieldFormat();
    $('#purchase_invoice_detail_product_id_'+$grid_row).select2({
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
    $('#purchase_invoice_detail_warehouse_id_'+$grid_row).select2({width: '100%'});
    $grid_row++;
    calculateTotal();
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
                $('#purchase_invoice_detail_product_code_'+$row_id).val(json.product['product_code']).trigger('change');
                $('#purchase_invoice_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#purchase_invoice_detail_unit_'+$row_id).val(json.product['unit']);
                $('#purchase_invoice_detail_rate_'+$row_id).trigger('change');
                $('#purchase_invoice_detail_discount_percent_'+$row_id).trigger('change');
                $('#purchase_invoice_detail_tax_percent_'+$row_id).trigger('change');
            }
            else {
                //alert(json.error);
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
    var $cost_price = 0;
    checkExistProduct($product_code,$row_id);
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
            $('#purchase_invoice_detail_product_id_'+$row_id).select2('destroy');
            if(json.success)
            {
                $cost_price = parseFloat(json.product['cost_price']);
                $('#purchase_invoice_detail_unit_id_'+$row_id).val(json.product['unit_id']);
                $('#purchase_invoice_detail_unit_'+$row_id).val(json.product['unit']);
                $('#purchase_invoice_detail_product_id_'+$row_id).html('<option selected="selected" value="'+json.product['product_id']+'">'+json.product['name']+'</option>');
                $('#purchase_invoice_detail_product_category_id_'+$row_id).val(json.product['product_category_id']);
                $('#purchase_invoice_detail_product_category_'+$row_id).val(json.product['product_category']);
                $('#purchase_invoice_detail_rate_'+$row_id).val($cost_price);
                $('#purchase_invoice_detail_rate_'+$row_id).trigger('change');
                $('#purchase_invoice_detail_discount_percent_'+$row_id).trigger('change');
                $('#purchase_invoice_detail_tax_percent_'+$row_id).trigger('change');
            } else {
                // alert(json.error);
                $('#purchase_invoice_detail_unit_id_'+$row_id).val('');
                $('#purchase_invoice_detail_unit_'+$row_id).val('');
                $('#purchase_invoice_detail_product_id_'+$row_id).html('');
                $('#purchase_invoice_detail_product_category_id_'+$row_id).val('');
                $('#purchase_invoice_detail_product_category_'+$row_id).val('');
                $('#purchase_invoice_detail_rate_'+$row_id).val('0');
                $('#purchase_invoice_detail_sale_rate_'+$row_id).val('0');
                $('#purchase_invoice_detail_amount_'+$row_id).val('0');

            }
            $('#purchase_invoice_detail_product_id_'+$row_id).select2({
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

// check exist product function
function checkExistProduct($product_code,$row_id) {
     $.ajax({
        url: $UrlCheckExistProduct,
        dataType: 'json',
        type: 'post',
        data: 'product_code=' + $product_code,
        mimeType:"multipart/form-data",

        success: function(json) {
            if(json.success == true) {
                $("#exist_name_div_"+$row_id).removeClass('hide');
                $("#new_name_div_"+$row_id).addClass('hide');
                $("#exist_category_div_"+$row_id).removeClass('hide');
                $("#new_category_div_"+$row_id).addClass('hide');
            }
            else {
                $("#exist_name_div_"+$row_id).addClass('hide');
                $("#new_name_div_"+$row_id).removeClass('hide');
                $("#exist_category_div_"+$row_id).addClass('hide');
                $("#new_category_div_"+$row_id).removeClass('hide');
                $('#purchase_invoice_detail_new_product_category_id_'+$row_id).select2({width: '100%'});
                $('#purchase_invoice_detail_rate_'+$row_id).val('0').trigger('change');

            }
    
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    });

}

function setProductInformation($obj) {
    var $data = $($obj).data();
    var $row_id = $('#'+$data['element']).parent().parent().parent().data('row_id');
    $('#_modal').modal('hide');
    $('#purchase_invoice_detail_product_code_'+$row_id).val($data['product_code']).trigger('change');
    $('#purchase_invoice_detail_unit_id_'+$row_id).val($data['unit_id']);
    $('#purchase_invoice_detail_unit_'+$row_id).val($data['unit']);
    $('#purchase_invoice_detail_product_id_'+$row_id).select2('destroy');
    $('#purchase_invoice_detail_product_id_'+$row_id).html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#purchase_invoice_detail_product_category_id_'+$row_id).val($data['product_category_id']);
    $('#purchase_invoice_detail_product_category_'+$row_id).val($data['product_category']);
       
     $('#purchase_invoice_detail_product_id_'+$row_id).select2({
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

function calculateAmount($obj) {
    var $row_id = $($obj).parent().parent().data('row_id');
    var $qty = parseFloat($('#purchase_invoice_detail_qty_' + $row_id).val()) || 0;
    var $rate = parseFloat($('#purchase_invoice_detail_rate_' + $row_id).val()) || 0;


    var $amount = $qty * $rate;
    $amount = roundUpto($amount,0);

    $('#purchase_invoice_detail_amount_' + $row_id).val($amount);
    calculateTotal();

//     $('#purchase_invoice_detail_discount_percent_' + $row_id).trigger('change');
//     $('#purchase_invoice_detail_tax_percent_' + $row_id).trigger('change');
}

// function calculateDiscountAmount($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');
//     var $discount_percent = parseFloat($($obj).val() || 000);
//     var $amount = parseFloat($('#purchase_invoice_detail_amount_' + $row_id).val() || 000);
//     var $discount_amount = roundUpto($amount * $discount_percent / 100,0);
//     $('#purchase_invoice_detail_discount_amount_' + $row_id).val($discount_amount);
//     calculateRowTotal($obj);
// }

// function calculateDiscountPercent($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');
//     var $discount_amount = parseFloat($($obj).val() || 000);
//     var $amount = parseFloat($('#purchase_invoice_detail_amount_' + $row_id).val() || 000);
//     var $discount_percent = roundUpto($discount_amount / $amount * 100,0);

//     $('#purchase_invoice_detail_discount_percent_' + $row_id).val($discount_percent);
//     calculateRowTotal($obj);
// }

// function calculateTaxAmount($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');
//     var $tax_percent = parseFloat($($obj).val() || 000);
//     var $amount = parseFloat($('#purchase_invoice_detail_amount_' + $row_id).val() || 000);
//     var $tax_amount = roundUpto($amount * $tax_percent / 100,0);

//     $('#purchase_invoice_detail_tax_amount_' + $row_id).val($tax_amount);
//     calculateRowTotal($obj);
// }

// function calculateTaxPercent($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');
//     var $tax_amount = parseFloat($($obj).val() || 000);
//     var $amount = parseFloat($('#purchase_invoice_detail_amount_' + $row_id).val() || 000);
//     var $tax_percent = roundUpto($tax_amount / $amount * 100,0);

//     $('#purchase_invoice_detail_tax_percent_' + $row_id).val($tax_percent);
//     calculateRowTotal($obj);
// }

// function calculateRowTotal($obj) {
//     var $row_id = $($obj).parent().parent().data('row_id');

//     var $amount = parseFloat($('#purchase_invoice_detail_amount_' + $row_id).val());
//     var $discount_amount = parseFloat($('#purchase_invoice_detail_discount_amount_' + $row_id).val());
//     var $gross_amount = roundUpto($amount - $discount_amount,0);

//     var $tax_amount = parseFloat($('#purchase_invoice_detail_tax_amount_' + $row_id).val());
//     var $total_amount = roundUpto($gross_amount + $tax_amount,0);

//     $('#purchase_invoice_detail_gross_amount_' + $row_id).val($gross_amount);
//     $('#purchase_invoice_detail_total_amount_' + $row_id).val($total_amount);

//     calculateTotal();
// }

function calculateTotal() {
    var $item_amount = 0;
    var $item_discount = 0;
    var $item_tax = 0;
    var $item_total = 0;
    var $total_quantity = 0;
    $('#tblPurchaseInvoice tbody tr').each(function() {
        var $row_id = $(this).data('row_id');
        var $amount = $('#purchase_invoice_detail_amount_' + $row_id).val();
        // var $discount_amount = $('#purchase_invoice_detail_discount_amount_' + $row_id).val();
        // var $tax_amount = $('#purchase_invoice_detail_tax_amount_' + $row_id).val();
        // var $total_amount = $('#purchase_invoice_detail_total_amount_' + $row_id).val();
        var $quantity = $('#purchase_invoice_detail_qty_' + $row_id).val();

        $item_amount += parseFloat($amount);
        // $item_discount += parseFloat($discount_amount);
        // $item_tax += parseFloat($tax_amount);
        // $item_total += parseFloat($total_amount);
        $total_quantity += parseFloat($quantity);
    })

    // var $discount = $('#discount').val() || 0;
    var $freight_master = parseFloat($('#freight_master').val()) || 0;
    var $net_amount = $item_amount + $freight_master;
    // var $net_amount = $item_total - $discount + $freight_master;
    var $cash_paid = $('#cash_paid').val() || 0;
    var $balance_amount = $net_amount - $cash_paid;

    $('#total_quantity').val(roundUpto($total_quantity,0));
    $('#item_amount').val(roundUpto($item_amount,0));
    // $('#item_discount').val(roundUpto($item_discount,0));
    // $('#item_tax').val(roundUpto($item_tax,0));
    $('#item_total').val(roundUpto($net_amount,0));
    // $('#discount').val(roundUpto($discount,0));
    $('#net_amount').val(roundUpto($net_amount,0));
    $('#cash_paid').val(roundUpto($cash_paid,0));
    $('#balance_amount').val(roundUpto($balance_amount,0));
}


// Freight Master
function calculateTotalFreight() {
    var $total_freight = parseFloat($('#freight_master').val()) || 0;
    //var $total_qty = parseFloat($('#qty_master').val()) || 0;
    var $item_amount = parseFloat($('#item_amount').val()) || 0;
    var $total_net_amount = ($item_amount + $total_freight);
    $('#item_total').val($total_net_amount); 
    $('#net_amount').val($total_net_amount);  
    calculateTotal();   
}


//    $('.code1').keyup(function(e) {
//        console.log($row_id);
//        if (e.which == 13) {
//            $($row_id).closest('td').next().find('input.code1').focus();
//            e.preventDefault();
//        }
//    });




function EventCode($obj) {

//    var inputs = $('#tblPurchaseInvoice tbody tr td :input ').keypress(function (e) {
//        if (e.which == 13) {
//            e.preventDefault();
//            var nextInput = inputs.get(inputs.index(this) + 1);
//            if (nextInput) {
//                nextInput.focus();
//            }
//        }
//    });

//    var $row_id = $($obj).parent().parent().data('row_id');
//    var $row_id = $($obj).parent().parent().data('row_id');
//    $('.code1').keydown(function (e) {
//        if (e.which === 13) {
//            var index = $('.code1').index(this) + 1;
//            $('.code1').eq(index).focus();
//        }
//    })

//
//    $('input').keypress(function(e) {
//        if (e.keyCode == 13) {
//            var $this = $(this),
//                index = $this.closest('td').index();
//
//            $this.closest('td').next().find('td').eq(index).find('input').focus();
//            e.preventDefault();
//        }
//    })

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
