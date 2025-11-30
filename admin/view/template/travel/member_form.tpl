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
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">*</span>&nbsp;<?php echo $lang['member_name']; ?></label>
                                                    <input class="form-control" type="text" name="name" value="<?php echo $name; ?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['phone_no']; ?></label>
                                                    <input class="form-control" type="text" name="phone" value="<?php echo $phone; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['mobile_no']; ?></label>
                                                    <input class="form-control" type="text" name="mobile" value="<?php echo $mobile; ?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['email']; ?></label>
                                                    <input class="form-control" type="text" name="email" value="<?php echo $email; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['dob']; ?></label>
                                                    <input onchange="calculateAge(this, 'age');" class="form-control dtpDate" type="text" name="dob" id="dob" value="<?php echo $dob; ?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['age']; ?></label>
                                                    <input class="form-control" type="text" name="age" id="age" value="<?php echo $age; ?>" readonly />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['cnic_no']; ?></label>
                                                    <input class="form-control" type="text" name="cnic_no" id="cnic_no" value="<?php echo $cnic_no; ?>" />
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['passport_no']; ?></label>
                                                    <input class="form-control" type="text" name="passport_no" id="passport_no" value="<?php echo $passport_no; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['receivable_account']; ?></label>
                                                    <select class="form-control" id="outstanding_account_id" name="outstanding_account_id">
                                                        <?php if(count($outstanding_accounts)>1): ?>
                                                        <option value="">&nbsp;</option>
                                                        <?php endif; ?>
                                                        <?php foreach($outstanding_accounts as $coa): ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$outstanding_account_id?'selected="true"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><span class="required">&nbsp;</span>&nbsp;<?php echo $lang['advance_account']; ?></label>
                                                    <select class="form-control" id="advance_account_id" name="advance_account_id">
                                                        <?php if(count($advance_accounts)>1): ?>
                                                        <option value="">&nbsp;</option>
                                                        <?php endif; ?>
                                                        <?php foreach($advance_accounts as $coa): ?>
                                                        <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$advance_account_id?'selected="true"':''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="table-responsive">
                                            <label><?php echo $lang['family_member']; ?></label>
                                            <table class="table table-bordered" id="tblFamilyMember">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td><?php echo $lang['member_name']; ?></td>
                                                    <td><?php echo $lang['mobile_no']; ?></td>
                                                    <td><?php echo $lang['dob']; ?></td>
                                                    <td><?php echo $lang['age']; ?></td>
                                                    <td><?php echo $lang['cnic_no']; ?></td>
                                                    <td><?php echo $lang['passport_no']; ?></td>
                                                    <td style="width: 11%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row=0; ?>
                                                <?php foreach($family_members as $member): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(<?php echo $grid_row; ?>);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                        <input type="hidden" class="form-control" id="family_member_<?php echo $grid_row; ?>_member_family_id" name="family_members[<?php echo $grid_row; ?>][member_family_id]" value="<?php echo $member['member_family_id']; ?>" />
                                                    </td>
                                                    <td><input type="text" class="form-control" id="family_member_<?php echo $grid_row; ?>_member_name" name="family_members[<?php echo $grid_row; ?>][member_name]" value="<?php echo $member['member_name']; ?>" /></td>
                                                    <td><input type="text" class="form-control fDecimal" id="family_member_<?php echo $grid_row; ?>_mobile_no" name="family_members[<?php echo $grid_row; ?>][mobile_no]" value="<?php echo $member['mobile_no']; ?>" /></td>
                                                    <td><input onchange="calculateAge(this,'family_member_<?php echo $grid_row; ?>_age');" type="text" class="form-control dtpDate" id="family_member_<?php echo $grid_row; ?>_dob" name="family_members[<?php echo $grid_row; ?>][dob]" value="<?php echo $member['dob']; ?>" /></td>
                                                    <td><input type="text" class="form-control" id="family_member_<?php echo $grid_row; ?>_age" name="family_members[<?php echo $grid_row; ?>][age]" value="<?php echo $member['age']; ?>" readonly /></td>
                                                    <td><input type="text" class="form-control" id="family_member_<?php echo $grid_row; ?>_cnic_no" name="family_members[<?php echo $grid_row; ?>][cnic_no]" value="<?php echo $member['cnic_no']; ?>" /></td>
                                                    <td><input type="text" class="form-control" id="family_member_<?php echo $grid_row; ?>_passport_no" name="family_members[<?php echo $grid_row; ?>][passport_no]" value="<?php echo $member['passport_no']; ?>" /></td>
                                                    <td>
                                                        <a href="javascript:void(0);" class="btn btn-xs btn-danger" title="Remove" onclick="removeRow(<?php echo $grid_row; ?>);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $grid_row++; ?>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                            <input type="hidden" id="total_members" name="total_members" value="<?php echo $total_members; ?>" />
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
    </script>
    <script type="text/javascript" src="../admin/view/js/travel/member.js"></script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>