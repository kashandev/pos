<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
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
                        <!--<?php if($is_post == 0): ?>
                        <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                            <i class="fa fa-thumbs-up"></i>
                            &nbsp;<?php echo $lang['post']; ?>
                        </a>
                        <?php endif; ?> -->
                        <!--
                        <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                            <i class="fa fa-balance-scale"></i>
                            &nbsp;<?php echo $lang['ledger']; ?>
                        </button> -->
                        <!--
                        <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                            <i class="fa fa-print"></i>
                            &nbsp;<?php echo $lang['print']; ?>
                        </a> -->
                        <a class="btn btn-danger" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                        <?php else: ?>
                        <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                            <i class="fa fa-undo"></i>
                            &nbsp;<?php echo $lang['cancel']; ?>
                        </a>
                      <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                        <i class="fa fa-floppy-o"></i>
                        &nbsp;<?php echo $lang['save']; ?>
                        </button>
                        <?php endif; ?>
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
                            <input type="hidden" value="<?php echo $document_type_id; ?>" name="document_type_id" id="document_type_id" />
                            <input type="hidden" value="<?php echo $document_id; ?>" name="document_id" id="document_id" />
                            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_no']; ?></label>
                                            <input type="text" name="document_identity" value="<?php echo $document_identity; ?>" class="form-control" readonly="true" />
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['document_date']; ?></label>
                                            <input class="form-control dtpDate" type="text" name="document_date" value="<?php echo $document_date; ?>"/>
                                        </div>
                                    </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['from_date']; ?></label>
                                                <input type="text" id="date_from" name="date_from" value="<?php echo $date_from; ?>" class="form-control dtpDate" autocomplete="off"/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label><?php echo $lang['to_date']; ?></label>
                                                <input type="text" id="date_to" name="date_to" value="<?php echo $date_to; ?>" class="form-control dtpDate" autocomplete="off"/>
                                            </div>
                                        </div>

                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['bank']; ?></label>
                                            <?php if($coa_level3_id != ''){ ?>
                                            <input type="text" class="form-control" value="<?php echo $bank ?>" readonly="true">


                                            <?php }else{ ?>
                                            <select onchange="GetDocumentDetails();" class="form-control" id="coa_level3_id" name="coa_level3_id" >
                                                <option value="">&nbsp;</option>
                                                <?php foreach($coas as $coa): ?>
                                                <option value="<?php echo $coa['coa_level3_id']; ?>" <?php echo ($coa['coa_level3_id']==$coa_level3_id ? 'selected="selected"' : ''); ?>><?php echo $coa['level3_display_name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php echo $lang['closing_balance'] ?></label>
                                            <?php if($closing_balance > 0){ ?>
                                            <input class="form-control" type="text" name="closing_balance" id="closing_balance" value="<?php echo $closing_balance ?>" readonly="true">
                                            <?php }else{ ?>
                                            <input class="form-control" type="text" name="closing_balance" id="closing_balance" value="0">
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <p style="margin-top: 27px; font-weight: bold; font-size: 20px;"><i>As per Bank Statement</i></p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?php echo $lang['remarks']; ?></label>
                                            <input type="text" class="form-control" name="remarks" value="<?php echo $remarks; ?>"/>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="table-responsive form-group">
                                        <table id="tblBankReconciliationDetail" class="table table-striped table-bordered">
                                            <thead>
                                            <tr align="center" data-row_id="H">
                                                <td style="width: 7%;">
                                                    <!--<a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a>-->
                                                </td>
                                                <td><?php echo $lang['clearing_date']; ?></td>
                                                <td><?php echo $lang['document_date']; ?></td>
                                                <td><?php echo $lang['document_no']; ?></td>
                                                <td><?php echo $lang['cheque_no']; ?></td>
                                                <td><?php echo $lang['cheque_date']; ?></td>
                                                <td><?php echo $lang['debit']; ?></td>
                                                <td style="width: 8%;"><?php echo $lang['credit']; ?></td>
                                                <td><?php echo $lang['balance']; ?></td>
                                                <td style="width: 7%;">
                                                    <!--<a class="btnAddGrid btn btn-xs btn-primary" title="Add" href="javascript:void(0);"><i class="fa fa-plus"></i></a>-->
                                                </td>
                                            </tr>
                                            </thead>
                                            <?php $grid_row = 0; ?>
                                            <tbody>
                                            <?php foreach($bank_reconciliation_details as $detail): ?>
                                            <tr id="grid_row_<?php echo $grid_row; ?>" data-row_id="<?php echo $grid_row; ?>">
                                                <td>
                                                    <input style="width: 25px; height: 25px;margin-left: 25px;" type="checkbox" name="bank_reconciliation_details[<?php echo $grid_row; ?>][clearance]" id="clearance" value="1" disabled <?php echo ($detail['clearance']=='1'?'checked':'' ); ?>/>
                                                  </td>
                                                <td>
                                                    <input type="text" id="bank_reconciliation_detail_clearing_date_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][clearing_date]" value="<?php echo $detail['clearing_date']; ?>" class="form-control dtpDate" autocomplete="off" readonly="true"/>
                                                </td>
                                                <td>
                                                    <input type="text" id="bank_reconciliation_detail_document_date_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][document_date]" value="<?php echo $detail['document_date']; ?>" class="form-control dtpDate" autocomplete="off" readonly="true"/>
                                                </td>
                                                <td>
                                                    <input style="width: 200px;" type="text" id="bank_reconciliation_detail_document_identity_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][document_identity]" value="<?php echo $detail['document_identity']; ?>" class="form-control" readonly="true"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_ref_document_identity_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][ref_document_identity]" value="<?php echo $detail['ref_document_identity']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_document_type_id_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][document_type_id]" value="<?php echo $detail['document_type_id']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_ref_document_type_id_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][ref_document_type_id]" value="<?php echo $detail['ref_document_type_id']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_document_id_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][document_id]" value="<?php echo $detail['document_id']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_conversion_rate_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][conversion_rate]" value="<?php echo $detail['conversion_rate']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_base_currency_id_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][base_currency_id]" value="<?php echo $detail['base_currency_id']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_product_id_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][product_id]" value="<?php echo $detail['product_id']; ?>" class="form-control"/>
                                                </td>
                                                <td>
                                                    <input type="text" id="bank_reconciliation_detail_cheque_no_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][cheque_no]" value="<?php echo $detail['cheque_no']; ?>" class="form-control" readonly="true"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_qty_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][qty]" value="<?php echo $detail['qty']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_document_amount_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][document_amount]" value="<?php echo $detail['document_amount']; ?>" class="form-control"/>
                                                    <input type="hidden" id="bank_reconciliation_detail_amount_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][amount]" value="<?php echo $detail['amount']; ?>" class="form-control"/>
                                                </td>
                                                <td>
                                                    <input type="text" id="bank_reconciliation_detail_cheque_date_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][cheque_date]" value="<?php echo $detail['cheque_date']; ?>" class="form-control dtpDate" autocomplete="off" readonly="true"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control fDecimal" id="bank_reconciliation_detail_debit_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][debit]" value="<?php echo $detail['debit']; ?>" readonly="true"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control fDecimal " id="bank_reconciliation_detail_credit_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][credit]" value="<?php echo $detail['credit']; ?>" readonly="true"/>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control fDecimal " id="bank_reconciliation_detail_balance_<?php echo $grid_row; ?>" name="bank_reconciliation_details[<?php echo $grid_row; ?>][balance]" value="<?php echo $detail['balance']; ?>" readonly="true"/>
                                                </td>

                                                <td>
                                                </td>
                                            </tr>
                                            <?php $grid_row++; ?>
                                            <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                            </tfoot>
                                        </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" style="margin-top:10px;">

                                    <div class="col-sm-offset-6 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_debit']; ?></label>
                                            <input type="text" id="total_debit" name="total_debit" value="<?php echo $total_debit; ?>" class="form-control" readonly="readonly" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_credit']; ?></label>
                                            <input type="text" id="total_credit" name="total_credit" value="<?php echo $total_credit; ?>" class="form-control" readonly="readonly" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $lang['total_balance']; ?></label>
                                            <input type="text" id="total_balance" name="total_balance" value="<?php echo $total_balance; ?>" class="form-control" readonly="readonly" />
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="box-footer">
                            <div class="pull-right">
                                <?php if(isset($isEdit) && $isEdit==1): ?>
                                <!--<?php if($is_post == 0): ?>
                                <a class="btn btn-info" href="<?php echo $action_post; ?>" onclick="return  confirm('Are you sure you want to post this item?');">
                                    <i class="fa fa-thumbs-up"></i>
                                    &nbsp;<?php echo $lang['post']; ?>
                                </a>
                                <?php endif; ?> -->
                                <!--
                                <button type="button" class="btn btn-info" href="javascript:void(0);" onclick="getDocumentLedger();">
                                    <i class="fa fa-balance-scale"></i>
                                    &nbsp;<?php echo $lang['ledger']; ?>
                                </button> -->
                                <!--
                                <a class="btn btn-info" target="_blank" href="<?php echo $action_print; ?>">
                                    <i class="fa fa-print"></i>
                                    &nbsp;<?php echo $lang['print']; ?>
                                </a> -->
                                <a class="btn btn-danger" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <?php else: ?>
                                <a class="btn btn-default" href="<?php echo $action_cancel; ?>">
                                    <i class="fa fa-undo"></i>
                                    &nbsp;<?php echo $lang['cancel']; ?>
                                </a>
                                <button type="button" class="btn btn-primary btnsave" href="javascript:void(0);" onclick="Save();" <?php echo ($is_post==1?'disabled="true"':''); ?>>
                                <i class="fa fa-floppy-o"></i>
                                &nbsp;<?php echo $lang['save']; ?>
                                </button>
                                <?php endif; ?>
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
    <script type="text/javascript" src="../admin/view/js/gl/bank_reconciliation.js"></script>
    <script type="text/javascript" src="plugins/validate/jquery.validate.min.js"></script>
    <script type="text/javascript" src="plugins/iCheck/icheck.js"></script>
    <script>
        jQuery('#form').validate(<?php echo $strValidation; ?>);
        var $UrlGetDocumentDetails = '<?php echo $href_get_document_detail; ?>';
        var $grid_row = '<?php echo $grid_row; ?>';
        var $check=0;
        var $lang = <?php echo json_encode($lang) ?>;
        var $coas = <?php echo json_encode($coas) ?>;
        $(document).ready(function() {
//        $('#partner_type_id').val('2').trigger('change');
           // calculateTotal();

            $('.iCheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });

        });
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>