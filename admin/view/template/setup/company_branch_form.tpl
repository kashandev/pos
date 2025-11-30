<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">
<div class="wrapper">
    <?php echo $page_header; ?>
    <?php echo $column_left; ?>
    <div class="content-wrapper">
        <?php if ($error_warning) { ?>
        <div class="warning"><?php echo $error_warning; ?></div>
        <?php } ?>
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
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab-general" data-toggle="tab"><?php echo $lang['tab_general']; ?></a>
                                </li>
                                <li>
                                    <a href="#tab-document-prefix" data-toggle="tab"><?php echo $lang['tab_document_prefix']; ?></a>
                                </li>
                            </ul>
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="tab-general">
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['company']; ?></label>
                                                        <select class="form-control" id="company_id" name="company_id">
                                                            <?php foreach($companies as $company): ?>
                                                            <option value="<?php echo $company['company_id']; ?>" <?php echo ($company['company_id'] == $company_id ? 'selected="selected"': ''); ?>><?php echo $company['name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_code']; ?></label>
                                                        <input type="text" id="branch_code" name="branch_code" value="<?php echo $branch_code; ?>" class="form-control"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_name']; ?></label>
                                                        <input type="text" id="name" name="name" value="<?php echo $name; ?>" class="form-control"/>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><span class="required">*</span>&nbsp;<?php echo $lang['branch_account']; ?></label>
                                                        <select class="form-control" id="branch_account_id" name="branch_account_id" >
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($coas as $coa): ?>
                                                            <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$branch_account_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <label for="branch_account_id" style="display: none;" class="error">&nbsp;</label>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><span class="required">&nbsp;&nbsp;</span>&nbsp;<?php echo $lang['address']; ?></label>
                                                        <textarea class="form-control" id="address" name="address"><?php echo $address; ?></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label><span class="required">&nbsp;&nbsp;</span>&nbsp;<?php echo $lang['phone_no']; ?></label>
                                                        <input class="form-control fPhone" type="text" name="phone_no" value="<?php echo $phone_no; ?>"/>
                                                    </div>
                                                </div>
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
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab-document-prefix">
                                        <div class="table-responsive">
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <table class="table table-striped table-bordered table-hover">
                                                            <thead>
                                                            <tr>
                                                                <td><?php echo $lang['document']; ?></td>
                                                                <td><?php echo $lang['prefix_code']; ?></td>
                                                                <td><?php echo $lang['zero_prefix_digit']; ?></td>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <?php foreach($document_types as $row => $document_type): ?>
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][document_type_id]" value="<?php echo $document_type['document_type_id']; ?>"/>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][document_name]" value="<?php echo $document_type['document_name']; ?>"/>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][reset_on_fiscal_year]" value="<?php echo $document_type['reset_on_fiscal_year']; ?>"/>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][table_name]" value="<?php echo $document_type['table_name']; ?>"/>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][route]" value="<?php echo $document_type['route']; ?>"/>
                                                                    <input type="hidden" name="company_branch_document_prefixes[<?php echo $row; ?>][primary_key]" value="<?php echo $document_type['primary_key']; ?>"/>
                                                                    <?php echo $document_type['document_name']; ?>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="company_branch_document_prefixes[<?php echo $row; ?>][document_prefix]" value="<?php echo $document_type['document_prefix']; ?>"/>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="company_branch_document_prefixes[<?php echo $row; ?>][zero_padding]" value="<?php echo $document_type['zero_padding']; ?>" class="fInteger"/>
                                                                </td>
                                                            </tr>
                                                            <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
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
        jQuery('#form').validate(<?php echo $strValidation;  ?>);

        $('#company_id').change(function() {
            var branch_name = $('#name').val();
            if(branch_name != '') {
                $('#name').removeData("previousValue");
                $('#form').validate().element("#name");
            }
        });
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>