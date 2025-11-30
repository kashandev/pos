/**
 * Created by Huzaifa on 9/18/15.
 */

function getDetailReport() {


        var $month = $('#month_id').val();
        var $product_category_id = $('#product_category_id').val();
        var $product_id = $('#product_id').val();

        $.ajax({
            url: $UrlGetDetailReport,
            dataType: 'json',
            type: 'post',
            data: 'month=' + $month,
            mimeType: "multipart/form-data",
            beforeSend: function () {
                $('#btnFilter').append('<i id="loader" class="fa fa-search fa-spin">&nbsp;</i>');
                $dataTable.destroy();
            },
            complete: function () {
                $('#loader').remove();
                $dataTable = $('#tblReport').DataTable();
            },
            success: function (json) {
                if (json.success) {
                    $('#tblReport tbody').html(json.html);
                }
                else {
                    alert(json.error);
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(xhr.responseText);
            }
        })

}

function printDetailReport() {
    var selected_month = $('#month_id').val();
    //alert('value:'+selected_month);
    if(selected_month != '') {
    $('#form').attr('action', $UrlPrintDetailReport).submit();
    }
    else
    {
        alert('Please select a valid value in "Months Before"...!');
    }
}
function printExcelReport(){
    $('#form').attr('action', $UrlExcelReport).submit();


}
function printSummaryReport() {
    $('#form').attr('action', $UrlPrintSummaryReport).submit();
}