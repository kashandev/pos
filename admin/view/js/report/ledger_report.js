/**
 * Created by Huzaifa on 9/18/15.
 */

$(document).on('change', '#coa_level1_id', function() {
    var $coa_level1_id = $(this).val();
    $.ajax({
        url: $UrlGetCOALevel2,
        dataType: 'json',
        type: 'post',
        data: 'coa_level1_id=' + $coa_level1_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#coa_level2_id').before('<span id="loader"><i class="fa fa-refresh fa-spin">&nbsp;</i></span>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#coa_level2_id').html(json.html).trigger('change');
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

$(document).on('change', '#coa_level2_id', function() {
    var $coa_level2_id = $(this).val();
    var $coa_level1_id = $('#coa_level1_id').val();
    $.ajax({
        url: $UrlGetCOALevel3,
        dataType: 'json',
        type: 'post',
        data: 'coa_level2_id=' + $coa_level2_id + '&coa_level1_id=' + $coa_level1_id,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#coa_level3_id').before('<span id="loader"><i class="fa fa-refresh fa-spin">&nbsp;</i></span>');
        },
        complete: function() {
            $('#loader').remove();
        },
        success: function(json) {
            if(json.success)
            {
                $('#coa_level3_id').html(json.html).trigger('change');
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

$(document).on('click', '#btnFilter', function() {
    var $data = {
        date_from: $('#date_from').val(),
        date_to: $('#date_to').val(),
        coa_level1_id: $('#coa_level1_id').val(),
        coa_level2_id: $('#coa_level2_id').val(),
        coa_level3_id: $('#coa_level3_id').val()
    }

    $.ajax({
        url: $UrlGetReport,
        dataType: 'json',
        type: 'post',
        data: $data,
        mimeType:"multipart/form-data",
        beforeSend: function() {
            $('#btnFilter i').removeClass('fa-filter').addClass('fa-refresh').addClass('fa-spin');
            $dataTable.destroy();
        },
        complete: function() {
            $('#btnFilter i').removeClass('fa-spin').removeClass('fa-refresh').addClass('fa-filter');
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
});


function printReport() {
    // if($('#date_to').val() == '' || $('#date_from').val() == '')
    // {
    //     alert('Please select date');
    // }
    // else
    // {
        // if($('#form').valid())
        // {
            $('#form').attr('action', $UrlPrintReport).submit();

        // }
        
    // }
    
}

function printExcel() {
    // if($('#date_to').val() == '' || $('#date_from').val() == '')
    // {ut
    //     alert('Please select date');
    // }
    // else
    // {
        $('#form').attr('action', $UrlPrintExcel).submit();
    // }
    
}
