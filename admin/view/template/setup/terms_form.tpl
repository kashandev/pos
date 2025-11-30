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
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <a class="btn btn-primary btnclass" href="javascript:void(0);" onclick="$('#form').submit();">
                            <i class="fa fa-floppy-o"></i>
                            &nbsp;<?php echo $lang['save']; ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        <!-- Main content -->


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
        <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
        <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />

        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">

        <div class="row">
            <div class="col-md-10">
                <div class="form-group">
                    <label><span class="required">*</span><?php echo $lang['terms']; ?></label>
                    <input autocomplete="off" type="text" name="term" id="term" class="form-control" value="<?php echo $term ?>">
                </div>
            </div>
        </div>

        </form>
        </div><!-- /.box-body -->
        </div><!-- /.box -->
        </div><!-- /.col -->
        </div><!-- /.row -->
        </section><!-- /.content -->
        </div>
        <?php echo $page_footer; ?>
        <?php echo $column_right; ?>
        <!-- right-side -->
        </div>

        <script type="text/javascript" src="validate/jquery.validate.min.js"></script>
        <script src="jasny-bootstrap.js" type="text/javascript"></script>
        <script>
            jQuery('#form').validate(<?php echo $strValidation; ?>);
        </script>
        <script>
            $('.btnclass').on('click',function(){
                $term = $('term').val();
                console.log('in'+$term);
                if($term == ''){
                    console.log('if');
                    $('term_error').show();
                    $('.btnsave').attr('disabled','disabled');
            }
            });


        </script>
        <?php echo $footer; ?>
        </body>
        </html>