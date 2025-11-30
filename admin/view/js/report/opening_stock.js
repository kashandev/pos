/**
 * Created by Huzaifa on 9/18/15.
 */

function getDetailReport() {
    var $warehouse_id = $('#warehouse_id').val();
    var $product_category_id = $('#product_category_id').val();
    var $product_id = $('#product_id').val();

    $.ajax({
        url: $UrlGetDetailReport,
        dataType: 'json',
        type: 'post',
        data: 'warehouse_id=' + $warehouse_id + '&product_category_id=' + $product_category_id + '&product_id=' + $product_id,
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

function printDetailReport() {
    $('#form').attr('action', $UrlPrintDetailReport).submit();
}

function printSummaryReport() {
    $('#form').attr('action', $UrlPrintSummaryReport).submit();
}

function printExcel() {
    $('#form').attr('action', $UrlPrintExcel).submit();
}