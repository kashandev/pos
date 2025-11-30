<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <base href="<?php echo $base; ?>"/>
    <title><?php echo CONFIG_APPLICATION_NAME ?> | <?php echo $lang['heading_title']; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="plugins/iCheck/square/blue.css">
    <!-- Modify CSS -->
    <link rel="stylesheet" href="dist/css/modify.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="javascript:void(0);"><b><?php echo $lang['heading_title']; ?></b></a>
    </div><!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg"><?php echo $lang['text_session']; ?></p>
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
            <fieldset>
                <div class="form-group">
                    <label><?php echo $entry_company; ?></label>
                    <select class="form-control" name="company_id" id="company_id">
                        <?php foreach($companys as $company):?>
                        <option value="<?php echo $company['company_id']; ?>"
                        <?php echo (($company['company_id'] = $company_id || count($companys)==1)? 'selected="selected"' : ''); ?>
                        ><?php echo $company['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo $entry_company_branch; ?></label>
                    <select class="form-control" name="company_branch_id" id="company_branch_id">
                        <?php foreach($company_branch_id as $companybranch):?>
                        <option value="<?php echo $companybranch['company_branch_id']; ?>"
                        <?php echo ($companybranch['company_branch_id'] = $company_branch_id ? 'selected="selected"' : ''); ?>
                        ><?php echo $companybranch['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label><?php echo $entry_fiscal_year; ?></label>
                    <select class="form-control" name="fiscal_year_id" id="fiscal_year_id">
                        <?php foreach($fiscal_years as $fiscal_year):?>
                        <option value="<?php echo $fiscal_year['fiscal_year_id']; ?>"
                        <?php echo ($fiscal_year['fiscal_year_id'] == $fiscal_year_id ? 'selected="selected"' : ''); ?>><?php echo $fiscal_year['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <a onclick="$('#form').submit();"
                   class="btn btn-primary btn-block btn-flat"><span><?php echo $button_submit; ?></span></a>
            </fieldset>
            <?php if ($redirect) { ?>
            <input type="hidden" name="redirect" value="<?php echo $redirect; ?>"/>
            <?php } ?>
        </form>
    </div><!-- /.login-box-body -->
</div><!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
<!-- Bootstrap 3.3.5 -->
<script src="bootstrap/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="plugins/iCheck/icheck.min.js"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
<script type="text/javascript"><!--
    $('#form input').keydown(function (e) {
        if (e.keyCode == 13) {
            $('#form').submit();
        }
    });
    //--></script>
<script type="text/javascript">
    $('#company_id').bind('change', function () {
        $.ajax({
            url: '<?php echo $href_get_branches; ?>',
            dataType: 'json',
            type: 'post',
            data: 'company_id=' + this.value,
            beforeSend: function () {
                $('#company_branch_id').before('<i id="loader" style="float: right; font-size: 24px;" class="fa fa-refresh fa-spin"></i>');
            },
            complete: function () {
                $('#loader').remove();
            },
            success: function (json) {
                if (json.success) {
                    var html = '';
                    if (json['company_branches'] != '') {
                        for (i = 0; i < json['company_branches'].length; i++) {
                            html += '<option value="' + json['company_branches'][i]['company_branch_id'] + '"';

                            if (json['company_branches'][i]['company_branch_id'] == '<?php echo $company_branch_id; ?>') {
                                html += ' selected="selected"';
                            }

                            html += '>' + json['company_branches'][i]['name'] + '</option>';
                        }
                    }
                }

                $('#company_branch_id').html(html).trigger('change');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
        $.ajax({
            url: '<?php echo $href_get_fiscal_year; ?>',
            dataType: 'json',
            type: 'post',
            data: 'company_id=' + this.value,
            beforeSend: function () {
                $('#fiscal_year_id').before('<i id="loader" style="float: right; font-size: 24px;" class="fa fa-refresh fa-spin"></i>');
            },
            complete: function () {
                $('#loader').remove();
            },
            success: function (json) {
                if (json.success) {
                    var html = '';
                    if (json['fiscal_years'] != '') {
                        for (i = 0; i < json['fiscal_years'].length; i++) {
                            html += '<option value="' + json['fiscal_years'][i]['fiscal_year_id'] + '"';

                            if (json['fiscal_years'][i]['fiscal_year_id'] == json['fiscal_year_id']) {
                                html += ' selected="selected"';
                            }

                            html += '>' + json['fiscal_years'][i]['name'] + '</option>';
                        }
                    }
                }


                $('#fiscal_year_id').html(html).trigger('change');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });

    $('#company_id').trigger('change');
</script>
</body>
</html>