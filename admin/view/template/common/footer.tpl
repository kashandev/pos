<form enctype="multipart/form-data" id="form-upload" style="display: none;">
    <input id="image_file" type="file" name="image" value="" />
    <input id="image_width" type="text" name="width" value="300" />
    <input id="image_height" type="text" name="height" value="300" />
</form>
<script type="text/javascript"><!--
    var $image_form_data;
    var $URLUploadImage = '<?php echo $href_upload_image; ?>';
    $('.img-thumbnail').on('click', function() {
        $id = $(this).attr('id');
        $image_src = $(this).attr('data-src_image');
        $input_src = $(this).attr('data-src_input');
        $image_width = $(this).attr('data-width');
        $image_height = $(this).attr('data-height');
        console.log($image_src, $input_src, $image_width, $image_height);
        if($image_width != '') {
            $('#form-upload #image_width').val($image_width);
        }
        if($image_height != '') {
            $('#form-upload #image_height').val($image_height);
        }
        //$('#form-upload').remove();

        $('#form-upload #image_file').trigger('click');

        $('#form-upload #image_file').on('change', function() {
            $image_form_data = null;
            $image_form_data = new FormData($('#form-upload #image_file').parent()[0]);
            $.ajax({
                url: $URLUploadImage,
                type: 'post',
                dataType: 'json',
                data: $image_form_data,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    jQuery('.wait').remove();
                    //$('#'+$id).after('<span class="wait">&nbsp;<img src="dist/loading.gif" alt="" /></span>');
                    $('#'+$id).before('<i id="loader" class="fa fa-refresh fa-spin"></i>');
                },
                complete: function() {
                    //jQuery('.wait').remove();
                    $('#loader').remove();
                },
                success: function(json) {
                    if (json['error']) {
                        alert(json['error']);
                    }

                    if (json['success']) {
                        $('#'+$image_src).attr('src',json['image_thumb']);
                        $('#'+$input_src).val(json['image']);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.responseText);
                }
            });
        });
    });
    //--></script>
<!-- Additional Changes to the theme by Huzaifa. -->
<link rel="stylesheet" href="dist/css/Addition.css">
<script type="text/javascript">
    $(document).ready(function() {
        var theme_val = '<?php echo $user_theme; ?>';
        $('body').removeClass (function (index, css) {
            return (css.match (/(^|\s)skin-\S+/g) || []).join(' ');
        });
        $('body').addClass(theme_val);
    })

    var $URLQuickSearchProduct = '<?php echo $href_quick_search_product; ?>';
    var $URLQuickSearchAjaxProduct = '<?php echo $href_quick_search_ajax_product; ?>';

</script>
<script type="text/javascript" src="dist/js/Addition.js"></script>
<div class="modal fade" id="_modal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">&nbsp;</h4>
            </div>
            <div class="modal-body">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function(){
        $('input,textarea').attr('autocomplete','off');
        $('.btnAddGrid, #btnAddGrid, #addRefDocument').click(function(){
            setTimeout(function(){
                $('table tr td input').attr('autocomplete','off');
            },1);
        });
    });
</script>