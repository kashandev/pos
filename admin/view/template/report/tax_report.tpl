<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="skin-josh">
<?php echo $page_header; ?>
<div class="wrapper row-offcanvas row-offcanvas-left">
<?php echo $column_left; ?>
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/buttons.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/colReorder.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/dataTables.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/rowReorder.bootstrap.css">
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/buttons.bootstrap.css" />
<link rel="stylesheet" type="text/css" href="../assets/datatables/css/scroller.bootstrap.css" />
<!--end of page level css-->
<aside class="right-side">
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
            <!--
            <div class="col-sm-6">
                <div class="pull-right">
                    <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                        <i class="fa fa-print"></i>
                        &nbsp;<?php echo $lang['print']; ?>
                    </a>
                </div>
            </div> -->
        </div>
    </section>
    <?php if ($error_warning) { ?>
    <div class="col-sm-12" id="danger-alert">
        <div class="alert alert-danger alert-dismissable">
            <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
            <?php echo $error_warning; ?>
        </div>
    </div>
    <?php } ?>
    <?php  if ($success) { ?>
    <div class="col-sm-12" id="success-alert">
        <div class="alert alert-success alert-dismissable">
            <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
            <?php echo $success; ?>
        </div>
    </div>
    <?php  } ?>

    <!-- Main content -->
    <section class="content">
        <div class="row padding-left_right_15">
            <div class="col-xs-12">
                <form action="#" target="_blank" method="post" enctype="multipart/form-data" id="form">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel">
                                <div class="panel-body">
                                    <fieldset>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang[from_date]; ?></label>
                                                    <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate"/>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang[to_date]; ?></label>
                                                    <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang['partner_name']; ?></label>
                                                    <select class="form-control" id="partner_id" name="partner_id">
                                                        <option value="">&nbsp;</option>
                                                        <?php foreach($partners as $partner): ?>
                                                        <option value="<?php echo $partner['customer_id']; ?>" ><?php echo $partner['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <label for="partner_id" class="error" style="display: none;"></label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label><?php echo $lang[group_by]; ?></label>
                                                    <select class="form-control" name="group_by" id="group_by_id" >
                                                        <option value="partner"><?php echo $lang[text_partner]; ?></option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                        </div>
                                        <div class="col-md-12">
                                            <div class="box-footer">
                                                <div class="pull-right">
                                                    <div class="pull-right">
                                                        <a class="btn btn-primary" href="javascript:void(0);" onclick="printDetail();">
                                                            <i class="fa fa-print"></i>
                                                            &nbsp;<?php echo $lang['print']; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php echo $page_footer; ?>
</aside>
<a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button" title="Return to top" data-toggle="tooltip" data-placement="left">
    <i class="livicon" data-name="plane-up" data-size="18" data-loop="true" data-c="#fff" data-hc="white"></i>
</a>


<link rel="stylesheet" href="../assets/plugins/dataTables/dataTables.bootstrap.css">
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="../admin/view/js/report/sale_tax_invoice.js"></script>
<script type="text/javascript">
    var $UrlPrint = '<?php echo $href_print_report; ?>';
    var $UrlGetDetailReport = '<?php echo $href_get_detail_report; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    //    $(document).ready(function() {
    //        $('#product_id').select2({
    //            allowClear: true,
    //            placeholder: "",
    //            width: '100%',
    //            ajax: {
    //                url: $UrlGetProductJSON,
    //                dataType: 'json',
    //                type: 'post',
    //                mimeType:"multipart/form-data",
    //                delay: 250,
    //                data: function (params) {
    //                    return {
    //                        q: params.term, // search term
    //                        page: params.page
    //                    };
    //                },
    //                processResults: function (data, params) {
    //                    // parse the results into the format expected by Select2
    //                    // since we are using custom formatting functions we do not need to
    //                    // alter the remote JSON data, except to indicate that infinite
    //                    // scrolling can be used
    //                    params.page = params.page || 1;
    //
    //                    return {
    //                        results: data.items,
    //                        pagination: {
    //                            more: (params.page * 30) < data.total_count
    //                        }
    //                    };
    //                },
    //                cache: true
    //            },
    //            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
    //            minimumInputLength: 5,
    //            templateResult: formatRepo, // omitted for brevity, see the source of this page
    //            templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
    //        });
    //    });

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
<?php echo $footer; ?>
</body>
</html>