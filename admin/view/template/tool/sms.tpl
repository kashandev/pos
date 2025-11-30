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
            </div>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-solid">
                        <div class="box-header">
                            <?php if ($error_warning) { ?>
                            <div class="alert alert-danger alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $error_warning; ?>
                            </div>
                            <?php } ?>
                            <?php  if ($success) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?>
                            </div>
                            <?php  } ?>
                            <form method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="radio">
                                                <label>
                                                    <input name="member_type" id="member_type_all" value="ALL" type="radio" checked>
                                                    <?php echo $lang['all']; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="radio">
                                                <label>
                                                    <input name="member_type" id="member_type_academy" value="Academy" type="radio">
                                                    <?php echo $lang['academy_members']; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 hide" id="div_sport">
                                        <div class="form-group">
                                            <label><?php echo $lang['sports']; ?></label>
                                            <select class="form-control" id="sport_id" name="sport_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($sports as $sport): ?>
                                                <option value="<?php echo $sport['sport_id']; ?>"><?php echo $sport['sport_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="radio">
                                                <label>
                                                    <input name="member_type" id="member_type_ground" value="Ground" type="radio">
                                                    <?php echo $lang['ground_members']; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4 hide" id="div_ground_area">
                                        <div class="form-group">
                                            <label><?php echo $lang['ground_areas']; ?></label>
                                            <select class="form-control" id="ground_area_id" name="ground_area_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($ground_areas as $area): ?>
                                                <option value="<?php echo $area['ground_area_id']; ?>"><?php echo $area['ground_area']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="row">
                                <div class="col-sm-offset-10 col-sm-2">
                                    <div class="form-group">
                                        <button type="button" class="form-control btn btn-primary" id="btnFilter" name="btnFilter"><?php echo $lang['filter']; ?></button>
                                    </div>
                                </div>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $lang['message']; ?></label>
                                        <span class="help-block pull-right" id="total_characters"></span>
                                        <textarea class="form-control" id="message" name="message" maxlength="100"></textarea>
                                    </div>
                                </div>
                                <div class="col-sm-offset-4 col-sm-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="form-control btn btn-info" id="sendSMS">Send SMS</button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table id="tblMembers" class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sr.</th>
                                                <th>Reg. No.</th>
                                                <th>Member Name</th>
                                                <th>Mobile No.</th>
                                                <th>Status.</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript" src="dist/js/pages/tool/sms.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        var $UrlGetMembers = '<?php echo $href_get_members; ?>';
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>