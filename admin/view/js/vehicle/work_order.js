$(function() {
    $('.commodity').hide();
    $('#commodity').change(function()
    {
        var $_commodity_value = $(this).val();
        $('.commodity').hide();
        $('#Commodity' + $_commodity_value).show();
    }).trigger('change');
})


$(document).on('change','#CommodityBulk input.fDecimal', function() {
//    var $_qty = parseFloat($('#woc_bulk_qty').val()) || 0;
    var $_net_weight = parseFloat($('#woc_bulk_net_weight').val()) || 0;
    var $_tare_weight = parseFloat($('#woc_bulk_tare_weight').val()) || 0;
    var $_gross_weight = ($_net_weight + $_tare_weight).toFixed(2);
    var $_factory_weight = parseFloat($('#woc_bulk_factory_weight').val()) || 0;
    var $_rate_per_ton = parseFloat($('#woc_bulk_rate_per_ton').val()) || 0;
    var $_freight = ($_net_weight * $_rate_per_ton).toFixed(2);
    var $_loading_charges = parseFloat($('#woc_bulk_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_bulk_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_bulk_weighbride_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_bulk_other_charges').val()) || 0;
    var $_total = (parseFloat($_freight) + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_bulk_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_bulk_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);

    $('#woc_bulk_tare_weight').val($_tare_weight);
    $('#woc_bulk_gross_weight').val($_gross_weight);
    $('#woc_bulk_factory_weight').val($_factory_weight);
    $('#woc_bulk_rate_per_ton').val($_rate_per_ton);
    $('#woc_bulk_freight').val($_freight);
    $('#woc_bulk_loading_charges').val($_loading_charges);
    $('#woc_bulk_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_bulk_weighbride_charges').val($_weighbride_charges);
    $('#woc_bulk_other_charges').val($_other_charges);
    $('#woc_bulk_total').val($_total);
    $('#woc_bulk_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_bulk_sales_tax_amount').val($_sales_tax);
    $('#woc_bulk_grand_total').val($_grand_total);
    $('#woc_bulk_income_tax_percent').val($_income_tax_percent);
    $('#woc_bulk_income_tax_amount').val($_income_tax);
    $('#woc_bulk_net_freight').val($_net_freight);

});


$(document).on('change','#CommodityContainers input.fDecimal', function() {

    var $_freight = parseFloat($('#woc_containers_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_containers_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_containers_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_containers_weighbride_charges').val()) || 0;
    var $_detention = parseFloat($('#woc_containers_detention').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_containers_deposit_charges').val()) || 0;
    var $_lolo = parseFloat($('#woc_containers_lolo').val()) || 0;
    var $_other_charges = parseFloat($('#woc_containers_other_charges').val()) || 0;
    var $_total = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_detention + $_deposit_charges + $_lolo + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_containers_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_containers_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);


    $('#woc_containers_freight').val($_freight);
    $('#woc_containers_loading_charges').val($_loading_charges);
    $('#woc_containers_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_containers_weighbride_charges').val($_weighbride_charges);
    $('#woc_containers_detention').val($_detention);
    $('#woc_containers_deposit_charges').val($_deposit_charges);
    $('#woc_containers_lolo').val($_lolo);
    $('#woc_containers_other_charges').val($_other_charges);
    $('#woc_containers_total').val($_total);
    $('#woc_containers_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_containers_sales_tax_amount').val($_sales_tax);
    $('#woc_containers_grand_total').val($_grand_total);
    $('#woc_containers_income_tax_percent').val($_income_tax_percent);
    $('#woc_containers_income_tax_amount').val($_income_tax);
    $('#woc_containers_net_freight').val($_net_freight);

});


