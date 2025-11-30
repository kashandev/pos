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
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_from_date']; ?></label>
                                            <input type="text" class="form-control dtpDate" id="document_from_date" name="document_from_date" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_to_date']; ?></label>
                                            <input type="text" class="form-control dtpDate" id="document_to_date" name="document_to_date" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['post_from_date']; ?></label>
                                            <input type="text" class="form-control dtpDate" id="post_from_date" name="post_from_date" value="" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['post_to_date']; ?></label>
                                            <input type="text" class="form-control dtpDate" id="post_to_date" name="post_to_date" value="" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_type']; ?></label>
                                            <select class="form-control" id="partner_type_id" name="partner_type_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($partner_types as $partner_type): ?>
                                                <option value="<?php echo $partner_type['partner_type_id']; ?>" ><?php echo $partner_type['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['partner_name']; ?></label>
                                            <select class="form-control" id="partner_id" name="partner_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($partners as $partner): ?>
                                                <option value="<?php echo $partner['partner_id']; ?>" ><?php echo $partner['name'] . ' [' . $partner['partner_type'] . ']'; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_type']; ?></label>
                                            <select class="form-control" id="document_type_id" name="document_type_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($document_types as $document_type): ?>
                                                <option value="<?php echo $document_type['document_type_id']; ?>" ><?php echo $document_type['document_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" id="btnFilter" name="btnFilter" class="btn btn-primary form-control" >
                                                <i class="fa fa-search"></i>
                                                <?php echo $lang['filter']; ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <hr />
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive">
                                            <table id="tblDocuments" class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>&nbsp;</th>
                                                    <th><?php echo $lang['document_type']; ?></th>
                                                    <th><?php echo $lang['document_date']; ?></th>
                                                    <th><?php echo $lang['document_no']; ?></th>
                                                    <th><?php echo $lang['partner_type']; ?></th>
                                                    <th><?php echo $lang['partner_name']; ?></th>
                                                    <th><?php echo $lang['post_date']; ?></th>
                                                    <th><?php echo $lang['post_by']; ?></th>
                                                    <th><?php echo $lang['created_at']; ?></th>
                                                </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script type="text/javascript" src="../admin/view/js/setup/document.js"></script>
    <link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
    <script src="plugins/dataTables/jquery.dataTables.js"></script>
    <script src="plugins/dataTables/dataTables.bootstrap.js"></script>
    <script type="text/javascript">
        var $UrlGetDocuments = '<?php echo $href_get_documents; ?>';
        var $UrlUnpostDocument = '<?php echo $href_unpost_document; ?>';
        oTable = jQuery('#tblDocuments').dataTable( {
            "sPaginationType": "full_numbers"
            ,"aoColumnDefs" : [ {
                'bSortable' : false,
                'aTargets' : [ 0 ]
            }, {
                'bSearchable' : false,
                'aTargets' : [ 0 ]
            } ]
            , "aaSorting": [[ 8, "desc" ]]
        });
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>