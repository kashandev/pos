/**
 * Created by Huzaifa on 9/18/15.
 */

/*
    Function EMpty
*/

function empty($val)
{
    return ($val==null||$val==''||$val=='undefined')?true:false;
}



$(document).on('click','.QSearchProduct', function() {
    $obj = this;
    var $element = $(this).data('element');
    var $field = $(this).data('field');
    if($(this).data('callback')) {
        var $callback = $(this).data('callback');
    } else {
        var $callback = '';
    }
    $.ajax({
        url: $URLQuickSearchProduct,
        dataType: 'json',
        data: 'element=' + $element + '&field='+ $field + '&callback='+ $callback,
        beforeSend: function() {
            $($obj).html('<i class="fa fa-refresh fa-spin"></i>');
            $('#_modal .modal-body').html('');
        },
        complete: function() {
            //$('#loader').remove();
            $($obj).html('<i class="fa fa-search"></i>');
        },
        success: function(json) {
            if(json.success) {
                $('#_modal .modal-title').html(json.title);
                $('#_modal .modal-body').html(json.html);
                $('#_modal').modal();

                oTable = $('#QSDataTable').dataTable( {
                    "bProcessing": true
                    ,"bServerSide": true
                    ,"bFilter": true
                    ,"bAutoWidth": true
                    ,"ajax": {
                        "url": $URLQuickSearchAjaxProduct,
                        "data": function ( d ) {
                            return $.extend( {}, d, {
                                "element": $element,
                                "field": $field,
                                "callback": $callback
                            } );
                        }
                    }
                    //,"sAjaxSource": $URLQuickSearchAjaxProduct
                    ,"aoColumnDefs" : [ {
                        'bSortable' : false,
                        'aTargets' : [ 0 ]
                    }, {
                        'bSearchable' : false,
                        'aTargets' : [ 0 ]
                    } ]
                    , "aaSorting": [[ 2, "asc" ]]
                });
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
})

function getDocumentLedger() {
    var $document_type_id = $('#document_type_id').val();
    var $document_id = $('#document_id').val();
    $.ajax({
        url: $UrlGetDocumentLedger,
        dataType: 'json',
        type: 'post',
        data: 'document_type_id=' + $document_type_id+'&document_id='+$document_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
        },
        complete: function() {
        },
        success: function(json) {
            if(json.success)
            {
                $('#_modal .modal-title').html(json.title);
                $('#_modal .modal-body').html(json.html);
                $('#_modal').modal();
            } else {
                alert(json.error);
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            console.log(xhr.responseText);
        }
    })
}