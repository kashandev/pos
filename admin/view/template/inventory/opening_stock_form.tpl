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
                        <?php if(isset($isEdit) && $isEdit==1): ?>
                        <?php if($is_post == 0): ?>
                        <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                            <i class="fa fa-thumbs-up"></i>
                            &nbsp;<?php echo $lang['post']; ?>
                        </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button>
                        <?php endif; ?>
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                        <i class="fa fa-floppy-o"></i>
                        &nbsp;<?php echo $lang['save']; ?>
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
                        <div class="box-header">
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
                        </div><!-- /.box-header -->
                        <div class="box-body">
                            <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                                <input type="hidden" value="<?php echo $opening_stock_id; ?>" name="document_id" id="document_id" />
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input class="form-control" type="text" name="document_identity" readonly="readonly" value="<?php echo $document_identity; ?>" placeholder="Auto" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label><span class="required">*</span>&nbsp;<?php echo $lang['warehouse']; ?></label>
                                            <select class="form-control" id="warehouse_id" name="warehouse_id">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($warehouses as $warehouse): ?>
                                                <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($warehouse_id == $warehouse['warehouse_id']?'selected="true"':'') ?>><?php echo $warehouse['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <label for="warehouse_id" class="error"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input type="text" class="form-control" name="remarks" value="<?php echo $remarks; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row hide">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['base_currency']; ?></label>
                                            <input type="hidden" id="base_currency_id" name="base_currency_id"  value="<?php echo $base_currency_id; ?>" />
                                            <input type="text" class="form-control" id="base_currency" name="base_currency" readonly="true" value="<?php echo $base_currency; ?>" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_currency']; ?></label>
                                            <select class="form-control" id="document_currency_id" name="document_currency_id" onchange="getCurrency();">
                                                <option value="">&nbsp;</option>
                                                <?php foreach($currencys as $currency): ?>
                                                <option value="<?php echo $currency['currency_id']; ?>" <?php echo ($document_currency_id == $currency['currency_id']?'selected="selected"':''); ?>><?php echo $currency['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['conversion_rate']; ?></label>
                                            <input class="form-control fDecimal" id="conversion_rate" type="text" name="conversion_rate" value="<?php echo $conversion_rate; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive form-group">
                                            <table id="tblOpeningStockDetail" class="table table-bordered">
                                                <thead>
                                                <tr align="center" data-row_id="H">
                                                    <td style="width: 7%;"><a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a></td>
                                                    <td><?php echo $lang['code']; ?></td>
                                                    <td style="width: 300px;"><?php echo $lang['product']; ?></td>
                                                    <td><?php echo $lang['quantity']; ?></td>
                                                    <td><?php echo $lang['unit']; ?></td>
                                                    <td><?php echo $lang['rate']; ?></td>
                                                    <td><?php echo $lang['amount']; ?></td>
                                                    <td style="width: 7%;">
                                                        <a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $grid_row = 0; ?>
                                                <?php foreach($opening_stock_details as $detail): ?>
                                                <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                    <td>
                                                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                    <td>
                                                        <input onchange="getProductByCode(this);" type="text" style="min-width: 100px;" class="form-control" name="opening_stock_details[<?php echo $grid_row; ?>][product_code]" id="opening_stock_detail_product_code_<?php echo $grid_row; ?>" value="<?php echo $detail['product_code']; ?>" />
                                                    </td>
                                                    <td>
                                                        <div class="input-group">
                                                            <select onchange="getProductById(this);" class="form-control select2 product" id="opening_stock_detail_product_id_<?php echo $grid_row; ?>" name="opening_stock_details[<?php echo $grid_row; ?>][product_id]" >
                                                                <option value="<?php echo $detail['product_id']; ?>" selected="selected"><?php echo $detail['product']; ?></option>
                                                            </select>
                                                            <span class="input-group-btn ">
                                                                <button class="btn btn-default btn-flat QSearchProduct" id="QSearchProduct" type="button" data-element="opening_stock_detail_product_id_<?php echo $grid_row; ?>" data-field="product_id">
                                                                    <i class="fa fa-search"></i>
                                                                </button>
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="opening_stock_details[<?php echo $grid_row; ?>][qty]" id="opening_stock_detail_qty_<?php echo $grid_row; ?>" value="<?php echo $detail['qty']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="hidden" class="form-control" name="opening_stock_details[<?php echo $grid_row; ?>][unit_id]" id="opening_stock_detail_unit_id_<?php echo $grid_row; ?>" value="<?php echo $detail['unit_id']; ?>" readonly />
                                                        <input type="text" class="form-control" name="opening_stock_details[<?php echo $grid_row; ?>][unit]" id="opening_stock_detail_unit_<?php echo $grid_row; ?>" value="<?php echo $detail['unit']; ?>" readonly />
                                                    </td>
                                                    <td>
                                                        <input onchange="calculateAmount(this);" style="min-width: 100px;" type="text" class="form-control fPDecimal" name="opening_stock_details[<?php echo $grid_row; ?>][rate]" id="opening_stock_detail_rate_<?php echo $grid_row; ?>" value="<?php echo $detail['rate']; ?>" />
                                                    </td>
                                                    <td>
                                                        <input type="text" style="min-width: 100px;" class="form-control fPDecimal" name="opening_stock_details[<?php echo $grid_row; ?>][amount]" id="opening_stock_detail_amount_<?php echo $grid_row; ?>" value="<?php echo $detail['amount']; ?>" readonly="true" />
                                                    </td>
                                                    <td>
                                                        <a onclick="removeRow(this);" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>
                                                        <a title="Add" class="btn btn-xs btn-primary btnAddGrid" id="btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>
                                                    </td>
                                                </tr>
                                                <?php $grid_row++; endforeach; ?>
                                                </tbody>
                                                <tfoot>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:10px;">
                                    <div class="col-sm-offset-9 col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['net_amount']; ?></label>
                                            <input type="text" id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fDecimal" readonly="readonly" onchange="calcNetAmount('+ grid_row +',\'net_amount\')" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <?php if(isset($isEdit) && $isEdit==1): ?>
                                <?php if($is_post == 0): ?>
                                <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                                    <i class="fa fa-thumbs-up"></i>
                                    &nbsp;<?php echo $lang['post']; ?>
                                </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                                    <i class="fa fa-balance-scale"></i>
                                    &nbsp;<?php echo $lang['ledger']; ?>
                                </button>
                                <?php endif; ?>
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                                <i class="fa fa-floppy-o"></i>
                                &nbsp;<?php echo $lang['save']; ?>
                                </button>
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
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $UrlGetProductJSON = '<?php echo $href_get_product_json; ?>';
        var $grid_row = <?php echo $grid_row; ?>;
        var $products = <?php echo json_encode($products) ?>;
        var $warehouses = <?php echo json_encode($warehouses) ?>;

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

//            markup += "<div class='select2-result-repository__statistics'>" +
//                    "   <div class='help-block'>" + repo.length + " X " + repo.width + " X " + repo.thickness + "</div>" +
//                    "</div>" +
//                    "</div></div>";

            return markup;
        }

        function formatRepoSelection (repo) {
            return repo.name || repo.text;
        }

        <?php if($this->request->get['opening_stock_id']): ?>
        $(document).ready(function() {
            $('select.product').select2({
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
                minimumInputLength: 2,
                templateResult: formatRepo, // omitted for brevity, see the source of this page
                templateSelection: formatRepoSelection // omitted for brevity, see the source of this page                }
            });
        });
        <?php endif; ?>
    </script>
    <script type="text/javascript" src="../admin/view/js/inventory/opening_stock.js"></script>
    <?php echo $page_footer; ?>
    <?php echo $right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>