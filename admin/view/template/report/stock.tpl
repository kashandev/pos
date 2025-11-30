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
                            &nbsp;<?php echo $lang['print_detail']; ?>
                        </a>
                        <a class="btn btn-primary" href="javascript:void(0);" onclick="printSummary();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print_summary']; ?>
                        </a>
                        <button class="btn btn-success" type="button" onclick="printExcelSummary();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print_summary_excel'] ?>
                        </button> 
                        <button class="btn btn-success" type="button" onclick="printExcel();">
                            <i class="fa fa-print"></i>
                            &nbsp;Print Excel
                        </button> 
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
                                    <div class="col-md-3 hide">
                                        <div class="form-group">
                                            <label><?php echo $lang['from_date']; ?></label>
                                            <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['to_date']; ?></label>
                                            <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['warehouse']; ?></label>
                                            <select class="form-control" name="warehouse_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                <option value="<?php echo $warehouse['warehouse_id']; ?>"><?php echo $warehouse['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['container_no']; ?></label>
                                            <input type="text" id="container_no" name="container_no" value="<?php echo $container_no; ?>" class="form-control"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['product_category']; ?></label>
                                            <select class="form-control" id="product_category_id" name="product_category_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($product_categories as $product_category): ?>
                                                <option value="<?php echo $product_category['product_category_id']; ?>"><?php echo $product_category['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label><?php echo $lang['product']; ?></label>
                                        <select class="form-control product" name="product_id" id="product_id">
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button class="btn btn-info form-control" type="button" onclick="getDetailReport();">Filter</button>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <div class="radio">
                                                <label>
                                                    <input name="report_type" id="report_type_warehouse" value="Warehouse" checked="" type="radio">
                                                    <?php echo $lang['warehouse']; ?>
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input name="report_type" id="report_type_container" value="Container" type="radio">
                                                    <?php echo $lang['container_no']; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table id="tblReport" class="table table-striped table-bordered">
                                            <thead class="th-color">
                                            <tr>
                                                <th class="center"><?php echo $lang['document_date']; ?></th>
                                                <th class="center"><?php echo $lang['document_no']; ?></th>
                                                <th class="center"><?php echo $lang['warehouse']; ?></th>
                                                <th class="center"><?php echo $lang['product_category']; ?></th>
                                                <th class="center"><?php echo $lang['product_code']; ?></th>
                                                <th class="center"><?php echo $lang['product']; ?></th>
                                                <th class="center"><?php echo $lang['unit']; ?></th>
                                                <th class="center"><?php echo $lang['qty']; ?></th>
                                                <th class="center"><?php echo $lang['rate']; ?></th>
                                                <th class="center"><?php echo $lang['amount']; ?></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <div class="pull-right">
                                    <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                                        <i class="fa fa-print"></i>
                                        &nbsp;<?php echo $lang['print_detail']; ?>
                                    </a>
                                    <a class="btn btn-primary" href="javascript:void(0);" onclick="printSummary();">
                                        <i class="fa fa-print"></i>
                                        &nbsp;<?php echo $lang['print_summary']; ?>
                                    </a>
                                      <button class="btn btn-success" type="button" onclick="printExcelSummary();">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print_summary_excel'] ?>
                        </button> 
                                     <button class="btn btn-success" type="button" onclick="printExcel();"><i class="fa fa-print"></i>&nbsp;Print Excel
                                    </button>
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
    <script type="text/javascript" src="../admin/view/js/report/stock_report.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
        jQuery('#form').validate(<?php echo $strValidation; ?>);`
    </script>
    <script type="text/javascript">
        var $UrlPrintExcel = '<?php echo $href_print_excel; ?>';
        var $UrlPrintExcelSummary = '<?php echo $href_print_excel_summary; ?>';
        var $UrlGetDetailReport = '<?php echo $href_get_detail_report; ?>';
        var $UrlGetSummaryReport = '<?php echo $href_get_summary_report; ?>';
        var $UrlPrintWarehouseDetail = '<?php echo $href_print_warehouse_detail; ?>';
        var $UrlPrintWarehouseSummary = '<?php echo $href_print_warehouse_summary; ?>';
        var $UrlPrintContainerDetail = '<?php echo $href_print_container_detail; ?>';
        var $UrlPrintContainerSummary = '<?php echo $href_print_container_summary; ?>';
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

        $(document).ready(function() {
            $('#partner_type_id').trigger('change');
            $('select.product').select2({
                allowClear: true,
                placeholder: "",
                width: '100%',
                ajax: {
                    url: $UrlGetProductJSON,
                    dataType: 'json',
                    type: 'post',
                    mimeType:"multipart/form-data",
                    delay: 250,
                    data: function (params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                            product_category_id: function() {
                                return $('#product_category_id').val();
                            }
                        };
                    },
                    processResults: function (data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        params.page = params.page || 1;

                        return {
                            results: data.items,
                            pagination: {
                                more: (params.page * 30) < data.total_count
                            }
                        };
                    },
                    cache: true
                },
                escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
                minimumInputLength: 2,
                templateResult: formatRepo, // omitted for brevity, see the source of this page
                templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
            });
        });

        function printExcel() {
            $('#form').attr('action', $UrlPrintExcel).submit();
        }
        function printExcelSummary() {
            $('#form').attr('action', $UrlPrintExcelSummary).submit();
        }

        $dataTable = $('#tblReport').DataTable();
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>