$(document).on('change','#CommodityBags input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_bags_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_bags_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_bags_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_bags_weighbride_charges').val()) || 0;
    var $_detention = parseFloat($('#woc_bags_detention').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_bags_deposit_charges').val()) || 0;
    var $_labour_charges = parseFloat($('#woc_bags_labour_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_bags_other_charges').val()) || 0;
    var $_total = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_detention + $_deposit_charges + $_labour_charges + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_bags_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_bags_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);

    $('#woc_bags_freight').val($_freight);
    $('#woc_bags_loading_charges').val($_loading_charges);
    $('#woc_bags_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_bags_weighbride_charges').val($_weighbride_charges);
    $('#woc_bags_detention').val($_detention);
    $('#woc_bags_deposit_charges').val($_deposit_charges);
    $('#woc_bags_labour_charges').val($_labour_charges);
    $('#woc_bags_other_charges').val($_other_charges);
    $('#woc_bags_total').val($_total);
    $('#woc_bags_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_bags_sales_tax_amount').val($_sales_tax);
    $('#woc_bags_grand_total').val($_grand_total);
    $('#woc_bags_income_tax_percent').val($_income_tax_percent);
    $('#woc_bags_income_tax_amount').val($_income_tax);
    $('#woc_bags_net_freight').val($_net_freight);

});


$(document).on('change','#CommodityLCL input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_lcl_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_lcl_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_lcl_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_lcl_weighbride_charges').val()) || 0;
    var $_detention = parseFloat($('#woc_lcl_detention').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_lcl_deposit_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_lcl_other_charges').val()) || 0;
    var $_total = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_detention + $_deposit_charges + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_lcl_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_lcl_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);


    $('#woc_lcl_freight').val($_freight);
    $('#woc_lcl_loading_charges').val($_loading_charges);
    $('#woc_lcl_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_lcl_weighbride_charges').val($_weighbride_charges);
    $('#woc_lcl_detention').val($_detention);
    $('#woc_lcl_deposit_charges').val($_deposit_charges);
    $('#woc_lcl_other_charges').val($_other_charges);
    $('#woc_lcl_total').val($_total);
    $('#woc_lcl_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_lcl_sales_tax_amount').val($_sales_tax);
    $('#woc_lcl_grand_total').val($_grand_total);
    $('#woc_lcl_income_tax_percent').val($_income_tax_percent);
    $('#woc_lcl_income_tax_amount').val($_income_tax);
    $('#woc_lcl_net_freight').val($_net_freight);

});

$(document).on('change','#CommodityPacking input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_packing_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_packing_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_packing_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_packing_weighbride_charges').val()) || 0;
    var $_detention = parseFloat($('#woc_packing_detention').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_packing_deposit_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_packing_other_charges').val()) || 0;
    var $_total = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_detention + $_deposit_charges + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_packing_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_packing_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);


    $('#woc_packing_freight').val($_freight);
    $('#woc_packing_loading_charges').val($_loading_charges);
    $('#woc_packing_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_packing_weighbride_charges').val($_weighbride_charges);
    $('#woc_packing_detention').val($_detention);
    $('#woc_packing_deposit_charges').val($_deposit_charges);
    $('#woc_packing_other_charges').val($_other_charges);
    $('#woc_packing_total').val($_total);
    $('#woc_packing_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_packing_sales_tax_amount').val($_sales_tax);
    $('#woc_packing_grand_total').val($_grand_total);
    $('#woc_packing_income_tax_percent').val($_income_tax_percent);
    $('#woc_packing_income_tax_amount').val($_income_tax);
    $('#woc_packing_net_freight').val($_net_freight);

});


