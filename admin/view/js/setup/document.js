/**
 * Created by Huzaifa on 9/18/15.
 */
var oTable;
$(document).on('click','#btnFilter', function() {
//    alert('this');
    var $data = {
        'document_from_date': $('#document_from_date').val(),
        'document_to_date': $('#document_to_date').val(),
        'post_from_date': $('#post_from_date').val(),
        'post_to_date': $('#post_to_date').val(),
        'partner_type_id': $('#partner_type_id').val(),
        'partner_id': $('#partner_id').val(),
        'document_type_id': $('#document_type_id').val()
    };
//    console.log($data);
    $.ajax({
        url: $UrlGetDocuments,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $("#tblDocuments").dataTable().fnDestroy();
            $("#tblDocuments tbody").html('')
            $('#btnFilter i').removeClass('fa-search').addClass('fa-refresh fa-spin');
        },
        complete: function() {
            $('#btnFilter i').removeClass('fa-refresh').removeClass('fa-spin').addClass('fa-search');
        },
        success: function(json) {
            if(json.success)
            {
                $('#tblDocuments tbody').html(json.html);
            }
            else {
                alert(json.error);
            }
            oTable = jQuery('#tblDocuments').dataTable( {
                "sPaginationType": "full_numbers",
                "iDisplayLength": 50
                ,"aoColumnDefs" : [ {
                    'bSortable' : false,
                    'aTargets' : [ 0 ]
                }, {
                    'bSearchable' : false,
                    'aTargets' : [ 0 ]
                } ]
                , "aaSorting": [[ 8, "desc" ]]
            });
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
});

function unPost($obj) {
    var $document_id = $($obj).data('document_id');
    $.ajax({
        url: $UrlUnpostDocument,
        dataType: 'json',
        type: 'post',
        data: 'document_id=' + $document_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $($obj).prepend('<i id="loader" class="fa fa-refresh fa-spin"></i>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#btnFilter').trigger('click');
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
