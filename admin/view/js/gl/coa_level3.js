/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#coa_level1_id', function() {
    var $coa_level1_id = $(this).val();
    $.ajax({
        url: $UrlGetCOALevel2,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id + '&coa_level2_id=' + $coa_level2_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#coa_level2_id').html('');
            $('#coa_level2_id').before('<i id="loader" style="float: right; font-size: 24px" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#coa_level2_id').select('destroy');
                $('#coa_level2_id').html(json.html);
                $('#coa_level2_id').select({width: '100%'}).trigger('change');
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
    var $coa_level2_id = $(this).val();
    var $coa_level1_id = $('#coa_level1_id').val();
    $.ajax({
        url: $UrlGetLevelData,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id + '&coa_level2_id=' + $coa_level2_id,
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
                if(json.level3_new_code) {
                    $('#level3_code').val(json.level3_new_code);
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
