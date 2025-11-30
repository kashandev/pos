<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">×</button>
        <?php echo $error_warning; ?></div>
    <?php } ?>
    <?php  if ($success) { ?>
    <div class="alert alert-success alert-dismissable">
        <button class="close" aria-hidden="true" data-dismiss="alert" type="button">x</button>
        <?php echo $success; ?>
    </div>
    <?php  } ?>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading heading">
                    <?php echo $heading_title . ($is_post?' (Posted)':($this->request->get['sale_inquiry_id']?' (Unposted)':'')); ?>
                    <ul style="float: right;" class="list-nostyle list-inline">
                        <?php if($ledgers): ?>
                        <li><a class="btn btn-outline btn-default btn-sm" onclick="$('#ledger').dialog('open');"><i class="fa fa-indent"></i><?php echo $button_ledger; ?></a></li>
                        <?php endif; ?>
                        <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="validateForm();" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                        <?php if($this->request->get['sale_inquiry_id']): ?>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="<?php echo $action_post; ?>" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-check"></i><?php echo $button_post; ?></a></li>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="<?php echo $action_print; ?>" target="_blank"><i class="fa fa-print"></i><?php echo $button_print; ?></a></li>
                        <?php endif; ?>
                        </ul>
                </div>
                <div class="panel-body">
                    <form  action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $entry_voucher_no; ?></label>
                                    <input class="form-control" type="text" id="invoice_number" name="invoice_no" readonly="readonly" value="<?php echo $voucher_no; ?>" placeholder="auto" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $entry_voucher_date; ?></label>
                                    <input class="form-control dtpDate" type="text" name="invoice_date" value="<?php echo stdDate($voucher_date); ?>" />
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><span class="required">*</span>&nbsp;<?php echo $entry_customer_name; ?></label>
                                    <select class="form-control" id="customer_id" name="customer_id" >
                                        <option value="">&nbsp;</option>
                                        <?php foreach($customers as $customer): ?>
                                        <option value="<?php echo $customer['customer_id']; ?>" <?php echo ($customer_id == $customer['customer_id']?'selected="selected"':''); ?>"><?php echo $customer['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="customer_id" class="error" style="display: none;">&nbsp;</label>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label><?php echo $entry_ref_document_no; ?></label>
                                    <input class="form-control" type="text" name="ref_document_no" value="<?php echo $ref_document_no; ?>" />
                                </div>
                            </div>
                        </div>
                          <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $entry_address; ?></label>
                                    <input  type="text" name="address" value="<?php echo $address; ?>" class="form-control" />
                                    <?php if (isset($error['address'])) { ?>
                                    <span class="error"><?php echo $error['address']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php echo $entry_remarks; ?></label>
                                    <input class="form-control" type="text" name="remarks" value="<?php echo $remarks; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive table-bordered tbl_grid">
                                    <table style="width:100% ;" id="sale_inquiry" class="table form-grid table-striped table-hover flat-grid">
                                        <thead>
                                        <tr align="center">
                                            <td style="width: 3px;"><a id="btnAddGrid1" title="<?php echo $text_add; ?>" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a></td>

                                            <td style="width: 150px;"><?php echo $column_code; ?></td>
                                            <td style="width: 250px;"><?php echo $column_name; ?></td>
                                            <td style="width: 200px;"><?php echo $column_unit; ?></td>
                                            <td style="width: 200px;"><?php echo $column_quantity; ?></td>
                                            <!-- <td style="width: 100px;"><?php echo $column_rate; ?></td>
                                            <td style="width: 200px;"><?php echo $column_warehouse; ?></td>
                                             <td style="width: 100px;"><?php echo $column_amount; ?></td>-->
                                            <td style="width: 10px;"><a id="btnAddGrid" title="<?php echo $text_add; ?>" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a></td>
                                        </tr>
                                        </thead>
                                        <?php $grid_row = 0; ?>
                                        <?php foreach($sale_inquiry_details as $detail): ?>
                                        <tbody id="grid_row_<?php echo $grid_row; ?>" row_id="<?php echo $grid_row; ?>">
                                        <tr>
                                            <td>
                                                <input  type="text" name="sale_inquiry_details[<?php echo $grid_row; ?>][product_code]" id="sale_inquiry_detail_code_<?php echo $grid_row; ?>" class="" value="<?php echo $detail[product_code]; ?>" onchange="getProductInformationCode(<?php echo grid_row; ?>)" />
                                            </td>
                                            <td>
                                                <div class="form-group input-group">
                                                    <select class="" id="sale_inquiry_detail_product_id_<?php echo $grid_row; ?>" name="sale_inquiry_details[<?php echo $grid_row; ?>][product_id]" onchange="getProductInformation(<?php echo grid_row; ?>)">
                                                        <?php foreach($products as $product): ?>
                                                        <option value="<?php echo $product['product_id']; ?>" <?php echo ($detail['product_id'] == $product['product_id']?'selected="selected"':''); ?>"><?php echo $product['name']; ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span class="input-group-btn">
                                                        <button type="button"  model="setup/product" ref_id="sale_inquiry_detail_product_id_<?php echo $grid_row; ?>" callback="getProductInformation" value="..." class="QSearch btn btn-default"><i class="fa fa-search"></i></button>
                                                    </span>
                                                </div>
                                            </td>
                                           <!-- <td>
                                                <select class="" name="sale_inquiry_details[<?php echo $grid_row; ?>][warehouse_id]">
                                                    <option value=""></option>
                                                    <?php foreach($warehouses as $warehouse): ?>
                                                    <option value="<?php echo $warehouse['warehouse_id']; ?>" <?php echo ($detail['warehouse_id'] == $warehouse['warehouse_id']?'selected="selected"':''); ?>><?php echo $warehouse['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>-->
                                            <td>
                                                <select class="" id="sale_inquiry_detail_unit_id_<?php echo $grid_row; ?>" name="sale_inquiry_details[<?php echo $grid_row; ?>][unit_id]">
                                                    <option value=""></option>
                                                    <?php foreach($units as $unit): ?>
                                                    <option value="<?php echo $unit['unit_id']; ?>" <?php echo ($detail['unit_id'] == $unit['unit_id']?'selected="selected"':''); ?>"><?php echo $unit['name']; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="fDecimal" id="sale_inquiry_detail_qty_<?php echo $grid_row; ?>" name="sale_inquiry_details[<?php echo $grid_row; ?>][qty]" value="<?php echo $detail['qty']; ?>" onchange="calcRowTotal('<?php echo $grid_row; ?>','qty')" />
                                                <input type="text" class="fDecimal" id="sale_inquiry_detail_rate_<?php echo $grid_row; ?>" name="sale_inquiry_details[<?php echo $grid_row; ?>][rate]" value="<?php echo $detail['rate']; ?>" onchange="calcRowTotal('<?php echo $grid_row; ?>','rate') e"  hidden="hidden"/>
                                                <input type="text" class="fDecimal" id="sale_inquiry_detail_amount_<?php echo $grid_row; ?>" name="sale_inquiry_details[<?php echo $grid_row; ?>][amount]" value="<?php echo $detail['amount']; ?>" readonly="readonly" onchange="calcRowTotal('<?php echo $grid_row; ?>','amount')" hidden="hidden" />
                                            </td>
                                            <td>
                                                <a title="<?php echo $text_delete; ?>" href="javascript:void(0);" onclick="removeRow(<?php echo $grid_row; ?>);"><span class="fa fa-times"></span></a>
                                            </td>
                                        </tr>
                                        </tbody>
                                        <?php $grid_row++; ?>
                                        <?php endforeach; ?>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                       <!-- <div class="row">
                            <div class="col-md-offset-9 col-md-3">
                                <div class="form-group">
                                    <label><?php echo $column_net_amount; ?></label>
                                    <input type="text"  id="net_amount" name="net_amount" value="<?php echo $net_amount; ?>" class="form-control fDecimal" readonly="readonly" hidden="hidden" />
                                </div>
                            </div>
                        </div>-->

                    </form>
                </div>
                <div class="panel-heading heading">
                    &nbsp;
                    <ul style="float: right;" class="list-nostyle list-inline">
                        <li><a class="btn btn-outline btn-default btn-sm" href="<?php echo $cancel; ?>"><i class="fa fa-undo"></i><?php echo $button_cancel; ?></a></li>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="javascript:void(0);" onclick="validateForm();" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-floppy-o"></i><?php echo $button_save; ?></a></li>
                        <?php if($this->request->get['sale_inquiry_id']): ?>
                        <li><a class="btn btn-outline btn-primary btn-sm" href="<?php echo $action_post; ?>" <?php echo ($is_post?'disabled="true"':''); ?>><i class="fa fa-check"></i><?php echo $button_post; ?></a></li>                        
                        <li><a class="btn btn-outline btn-primary btn-sm" href="<?php echo $action_print; ?>" target="_blank"><i class="fa fa-print"></i><?php echo $button_print; ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var grid_row = '<?php echo $grid_row; ?>';
    function addGridRow() {
        html = '<tbody id="grid_row_' + grid_row + '" row_id="'+ grid_row +'">';
        html +='<tr>';
        html +='<td><a href="javascript:void(0);" onclick="removeGridRow('+grid_row+');"><span class="fa fa-times"></span></a></td>';

        html +='<td><input  type="text" name="sale_inquiry_details['+grid_row+'][product_code]" id="sale_inquiry_detail_code_'+ grid_row +'" class="" value="" onchange="getProductInformationCode('+ grid_row +');" /></td>';
        html +='<td>';
        html +='<div class="form-group input-group">';
        html +='<select class="" id="sale_inquiry_detail_product_id_' + grid_row + '" name="sale_inquiry_details[' + grid_row + '][product_id]" onchange="getProductInformation('+ grid_row +');">';
        html +='<option value=""></option>';
    <?php foreach($products as $product): ?>
        html +='<option value="<?php echo $product['product_id']; ?>" ><?php echo addslashes $product['name']; ?></option>';
    <?php endforeach; ?>
        html +='</select>';
        html +='<span class="input-group-btn ">';
        html +='<button type="button"  model="setup/product" ref_id="sale_inquiry_detail_product_id_' + grid_row + '" callback="getProductInformation"  value="..." class="QSearch btn btn-default" ><i class="fa fa-search"></i>';
        html +='</button></span></div></td>';
     /*  html +='<td><select class="" name="sale_inquiry_details['+grid_row+'][warehouse_id]">';
       html +='<option value=""></option>';
    <?php foreach($warehouses as $warehouse): ?>
        html +='<option value="<?php echo $warehouse['warehouse_id']; ?>" ><?php echo $warehouse['name']; ?></option>';
    <?php endforeach; ?>
        html +='</select></td>';*/
        html +='<td><select class="" id="sale_inquiry_detail_unit_id_' + grid_row + '" name="sale_inquiry_details['+ grid_row +'][unit_id]">';
        html +='<option value=""></option>';
    <?php foreach($units as $unit): ?>
        html +='<option value="<?php echo $unit['unit_id']; ?>" ><?php echo $unit['name']; ?></option>';
    <?php endforeach; ?>
        html +='</select></td>';
        html +='<td>';
        html +='<input  type="text" class="fDecimal" id="sale_inquiry_detail_qty_'+ grid_row +'" name="sale_inquiry_details['+grid_row+'][qty]" value="0" onchange="calcRowTotal('+ grid_row +',\'qty\')" />';
        html +='<input type="text" class="fDecimal" id="sale_inquiry_detail_rate_'+ grid_row +'" name="sale_inquiry_details['+grid_row+'][rate]" value="0" onchange="calcRowTotal('+ grid_row +',\'rate\')" hidden="hidden" />';
        html +='<input type="text" class="fDecimal" id="sale_inquiry_detail_amount_'+ grid_row +'" name="sale_inquiry_details['+grid_row+'][amount]" value="0" onchange="calcRowTotal('+ grid_row +',\'amount\')" readonly="readonly" hidden="hidden"/>';
        html +='</td>';
        html +='<td><a href="javascript:void(0);" onclick="removeGridRow('+grid_row+');"><span class="fa fa-times"></span></a></td>';
        html +='</tr>';
        html +='</tbody>';
        $('#sale_inquiry thead').after(html);

        setFieldFormat();
        grid_row++;

        addRowButton();
    };

    function addRowButton() {
        row_id = $('#sale_inquiry tbody:first').attr('row_id');
        var btnAdd = '<a id="btnAddGrid" title="<?php echo $text_add; ?>" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a>';
        var btnAdd1 = '<a id="btnAddGrid1" title="<?php echo $text_add; ?>" href="javascript:void(0);" onclick="addGridRow();"><span class="fa fa-plus"></span></a>';
        var btnRemove = '<a title="<?php echo $text_delete; ?>" href="javascript:void(0);" onclick="removeGridRow('+row_id+');"><span class="fa fa-times"></span></a>';
        $('#btnAddGrid').remove();
        $('#btnAddGrid1').remove();
        if(row_id) {
            $('#sale_inquiry tbody:first td:last').html(btnAdd+btnRemove);
            $('#sale_inquiry tbody:first td:first').html(btnAdd1+btnRemove);
            $('#sale_inquiry tbody:first input:first').focus();
        } else {
            $('#sale_inquiry thead td:last').html(btnAdd);
            $('#sale_inquiry thead td:first').html(btnAdd1);
        }
    }

    function removeGridRow(grid_row) {
        $('#grid_row_'+grid_row).remove();
        if($('#sale_inquiry tbody').length > 0) {
            $('#supplier_id').attr('disabled','true');
        } else {
            $('#supplier_id').prop('disabled',false);
        }
        addRowButton();
        updatePeopleCombo();
    }

    function getProductUnit(element_id, ref_id) {
        var product_id = $('#'+element_id).val();
        $.ajax({
            url: '<?php echo HTTP_SERVER; ?>index.php?route=setup/product/getProductUnits&token=<?php echo $token; ?>',
            dataType: 'json',
            type: 'POST',
            data: 'product_id=' + product_id,
            beforeSend: function() {
                $('#' + ref_id).after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
            },
            complete: function() {
                $('.wait').remove();
            },
            success: function(json) {
                if(json.success) {
                    $('#' + ref_id).html(json.html).trigger("change");
                } else {
                    alert(json.error);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    function getProductInformation(row_id){
        var element_product_id = '#sale_inquiry_detail_product_id_' + row_id;
        var element_unit_id = ('#sale_inquiry_detail_unit_id_' + row_id);
        var element_code = ('#sale_inquiry_detail_code_' + row_id);
        var element_rate = ('#sale_inquiry_detail_rate_' + row_id);
        //alert(element_product_id+'|'+row_id);
        $.ajax({
            url: '<?php echo HTTP_SERVER; ?>index.php?route=setup/product/getProductInformation&token=<?php echo $token; ?>',
            dataType: 'json',
            type: 'POST',
            data: 'product_id=' + $(element_product_id).val(),
            beforeSend: function() {
                $('#' + row_id).after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
            },
            complete: function() {
                $('.wait').remove();
            },
            success: function(json) {
                if(json.success) {
                    $(element_unit_id).html(json.unit).trigger("change");
                    $(element_code).val(json.code);
                    $(element_rate).val(json.avg_rate);
                } else {
                    alert(json.error);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    function getProductInformationCode(row_id){
        var element_code = '#sale_inquiry_detail_code_' + row_id;
        var element_product_id = ('#sale_inquiry_detail_product_id_' + row_id);
        $.ajax({
            url: '<?php echo HTTP_SERVER; ?>index.php?route=setup/product/getProductInformationCode&token=<?php echo $token; ?>',
            dataType: 'json',
            type: 'POST',
            data: 'product_code=' + $(element_code).val(),
            beforeSend: function() {
                $('#' + row_id).after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
            },
            complete: function() {
                $('.wait').remove();
            },
            success: function(json) {
                if(json.success) {
                    $(element_product_id).val(json.product_id).trigger("change");
                    $(element_product_id).trigger('change');


                } else {
                    alert(json.error);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    function validateForm() {
        var data = $('#form').serialize();
        $.ajax({
            url: '<?php echo HTTP_SERVER; ?>index.php?route=transaction/sale_inquiry/ajaxValidateForm&token=<?php echo $token; ?>',
            dataType: 'json',
            type: 'POST',
            data: data,
            beforeSend: function() {

            },
            complete: function() {
                $('.wait').remove();
            },
            success: function(json) {
                if(json.success) {
                    $('#form').submit();
                } else {
                    alert(json.error);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }


    function calcRowTotal(row_id, event) {
        var qty = 0 ;
        var rate = 0;
        var amount = 0;

        qty += parseFloat($('#sale_inquiry_detail_qty_' + row_id).val());
        rate += parseFloat($('#sale_inquiry_detail_rate_' + row_id).val());

        amount = qty*rate;
        $('#sale_inquiry_detail_amount_' + row_id).val(amount);

        calcNetAmount();
    }

    function calcNetAmount() {
        var net_amount = 0;

        $('#sale_inquiry tbody').each(function() {
            var row_id = $(this).attr('row_id');
            var amount = 0;
            net_amount += (parseFloat($('#sale_inquiry_detail_amount_' + row_id).val()) || 0);
        });

        $('#net_amount').val(net_amount);
    }

</script>


<?php if($ledgers): ?>
<div id="ledger" title="<?php echo $text_ledger_entries; ?>">
    <table class="table table-bordered table-striped table-responsive">
        <thead>
        <tr>
            <td align="center"><?php echo $column_account; ?></td>
            <td align="center"><?php echo $column_debit; ?></td>
            <td align="center"><?php echo $column_credit; ?></td>
        </tr>
        </thead>
        <tbody>
        <?php $sum_debit=0; $sum_credit=0; ?>
        <?php foreach($ledgers as $ledger): ?>
        <?php $sum_debit+=$ledger['debit']; $sum_credit+=$ledger['credit']; ?>
        <tr>
            <td align="left"><?php echo $ledger['display_name']; ?></td>
            <td align="right"><?php echo number_format($ledger['debit'],2); ?></td>
            <td align="right"><?php echo number_format($ledger['credit'],2); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td align="right"><?php echo $column_total; ?></td>
            <td align="right"><strong><?php echo number_format($sum_debit,2); ?></strong></td>
            <td align="right"><strong><?php echo number_format($sum_credit,2); ?></strong></td>
        </tr>
        </tfoot>
    </table>
</div>
<link href="view/js/plugins/ui/jquery.ui.css" rel="stylesheet" />
<script src="view/js/plugins/ui/jquery.ui.js"></script>
<script type="text/javascript">
    $('#ledger').dialog({width: 800, minHeight: 300, autoOpen: false});
</script>
<?php endif; ?>


<?php if($this->request->get['print']): ?>
<script>
    window.open('<?php echo $action_print; ?>','_blank');
</script>
<?php endif; ?>
<script type="text/javascript" src="view/js/plugins/validate/jquery.validate.min.js"></script>
<script>
    jQuery('#form').validate(<?php echo $strValidation; ?>);
</script>
<?php echo $footer; ?>
