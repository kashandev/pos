/**
 * Created by Huzaifa on 9/18/15.
 */

function getDetailReport() {
    var $data = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        warehouse_id: $('#warehouse_id').val(),
        container_no: $('#container_no').val(),
        product_category_id: $('#product_category_id').val(),
        product_id: $('#product_id').val()
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

function printDetail() {
    console.log('click')
    var $report_type = $('input[name="report_type"]:checked').val();
    if($report_type == 'Warehouse') {
        $('#form').attr('action', $UrlPrintWarehouseDetail).submit();
    } else if($report_type == 'Container') {
        $('#form').attr('action', $UrlPrintContainerDetail).submit();
    }
}

function printSummary() {
    console.log('click')
    var $report_type = $('input[name="report_type"]:checked').val();
    if($report_type == 'Warehouse') {
        $('#form').attr('action', $UrlPrintWarehouseSummary).submit();
    } else if($report_type == 'Container') {
        $('#form').attr('action', $UrlPrintContainerSummary).submit();
    }
}


function printExcel() {
        $('#form').attr('action', $UrlPrintExcel).submit();
    
}
function printExcelSummary() {
        $('#form').attr('action', $UrlPrintExcelSummary).submit();
    
}