$(document).on('change','#CommodityPallets input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_pallets_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_pallets_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_pallets_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_pallets_weighbride_charges').val()) || 0;
    var $_detention = parseFloat($('#woc_pallets_detention').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_pallets_deposit_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_pallets_other_charges').val()) || 0;
    var $_total = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_detention + $_deposit_charges + $_other_charges).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_pallets_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_pallets_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);


    $('#woc_pallets_freight').val($_freight);
    $('#woc_pallets_loading_charges').val($_loading_charges);
    $('#woc_pallets_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_pallets_weighbride_charges').val($_weighbride_charges);
    $('#woc_pallets_detention').val($_detention);
    $('#woc_pallets_deposit_charges').val($_deposit_charges);
    $('#woc_pallets_other_charges').val($_other_charges);
    $('#woc_pallets_total').val($_total);
    $('#woc_pallets_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_pallets_sales_tax_amount').val($_sales_tax);
    $('#woc_pallets_grand_total').val($_grand_total);
    $('#woc_pallets_income_tax_percent').val($_income_tax_percent);
    $('#woc_pallets_income_tax_amount').val($_income_tax);
    $('#woc_pallets_net_freight').val($_net_freight);
});

$(document).on('change','#supplier_id', function() {
    var $cell_no1 = $('#supplier_id option:selected').data('mobile_no');
    $('#broker_cell').val($cell_no1);
})

$(document).on('change','#driver_id', function() {
    var $cell_no = $('#driver_id option:selected').data('mobile_no');
    $('#driver_cell').val($cell_no);
})


$(document).on('click','.btnAddGrid', function() {
    $html = '';
    $html += '<tr id="grid_row_'+$grid_row+'" data-row_id="'+$grid_row+'">';
    $html += '<td>';
    $html += '<a onclick="removeRow('+$grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '&nbsp;<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '</td>';
    $html += '<td>';
    $html += '<select class="form-control select2" id="grid_row_'+$grid_row+'_coa_id" name="work_order_details['+$grid_row+'][coa_id]">';
    $html += '<option value="">&nbsp;</option>';
    $.each($coas,function(i,$obj) {
        $html += '<option value="'+$obj['coa_level3_id']+'">'+$obj['level3_display_name']+'</option>';
    })
    $html += '</select>'
    $html += '</td>'
    $html += '<td>';
    $html += '<input type="text" class="form-control" id="grid_row_'+$grid_row+'_remarks" name="work_order_details['+$grid_row+'][remarks]"  value="" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fDecimal" id="grid_row_'+$grid_row+'_debit" name="work_order_details['+$grid_row+'][debit]"  value="" onchange="calcTotal();" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<input type="text" class="form-control fDecimal" id="grid_row_'+$grid_row+'_credit" name="work_order_details['+$grid_row+'][credit]" value="" onchange="calcTotal();" />';
    $html += '</td>';
    $html += '<td>';
    $html += '<a title="Add" class="btn btn-xs btn-primary btnAddGrid" href="javascript:void(0);"><i class="fa fa-plus"></i></a>';
    $html += '&nbsp;';
    $html += '<a onclick="removeRow('+$grid_row+');" title="Remove" class="btn btn-xs btn-danger" href="javascript:void(0);"><i class="fa fa-times"></i></a>';
    $html += '</td>';
    $html += '</tr>'

    if($(this).parent().parent().data('row_id')=='H') {
        $('#tblTransaction tbody').prepend($html);
    } else {
        $(this).parent().parent().after($html);
    }

    //$('#tblTransaction #grid_row_'+$grid_row+' input:first').focus();

    $('#grid_row_'+$grid_row+'_coa_id').select2({width: '100%'}).select2('open');
    $grid_row++;
});


function removeRow(row_id) {
    $('#grid_row_'+row_id).remove();
    calcTotal();
}

function calcTotal() {
    var debit_amount = 0;
    var credit_amount = 0;

    $('#tblTransaction tbody tr').each(function() {
        var row_id = $(this).data('row_id');
        console.log(row_id);

        debit_amount += (parseFloat($('#grid_row_' + row_id+ '_debit').val()) || 0);
        credit_amount += (parseFloat($('#grid_row_' + row_id+ '_credit').val()) || 0);

    });

    $('#total_debit').val(debit_amount);
    $('#total_credit').val(credit_amount);
}
