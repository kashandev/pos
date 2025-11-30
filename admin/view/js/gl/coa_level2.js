/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#coa_level1_id', function() {
    var $coa_level1_id = $(this).val();
    $.ajax({
        url: $UrlGetLevelData,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#tblList tbody').html('');
            $('#tblList').before('<i id="loader" style="float: right; font-size: 24px" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#tblList tbody').html(json.html);
                if(json.level2_new_code) {
                    $('#level2_code').val(json.level2_new_code);
                }
            }
            else {
                console.log(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
});
