/**
 * Created by Huzaifa on 9/18/15.
 */
$(document).on('change','#partner_type_id', function() {
    $partner_type_id = $(this).val();
    $.ajax({
        url: $UrlGetPartnerCategory,
        dataType: 'json',
        type: 'post',
        data: 'partner_type_id=' + $partner_type_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#partner_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#partner_id').select2('destroy');
                $('#partner_id').html(json.html);
                $('#partner_id').select2({width:'100%'});
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

function getDetailReport() {
    var $data = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        partner_type_id: $('#partner_type_id').val(),
        partner_id: $('#partner_id').val()
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

function printDetail() {
    $('#form').attr('action', $UrlPrintDetail).submit();
}

function printSummary() {
    $('#form').attr('action', $UrlPrintSummary).submit();
}

function printWarehouseSummary() {
    $('#form').attr('action', $UrlPrintWarehouseSummary).submit();
}

function printExcel() {
    $('#form').attr('action', $UrlPrintExcel).submit();
}