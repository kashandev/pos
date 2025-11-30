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
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['supplier_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="supplier_file" name="supplier_file" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['customer_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="customer_file" name="customer_file" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['warehouse_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="warehouse_file" name="warehouse_file" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['product_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="product_file" name="product_file" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['opening_stock_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="opening_stock_file" name="opening_stock_file" value="" />
                                        </div>
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['opening_account_file']; ?></label>
                                            <input type="file" accept=".csv, text/csv" class="form-control" id="opening_account_file" name="opening_account_file" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['truncate_opening_stock']; ?></label>
                                            <input type="checkbox" id="truncate_opening_stock" name="truncate_opening_stock" value="1" checked="true" />
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