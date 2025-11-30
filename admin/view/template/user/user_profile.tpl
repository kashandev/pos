<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1><?php echo $lang['heading_title']; ?></h1>
            <div class="row">
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <?php foreach($breadcrumbs as $breadcrumb): ?>
                        <li>
                            <a href="<?php echo $breadcrumb['href']; ?>">
                                <i class="<?php echo $breadcrumb['class']; ?>"></i>
                                <?php echo $breadcrumb['text']; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <div class="col-sm-6">
                    <div class="pull-right">
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
                            <i class="fa fa-floppy-o"></i>
                            &nbsp;<?php echo $lang['save']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <?php if ($error_warning) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $error_warning; ?></div>
                            <?php } ?>
                            <?php  if ($success) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?></div>
                            <?php  } ?>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form action="<?php echo $action_save; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="hidden" name="company_id" value="<?php echo $this->user->getCompanyId(); ?>" />
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['user_name']; ?></label>
                                            <input type="text" name="user_name" value="<?php echo $user_name; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['email']; ?></label>
                                            <input type="text" name="email" value="<?php echo $email; ?>" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['login_name']; ?></label>
                                            <input type="text" name="login_name" value="<?php echo $login_name; ?>" class="form-control" readonly="true"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['password']; ?></label>
                                            <input type="password" id="password" name="login_password" value="" autocomplete="off" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['confirm']; ?></label>
                                            <input type="password" name="confirm" value="" autocomplete="off" class="form-control"/>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['status']; ?></label>
                                            <select id="status" name="status" class="form-control select2" style="width: 100%;">
                                                <option value="Inactive" <?php echo ($status == 'Inactive'?'selected="true"':'')?>><?php echo $lang['inactive']; ?></option>
                                                <option value="Active" <?php echo ($status == 'Active'?'selected="true"':'')?>><?php echo $lang['active']; ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <a href="javascript:void(0);" id="a_user_image"  data-toggle="image" class="img-thumbnail" data-src_image="src_user_image" data-src_input="file_user_image">
                                                <img alt="User profile picture" src="<?php echo $src_user_image; ?>"  id="src_user_image" alt="" title="" data-placeholder="<?php echo $no_image; ?>" class="profile-user-img img-responsive img-circle"/>
                                            </a>
                                            <input type="hidden" name="user_image" value="<?php echo $user_image; ?>" id="file_user_image" />
                                            <br />
                                            <a class="btn btn-primary btn-xs" onclick="jQuery('#src_user_image').attr('src', '<?php echo $no_image; ?>'); jQuery('#file_user_image').attr('value', '');"><?php echo $lang['clear']; ?></a>
                                            <br />&nbsp;
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['colour_theme']; ?></label>
                                            <select id="colour_theme" name="colour_theme" class="form-control select2" style="width: 100%;" onchange="setColourTheme();">
                                                <option value="skin-blue" <?php echo ($colour_theme == 'skin-blue'?'selected="true"':'')?>><?php echo $lang['skin_blue']; ?></option>
                                                <!--
                                                <option value="skin-blue-light" <?php echo ($colour_theme == 'skin-blue-light'?'selected="true"':'')?>><?php echo $lang['skin_blue_light']; ?></option>
                                                <option value="skin-black" <?php echo ($colour_theme == 'skin-black'?'selected="true"':'')?>><?php echo $lang['skin_black']; ?></option>
                                                <option value="skin-black-light" <?php echo ($colour_theme == 'skin-black-light'?'selected="true"':'')?>><?php echo $lang['skin_black_light']; ?></option>
                                                <option value="skin-green" <?php echo ($colour_theme == 'skin-green'?'selected="true"':'')?>><?php echo $lang['skin_green']; ?></option>
                                                <option value="skin-green-light" <?php echo ($colour_theme == 'skin-green-light'?'selected="true"':'')?>><?php echo $lang['skin_green_light']; ?></option>
                                                <option value="skin-purple" <?php echo ($colour_theme == 'skin-purple'?'selected="true"':'')?>><?php echo $lang['skin_purple']; ?></option>
                                                <option value="skin-purple-light" <?php echo ($colour_theme == 'skin-purple-light'?'selected="true"':'')?>><?php echo $lang['skin_purple_light']; ?></option>
                                                <option value="skin-red" <?php echo ($colour_theme == 'skin-red'?'selected="true"':'')?>><?php echo $lang['skin_red']; ?></option>
                                                <option value="skin-red-light" <?php echo ($colour_theme == 'skin-red-light'?'selected="true"':'')?>><?php echo $lang['skin_red_light']; ?></option>
                                                <option value="skin-yellow" <?php echo ($colour_theme == 'skin-yellow'?'selected="true"':'')?>><?php echo $lang['skin_yellow']; ?></option>
                                                <option value="skin-blue-yellow" <?php echo ($colour_theme == 'skin-yellow-light'?'selected="true"':'')?>><?php echo $lang['skin_yellow_light']; ?></option>
                                                -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<!-- Select2 -->
<link rel="stylesheet" href="plugins/select2/select2.min.css">
<script src="plugins/select2/select2.full.min.js"></script>
<script>
    $('document').ready(function () {
        //Initialize Select2 Elements
        $(".select2").select2();
    });
</script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>
<form enctype="multipart/form-data" id="form-upload" style="display: none;">
    <input id="image_file" type="file" name="image" value="" />
</form>
<script type="text/javascript"><!--
    var $image_form_data;
    $('.img-thumbnail').on('click', function(e) {
        e.preventDefault();
        //alert('I am clicked');
        $id = $(this).attr('id');
        $image_src = $(this).attr('data-src_image');
        $input_src = $(this).attr('data-src_input');
        //$('#form-upload').remove();
        //console.log($image_src, $input_src);

        //$('#form-upload #image_file').trigger('click');

        $('#form-upload #image_file').on('change', function() {
            $image_form_data = null;
            $image_form_data = new FormData($('#form-upload #image_file').parent()[0]);
            //console.log($image_form_data);

            $.ajax({
                url: '<?php echo HTTP_SERVER; ?>index.php?route=common/filemanager/upload&token=<?php echo $token; ?>&directory=<?php echo DIR_IMAGE; ?>&width=300&height=300',
                type: 'post',
                dataType: 'json',
                data: $image_form_data,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    jQuery('.wait').remove();
                    $('#'+$id).after('<span class="wait">&nbsp;<img src="dist/img/loading.gif" alt="" /></span>');
                },
                complete: function() {
                    jQuery('.wait').remove();
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

    function setColourTheme() {
        var theme_val = $('#colour_theme').val();
        $('body').removeClass (function (index, css) {
            return (css.match (/(^|\s)skin-\S+/g) || []).join(' ');
        });
        $('body').addClass(theme_val);
    }
    //--></script>
<?php echo $footer; ?>
</body>
</html>