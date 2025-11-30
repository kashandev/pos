<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
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
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
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
                <div class="col-sm-12">
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
                            <form action="<?php echo $action_update; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_base_currency']; ?></label>
                                            <select class="form-control" id="base_currency_id" name="base_currency_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($currencies as $id => $caption): ?>
                                                <option value="<?php echo $id; ?>" <?php echo ($id == $base_currency_id ? 'selected="selected"' : ''); ?>><?php echo $caption; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="base_currency_id" style="display: none;" class="error"><?php echo $error['base_currency_account']?></label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_time_zone']; ?></label>
                                            <select class="form-control" id="time_zone" name="time_zone" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($time_zones as $zone): ?>
                                                <option value="<?php echo $zone; ?>" <?php echo ($time_zone == $zone?'selected="selected"':''); ?>><?php echo $zone; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="time_zone" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_suspense_account']; ?></label>
                                            <select class="form-control" id="suspense_account_id" name="suspense_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $suspense_account_id?'selected="selected"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="suspense_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_cash_account']; ?></label>
                                            <select class="form-control" id="cash_account_id" name="cash_account_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id'] == $cash_account_id?'selected="selected"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="cash_account_id" style="display: none;" class="error">&nbsp;</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['entry_company_logo']; ?></label><br />
                                            <a href="javascript:void(0);" id="a_company_image"  data-toggle="image" class="img-thumbnail" data-src_image="src_company_image" data-src_input="file_company_image" data-width="300" data-height="100">
                                                <img alt="Company Logo" src="<?php echo $src_company_image; ?>"  id="src_company_image" alt="" title="" data-placeholder="<?php echo $no_image; ?>" class="img-responsive"/>
                                            </a>
                                            <input type="hidden" name="company_logo" value="<?php echo $company_logo; ?>" id="file_company_image" />
                                            <br />
                                            <a class="btn btn-primary btn-xs" onclick="jQuery('#src_company_image').attr('src', '<?php echo $no_image; ?>'); jQuery('#file_company_image').attr('value', '');"><?php echo $lang['clear']; ?></a>
                                            <br />&nbsp;
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label><?php echo $lang['description']; ?></label>
                                            <textarea  class="form-control ckeditor" type="text" id="description" name="description" ><?php echo $description ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="company_header_print"><?= $lang['company_header_print'] ?></label>
                                            <a href="javascript:void(0);" id="a_company_header_print"  data-toggle="image" class="img-thumbnail d-block" data-src_image="src_company_header_print" data-src_input="file_company_header_print" data-width="1000" data-height="200">
                                                <img alt="Product Image" src="<?php echo $src_company_header_print; ?>"  id="src_company_header_print" alt="" title="" data-placeholder="<?php echo $no_image; ?>" class="img-responsive" style="width:100%;object-fit:contain"/>
                                            </a>
                                            <input type="hidden" name="company_header_print" value="<?php echo $company_header_print; ?>" id="file_company_header_print" />
                                            <br />
                                            <a class="btn btn-primary btn-xs" onclick="jQuery('#src_company_header_print').attr('src', '<?php echo $no_image; ?>'); jQuery('#file_company_header_print').attr('value', '');"><?php echo $lang['clear']; ?></a>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label for="company_footer_print"><?= $lang['company_footer_print'] ?></label>
                                            <a href="javascript:void(0);" id="a_company_footer_print"  data-toggle="image" class="img-thumbnail d-block" data-src_image="src_company_footer_print" data-src_input="file_company_footer_print" data-width="1000" data-height="200">
                                                <img alt="Product Image" src="<?php echo $src_company_footer_print; ?>"  id="src_company_footer_print" alt="" title="" data-placeholder="<?php echo $no_image; ?>" class="img-responsive" style="width:100%;object-fit:contain"/>
                                            </a>
                                            <input type="hidden" name="company_footer_print" value="<?php echo $company_footer_print; ?>" id="file_company_footer_print" />
                                            <br />
                                            <a class="btn btn-primary btn-xs" onclick="jQuery('#src_company_footer_print').attr('src', '<?php echo $no_image; ?>'); jQuery('#file_company_footer_print').attr('value', '');"><?php echo $lang['clear']; ?></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="$('#form').submit();">
                                    <i class="fa fa-floppy-o"></i>
                                    &nbsp;<?php echo $lang['save']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);

        function getURLVar(key) {
            var value = [];

            var query = String(document.location).split('?');

            if (query[1]) {
                var part = query[1].split('&');

                for (i = 0; i < part.length; i++) {
                    var data = part[i].split('=');

                    if (data[0] && data[1]) {
                        value[data[0]] = data[1];
                    }
                }

                if (value[key]) {
                    return value[key];
                } else {
                    return '';
                }
            }
        }

    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>