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
                            &nbsp;Excel
                        </a>
                    </div>
                </div>
            </div>
        </section>
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
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><span class="required">*&nbsp;</span><?php echo $lang[from_date]; ?></label>
                                        <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate" autocomplete="off"/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><span class="required">*&nbsp;</span><?php echo $lang[to_date]; ?></label>
                                        <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate" autocomplete="off"/>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 hide">
                                    <div class="form-group">
                                        <label><?php echo $lang['partner_type']; ?></label>
                                        <select class="form-control" id="partner_type_id" name="partner_type_id">
                                            <option value="">&nbsp;</option>
                                            <?php foreach($partner_types as $partner_type): ?>
                                            <option value="<?php echo $partner_type['partner_type_id']; ?>" <?php echo ($partner_type_id == $partner_type['partner_type_id']?'selected="true"':''); ?>><?php echo $partner_type['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $lang['partner_name']; ?></label>
                                        <select class="form-control" id="partner_id" name="partner_id[]" multiple>
                                            <option value="">&nbsp;</option>
                                            <?php foreach($partners as $partner): ?>
                                            <option value="<?php echo '\''.$partner['customer_id'].'\''; ?>" ><?php echo $partner['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="partner_id" class="error" style="display: none;"></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label><?php echo $lang['product']; ?></label>
                                    <div style="display:flex">
                                        <select class="form-control product" name="product_id[]" id="product_id" multiple>
                                            <option value="">&nbsp;</option>
                                            <?php foreach($products as $product): ?>
                                            <option value="<?php echo '\''.$product['product_id'].'\''; ?>"><?php echo $product['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                         <span class="">
                                              <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="product_id" data-field="product_id">
                                                  <i class="fa fa-search"></i>
                                              </button>
                                         </span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 hide">
                                    <div class="form-group">
                                        <label><?php echo $lang['warehouse']; ?></label>
                                        <select class="form-control" id="warehouse_id" name="warehouse_id">
                                            <option value="">&nbsp;</option>
                                            <?php foreach($warehouses as $warehouse): ?>
                                            <option value="<?php echo $warehouse['warehouse_id']; ?>"><?php echo $warehouse['name']; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="warehouse_id" class="error" style="display: none;"></label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><?php echo $lang[group_by]; ?></label>
                                        <select class="form-control" name="group_by" id="group_by_id" >
                                            <option value="document"><?php echo $lang[text_document]; ?></option>
                                            <option value="partner"><?php echo $lang[text_partner]; ?></option>
                                            <!-- <option value="warehouse"><?php echo $lang[text_warehouse]; ?></option> -->
                                            <option value="product"><?php echo $lang[text_product]; ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 hide">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button id="btnFilter" class="btn btn-info form-control" type="button" onclick="getDetailReport();"><?php echo $lang['button_filter']; ?></button>
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
                                    <a class="btn btn-success" href="javascript:void(0);" onclick="printExcel();"><i class="fa fa-print"></i>&nbsp;Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
<link rel="stylesheet" href="../assets/plugins/dataTables/dataTables.bootstrap.css">
<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
<script type="text/javascript" src="../admin/view/js/report/sale_analysis_report.js"></script>
<script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
        jQuery('#form').validate(<?php echo $strValidation; ?>);
    </script>
<script type="text/javascript">
    var $UrlPrint = '<?php echo $href_print_report; ?>';
    var $UrlPrintExcel = '<?php echo $href_print_excel_report; ?>';
    var $UrlGetDetailReport = '<?php echo $href_get_detail_report; ?>';
    var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
    var $UrlGetPartnerJSON = '<?php echo $href_get_partner_json; ?>';
   $(document).ready(function() {
       $('#product_id').select2({
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
                       page: params.page
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
           minimumInputLength: 5,
           templateResult: formatRepo, // omitted for brevity, see the source of this page
           templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
       });
   });

    function formatRepo (repo) {
        if (repo.loading) return repo.text;

        var markup = "<div class='select2-result-repository clearfix'>";
        if(repo.image_url) {
            markup +="<div class='select2-result-repository__avatar'><img src='" + repo.image_url + "' /></div>";
        }
        markup +="<div class='select2-result-repository__meta'>";
        markup +="  <div class='select2-result-repository__title'>" + repo.name + "</div>";

        // if (repo.description) {
        //     markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
        // }

        // markup += "<div class='select2-result-repository__statistics'>" +
        //         "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
        //         "</div>" +
        //         "</div></div>";

        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.name || repo.text;
    }


    $(document).ready(function(){
        $('#partner_id').select2({
            width: '100%',
            ajax: {
                url: $UrlGetPartnerJSON,
                dataType: 'json',
                type: 'post',
                mimeType:"multipart/form-data",
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term, // search term
                        page: params.page
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
    })

    $dataTable = $('#tblReport').DataTable();
</script>
</body>
</html>