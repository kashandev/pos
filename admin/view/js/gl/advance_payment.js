/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change','#partner_type_id', function() {
    $partner_type_id = $(this).val();
    $.ajax({
        url: $UrlGetPartner,
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
            if(json.success)
            {
                console.log(json);
                $('#partner_id').select2('destroy');
                $('#partner_id').html(json.html);
                $('#partner_id').select2({width:'100%'});
                $partners = json.partners;
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

function getAccount() {
    var check = $('#account_type:checked').val();
    // alert(check);
    if(check == 'CA')
    {
//        alert(check);
        $('#cheque_date').val('');
        $('#cheque_no').val('');
        $('#cheque_date').prop('disabled',true);
        $('#cheque_no').prop('disabled',true);

    }
    else{
        $('#cheque_date').prop('disabled',false);
        $('#cheque_no').prop('disabled',false);
    }
    $.ajax({

        url: $UrlGetBank,
        dataType: 'json',
        type: 'POST',
        data: 'bank_account=' + check,

        beforeSend: function() {
            $('#transaction_account_id').before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success) {
                $('#transaction_account_id').html(json.html).trigger('change');
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

function Save() {
    $('.btnsave').attr('disabled','disabled');
    if($('#form').valid() == true){
        $('#form').submit();
    }
    else{
        $('.btnsave').removeAttr('disabled');
    }
}