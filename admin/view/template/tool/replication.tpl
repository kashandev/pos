<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper" xmlns="http://www.w3.org/1999/html">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
        <?php echo $error_warning; ?></div>
    <?php } ?>
    <?php  if ($success) { ?>
    <div class="alert alert-success alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
        <?php echo $success; ?>
    </div>
    <?php  } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading heading">
                    <?php echo $lang['heading_title']; ?>
                    <ul style="float: right;" class="list-nostyle list-inline">
                        <li>
                            <a class="btn btn-outline btn-primary" onclick="$('#backup').submit();">
                                <i class="fa fa-save"></i>
                                <?php echo $lang['backup']; ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <form action="<?php echo $backup; ?>" method="post" enctype="multipart/form-data" id="backup">
                                <?php $row_no=0; ?>
                                <?php foreach($tables as $table): ?>
                                <?php if($row_no % 4 == 0): ?>
                                <div class="row">
                                    <?php endif; ?>
                                    <div class="col-md-3">
                                        <input type="checkbox" name="backup[]" value="<?php echo $table; ?>" checked="checked" />
                                        <?php echo $table; ?>
                                    </div>
                                    <?php $row_no++; ?>
                                    <?php if($row_no % 4 == 0 || $row_no == count($tables)): ?>
                                </div>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>