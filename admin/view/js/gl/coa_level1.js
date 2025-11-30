/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#gl_type_id', function() {
    var $gl_type_id = $(this).val();
    $.ajax({
        url: $UrlGetLevelData,
        dataType: 'json',
        type: 'post',
        data: 'gl_type_id=' + $gl_type_id,
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
                if(json.level1_new_code) {
                    $('#level1_code').val(json.level1_new_code);
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
