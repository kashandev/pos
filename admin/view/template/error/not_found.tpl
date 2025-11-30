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
        <section class="content-header">
            <h1><?php echo $lang['heading_title']; ?></h1>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php echo $lang['contents']; ?>
                </div>
            </div>
        </section>
    </div>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>