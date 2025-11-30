/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#coa_level1_id', function() {
    var $coa_level1_id = $(this).val();
    $.ajax({
        url: $UrlGetCOALevel2,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#coa_level2_id').after('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#coa_level2_id').select2('destroy');
                $('#coa_level2_id').html(json.html);
                $('#coa_level2_id').select2({width:'100%'});
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

$(document).on('change','#coa_level2_id', function() {
    var $coa_level1_id = $('#coa_level1_id').val();
    var $coa_level2_id = $(this).val();
    $.ajax({
        url: $UrlGetCOALevel3,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id + '&coa_level2_id=' + $coa_level2_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#coa_level2_id').after('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#coa_level3_id').select2('destroy');
                $('#coa_level3_id').html(json.html);
                $('#coa_level3_id').select2({width:'100%'});
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

function printExcel() {
    $('#form').attr('action', $UrlPrintExcel).submit();
}

function printPDF() {
    $('#form').attr('action', $UrlPrintPDF).submit();
}