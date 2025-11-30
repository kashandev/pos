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
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                        <i class="fa fa-print"></i>
                        &nbsp;<?php echo $lang['print']; ?>
                    </a>
                    <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();">
                        <i class="fa fa-print"></i>
                        &nbsp;<?php echo $lang['excel']; ?>
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
                    <div class="box-header box-default">
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
                        <form action="#" target="_blank" method="post" enctype="multipart/form-data" id="form">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $lang['product_category']; ?></label>
                                        <select class="form-control" id="product_category_id" name="product_category_id">
                                            <option value="">&nbsp;</option>
                                            <?php foreach($product_categorys as $product_category): ?>
                                            <option value="<?php echo $product_category['product_category_id']; ?>" ><?php echo $product_category['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="product_category_id" class="error" style="display: none;"></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $lang['product']; ?></label>
                                    <div class="form-group input-group">
                                        <select class="form-control product" name="product_id" id="product_id">
                                            <option value="">&nbsp;</option>
                                            <?php foreach($products as $product): ?>
                                            <option value="<?php echo $product['product_id']; ?>"><?php echo $product['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                             <span class="input-group-btn">
                                                  <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="product_id" data-field="product_id">
                                                      <i class="fa fa-search"></i>
                                                  </button>
                                             </span>
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo $lang[print_format]; ?></label>
                                        <select class="form-control" name="print_format" id="print_format_id" >
                                            <option value="all"><?php echo $lang[all]; ?></option>
                                            <option value="sale_price"><?php echo $lang[sale_price]; ?></option>
                                            <option value="wholesale_price"><?php echo $lang[wholesale_price]; ?></option>
                                            <option value="minimum_price"><?php echo $lang[minimum_price]; ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="box-footer">
                        <div class="pull-right">
                            <div class="pull-right">
                                <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a>
                                <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();"><i class="fa fa-print"></i>&nbsp;<?php echo $lang['excel']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<link rel="stylesheet" href="plugins/dataTables/dataTables.bootstrap.css">
<script src="plugins/dataTables/jquery.dataTables.js"></script>
<script src="plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="../admin/view/js/report/product_report.js"></script>
<script type="text/javascript">
    var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';
    var $UrlPrint = '<?php echo $href_print_report; ?>';
    var $UrlGetDetailReport = '<?php echo $href_get_detail_report; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';


    function formatRepo (repo) {
        if (repo.loading) return repo.text;

        var markup = "<div class='select2-result-repository clearfix'>";
        if(repo.image_url) {
            markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
        }
        markup +="<div class='select2-result-repository__meta'>";
        markup +="  <div class='select2-result-repository__title'>" + repo.name + "</div>";

        if (repo.description) {
            markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
        }

        markup += "<div class='select2-result-repository__statistics'>" +
                "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
                "</div>" +
                "</div></div>";

        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.name || repo.text;
    }

    $dataTable = $('#tblReport').DataTable();
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>