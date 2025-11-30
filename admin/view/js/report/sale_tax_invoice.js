/**
 * Created by Huzaifa on 9/18/15.
 */

//$(document).on('change','#partner_type_id', function() {
//    $partner_type_id = $(this).val();
//    $partner_id = $('#partner_id').val();
//    $.ajax({
//        url: $UrlGetPartner,
//        dataType: 'json',
//        type: 'post',
//        data: 'partner_type_id=' + $partner_type_id+'&partner_id='+$partner_id,
//        mimeType:"multipart/form-data",
//        beforeSend: function() {
//            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
//        },
//        complete: function() {
//            $('#loader').remove();
//        },
//        success: function(json) {
//            if(json.success)
//            {
//                $('#partner_id').select2('destroy');
//                $('#partner_id').html(json.html);
//                $('#partner_id').select2({width:'100%'});
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

function getDetailReport() {
    var $data = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        partner_type_id: $('#partner_type_id').val(),
        partner_id: $('#partner_id').val(),
        product_id: $('#product_id').val(),
        warehouse_id: $('#warehouse_id').val(),
    }

    $.ajax({
        url: $UrlGetDetailReport,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#btnFilter').append('<i id="loader" class="fa fa-search fa-spin">&nbsp;</i>');
            $dataTable.destroy();
        },
        complete: function() {
            $('#loader').remove();
            $dataTable = $('#tblReport').DataTable();
        },
        success: function(json) {
            if(json.success)
            {
                $('#tblReport tbody').html(json.html);
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}

function setProductInformation($obj) {
    var $data = $($obj).data();
    console.log($data);
    $('#_modal').modal('hide');
    // $('#product_id').val($data['product_id']);
    // $('#product_id').select2({width: '100%'});
    $('#product_id').html('<option selected="selected" value="'+$data['product_id']+'">'+$data['name']+'</option>');
    $('#product_id').select2({
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

function getProductInformation() {
    var product_id = $('#product_id').val();
    $.ajax({
        url: $UrlGetProductById,
        dataType: 'json',
        type: 'POST',
        data: 'product_id=' + product_id,
        beforeSend: function() {
//            $('#product_id').after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.wait').remove();
        },
        success: function(json) {
            if(json.success) {
                $('#product_id').html(json.html).trigger("change");
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

function printDetail() {
    $('#form').attr('action', $UrlPrint).submit();
}


function printExcel() {
    $('#form').attr('action', $UrlPrintExcel).submit();
}

