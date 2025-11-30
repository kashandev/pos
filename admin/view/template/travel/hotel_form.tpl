<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
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
                                <?php echo $error_warning; ?>
                            </div>
                            <?php } ?>
                            <?php  if ($success) { ?>
                            <div class="alert alert-success alert-dismissable">
                                <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
                                <?php echo $success; ?>
                            </div>
                            <?php  } ?>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['destination_name']; ?></label>
                                            <select id="destination_id" name="destination_id" class="form-control">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($destinations as $destination): ?>
                                                <option value="<?php echo $destination['destination_id']; ?>" <?php echo ($destination['destination_id'] == $destination_id?'selected="true"':'');?>><?php echo $destination['destination_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="destination_id" class="error" style="display: none;">&nbsp;</label>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['hotel_name']; ?></label>
                                            <input class="form-control" type="text" name="hotel_name" value="<?php echo $hotel_name; ?>" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['hotel_description']; ?></label>
                                            <textarea class="form-control" id="hotel_description" name="hotel_description" rows="5"><?php echo $hotel_description; ?></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['meal_available']; ?></label>
                                            <select class="form-control" id="meal_available" name="meal_available">
                                                <option value="Yes" <?php echo ($meal_available=='Yes'?'selected="true"':''); ?>><?php echo $lang['yes']; ?></option>
                                                <option value="No" <?php echo ($meal_available=='No'?'selected="true"':''); ?>><?php echo $lang['no']; ?></option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['avg_meal_charges']; ?></label>
                                            <input class="form-control fDecimal text-right" type="text" id="avg_meal_charges" name="avg_meal_charges" value="<?php echo $avg_meal_charges; ?>" <?php echo ($meal_available=='No'?'readonly="true"':''); ?>/>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="tblHotelRoom">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td><?php echo $lang['room_type']; ?></td>
                                                    <td><?php echo $lang['room_charges']; ?></td>
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row=0; ?>
                                                <?php foreach($hotel_rooms as $room): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(<?php echo $grid_row; ?>);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                    <td>
                                                        <select class="form-control" id="hotel_room_<?php echo $grid_row; ?>_room_type_id" name="hotel_rooms[<?php echo $grid_row; ?>][room_type_id]">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($room_types as $room_type): ?>
                                                            <option value="<?php echo $room_type['room_type_id'];?>" <?php echo ($room_type['room_type_id']==$room['room_type_id']?'selected="true"':'');?>><?php echo $room_type['room_type']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control fDecimal text-right" id="hotel_room_<?php echo $grid_row; ?>_room_charges" name="hotel_rooms[<?php echo $grid_row; ?>][room_charges]" value="<?php echo $room['room_charges']; ?>" /></td>
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(<?php echo $grid_row; ?>);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $grid_row++; ?>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <input type="hidden" id="total_rooms" name="total_rooms" value="<?php echo $grid_row; ?>" />
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
        var $grid_row = <?php echo $grid_row; ?>;
        var $room_types = <?php echo json_encode($room_types); ?>;
        var $UrlSendRegistrationCode = '<?php echo $href_send_registration_code; ?>';
    </script>
    <script type="text/javascript" src="../admin/view/js/travel/hotel.js"></script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>