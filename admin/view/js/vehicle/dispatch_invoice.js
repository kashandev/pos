$(function() {
    $('.commodity').hide();
    $('#commodity').change(function()
    {
        var $_commodity_value = $(this).val();
        $('.commodity').hide();
        $('#Commodity' + $_commodity_value).show();
    }).trigger('change');
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














//function fnAddLclDetail() {
//    $html = '';
//    $html += '<tr id="grid_row'+$grid_row+'">';
//    $html += '<td>';
//    $html += '<input type="text" class="form-control" id="grid_row_'+$grid_row+'_dispatch_note" name="lcl_details['+$grid_row+'][dispatch_note]"  value="" />';
//    $html += '</td>';
//    $html += '<td>';
//    $html += '<input type="text" class="form-control " id="grid_row_'+$grid_row+'_no_of_stops" name="lcl_details['+$grid_row+'][no_of_stops]"  value=""  />';
//    $html += '</td>';
//    $html += '<td>';
//    $html += '<input type="text" class="form-control fDecimal" id="grid_row_'+$grid_row+'_weight" name="lcl_details['+$grid_row+'][weight]" value=""  />';
//    $html += '</td>';
//    $html += '<td><a href="javascript:void(0);" onclick="removeRow('+$grid_row+');"><span class="fa fa-times"></span></a></td>';
//    $html += '</tr>'
//
//    $('#tbllcl tbody').append($html);
//
//    setFieldFormat();
//}



$(document).on('change','#driver_id', function() {
    var $cell_no = $('#driver_id option:selected').data('mobile_no');
    $('#driver_cell').val($cell_no);
})

$(document).on('change','#supplier_id', function() {
    var $cell_no1 = $('#supplier_id option:selected').data('mobile_no');
    $('#broker_cell').val($cell_no1);
})



$(document).on('change','#CommodityBulk input.fDecimal', function() {
    var $_net_weight = parseFloat($('#woc_bulk_net_weight').val()) || 0;
    var $_tare_weight = parseFloat($('#woc_bulk_tare_weight').val()) || 0;
    var $_gross_weight = ($_net_weight + $_tare_weight).toFixed(2);
    var $_factory_weight = parseFloat($('#woc_bulk_factory_weight').val()) || 0;
    var $_diff_weight = ($_net_weight - $_factory_weight).toFixed(2);


    $('#woc_bulk_net_weight').val($_net_weight);
    $('#woc_bulk_tare_weight').val($_tare_weight);
    $('#woc_bulk_gross_weight').val($_gross_weight);
    $('#woc_bulk_factory_weight').val($_factory_weight);
    $('#woc_bulk_diff_weight').val($_diff_weight);

})


function setBulk($data) {

    $('#woc_bulk_project_name').val($data['project_name']);
    $('#woc_bulk_bilty_no').val($data['bilty_no']);
    $('#woc_bulk_clearing_agent').val($data['clearing_agent']);
    $('#woc_bulk_region').val($data['region']);
    $('#woc_bulk_po_no').val($data['po_no']);
    $('#woc_bulk_commodity').val($data['commodity']);
    $('#woc_bulk_vessel_name').val($data['vessel_name']);
    $('#woc_bulk_loading_point_id').val($data['loading_point_id']).trigger('change');
    $('#woc_bulk_destination_id').val($data['destination_id']).trigger('change');
//    $('#woc_bulk_qty').val($data['qty']);
    $('#woc_bulk_qty').val(0);
//    $('#woc_bulk_rate_per_ton').val($data['rate_per_ton']);
    $('#woc_bulk_rate_per_ton').val(0);
    $('#woc_bulk_freight').val(0);
//    $('#woc_bulk_freight').val($data['freight']);
    $('#woc_bulk_loading_charges').val($data['loading_charges']);
    $('#woc_bulk_excess_wgt_charges').val($data['excess_wgt_charges']);
    $('#woc_bulk_weighbride_charges').val($data['weighbride_charges']);
    $('#woc_bulk_other_charges').val($data['other_charges']);
//    $('#woc_bulk_total_frieght').val($data['total']);
    $('#woc_bulk_total_frieght').val(0);
    $('#woc_bulk_sales_tax_amount').val($data['sales_tax_amount']);
    $('#woc_bulk_income_tax_amount').val($data['income_tax_amount']);
    $('#woc_bulk_adv_payment').val(0);


}
function setContainer($data) {

    $('#woc_containers_project_name').val($data['project_name']);
    $('#woc_containers_commodity').val($data['commodity']).trigger('change');
    $('#woc_containers_po_no').val($data['po_no']);
    $('#woc_containers_40ft_std').val($data['40ft_std']);
    $('#woc_containers_20ft_std').val($data['20ft_std']);
    $('#woc_containers_direct_shifting').val($data['direct_shifting']);
    $('#woc_containers_bl').val($data['bl']);
    $('#woc_containers_shipping_line').val($data['shipping_line']);
    $('#woc_containers_loading_point_id').val($data['loading_point_id']).trigger('change');
    $('#woc_containers_destination_id').val($data['destination_id']).trigger('change');
    $('#woc_containers_agent').val($data['clearing_agent']);
    $('#woc_containers_depositor').val($data['depositor_name']);
//    $('#woc_containers_freight').val($data['freight']);
    $('#woc_containers_freight').val(0);
    $('#woc_containers_loading_charges').val($data['loading_charges']);
    $('#woc_containers_excess_wgt_charges').val($data['wgt_charges']);
    $('#woc_containers_weighbride_charges').val($data['weighbride_charges']);
    $('#woc_containers_detention_charges').val($data['detention']);
    $('#woc_containers_deposit_fee').val($data['deposit_charges']);
    $('#woc_containers_lolo').val($data['lolo']);
    $('#woc_containers_other_charges').val($data['other_charges']);
    $('#woc_containers_total').val($data['total']);

}
function setBags($data) {

    $('#woc_bags_project_name').val($data['project_name']);
    $('#woc_bags_qty').val($data['qty']);
    $('#woc_bags_commodity').val($data['commodity']);
    $('#woc_bags_packing').val($data['packing']);
    $('#woc_bags_loading_point_id').val($data['loading_point_id']).trigger('change');
    $('#woc_bags_destination_id').val($data['destination_id']).trigger('change');
    $('#woc_bags_freight').val(0);
//    $('#woc_bags_freight').val($data['freight']);
    $('#woc_bags_loading_charges').val($data['loading_charges']);
    $('#woc_bags_excess_wgt_charges').val($data['excess_wgt_charges']);
    $('#woc_bags_weighbride_charges').val($data['weighbride_charges']);
    $('#woc_bags_deposit_charges').val($data['deposit_charges']);
    $('#woc_bags_labour_charges').val($data['labour_charges']);
    $('#woc_bags_lolo').val($data['lolo']);
    $('#woc_bags_other_charges').val($data['other_charges']);
    $('#woc_bags_total').val($data['total']);
}
function setLCL($data) {

    $('#woc_lcl_project_name').val($data['project_name']);
    $('#woc_lcl_clearing_agent').val($data['clearing_agent']);
    $('#woc_lcl_depositor_name').val($data['depositor_name']);
    $('#woc_lcl_shipping_line').val($data['shipping_line']);
    $('#woc_lcl_no_of_pkg').val($data['no_of_pkg']);
    $('#woc_lcl_net_weight').val($data['weight']);
    $('#woc_lcl_loading_point_id').val($data['loading_point_id']).trigger('change');
    $('#woc_lcl_destination_id').val($data['destination_id']).trigger('change');
//    $('#woc_lcl_freight').val($data['freight']);
    $('#woc_lcl_freight').val(0);
    $('#woc_lcl_loading_charges').val($data['loading_charges']);
    $('#woc_lcl_excess_wgt_charges').val($data['excess_wgt_charges']);
    $('#woc_lcl_weighbride_charges').val($data['weighbride_charges']);
    $('#woc_lcl_deposit_charges').val($data['deposit_charges']);
    $('#woc_lcl_other_charges').val($data['other_charges']);
    $('#woc_lcl_total').val($data['total']);
}

function setPallets($data) {

    $('#woc_pallets_project_name').val($data['project_name']);
    $('#woc_pallets_no_of_pallets').val($data['no_of_pallets']);
    $('#woc_pallets_weight').val($data['weight']);
    $('#woc_pallets_loading_point_id').val($data['loading_point_id']).trigger('change');
    $('#woc_pallets_destination_id').val($data['destination_id']).trigger('change');
    $('#woc_pallets_freight').val(0);
//    $('#woc_pallets_freight').val($data['freight']);
    $('#woc_pallets_loading_charges').val($data['loading_charges']);
    $('#woc_pallets_excess_wgt_charges').val($data['excess_wgt_charges']);
    $('#woc_pallets_weighbride_charges').val($data['weighbride_charges']);
    $('#woc_pallets_deposit_charges').val($data['deposit_charges']);
    $('#woc_pallets_other_charges').val($data['other_charges']);
    $('#woc_pallets_total').val($data['total']);
}


$(document).on('change','#CommodityBulk input.fDecimal', function() {
    var $_net_weight = parseFloat($('#woc_bulk_net_weight').val()) || 0;
    var $_rate_per_ton = parseFloat($('#woc_bulk_rate_per_ton').val()) || 0;
    var $_freight = ($_net_weight * $_rate_per_ton).toFixed(2);
    var $_loading_charges = parseFloat($('#woc_bulk_loading_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_bulk_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_bulk_weighbride_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_bulk_other_charges').val()) || 0;
    var $_total_frieght = (parseFloat($_freight) + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges + $_other_charges).toFixed(2);


    var $_adv_payment = parseFloat($('#woc_bulk_adv_payment').val()) || 0;
//    var $_fuel = parseFloat($('#woc_bulk_fuel').val()) || 0;
//    var $_service_charges = parseFloat($('#woc_bulk_service_charges').val()) || 0;
//    var $_commission = parseFloat($('#woc_bulk_commission').val()) || 0;
//    var $_loss_deduction = parseFloat($('#woc_bulk_loss_deduction').val()) || 0;
//    var $_vhr_comm = parseFloat($('#woc_bulk_vhr_comm').val()) || 0;
//    var $_texas = parseFloat($('#woc_bulk_texas').val()) || 0;
//    var $_grand_total = (parseFloat($_adv_payment) + $_fuel + $_service_charges + $_commission + $_loss_deduction + $_vhr_comm + $_texas).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_bulk_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total_frieght) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total_frieght) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_bulk_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);

    var $_due_balance = parseFloat($_net_freight - $_adv_payment) || 0;



    $('#woc_bulk_rate_per_ton').val($_rate_per_ton);
    $('#woc_bulk_freight').val($_freight);
    $('#woc_bulk_loading_charges').val($_loading_charges);
    $('#woc_bulk_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_bulk_weighbride_charges').val($_weighbride_charges);
    $('#woc_bulk_other_charges').val($_other_charges);
    $('#woc_bulk_total_frieght').val($_total_frieght);
    $('#woc_bulk_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_bulk_sales_tax_amount').val($_sales_tax);
    $('#woc_bulk_grand_total').val($_grand_total);
    $('#woc_bulk_income_tax_percent').val($_income_tax_percent);
    $('#woc_bulk_income_tax_amount').val($_income_tax);
    $('#woc_bulk_net_freight').val($_net_freight);
    $('#woc_bulk_balance').val($_due_balance);


});

$(document).on('change','#CommodityLCL input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_lcl_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_lcl_loading_charges').val()) || 0;
    var $_excess_stop = parseFloat($('#woc_lcl_excess_stop').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_lcl_excess_wgt_charges').val()) || 0;
    var $_shifting_charges = parseFloat($('#woc_lcl_shifting_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_lcl_weighbride_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_lcl_other_charges').val()) || 0;
    var $_total_frieght = ($_freight + $_loading_charges + $_excess_stop + $_excess_wgt_charges + $_shifting_charges + $_weighbride_charges  + $_other_charges).toFixed(2);

    var $_adv_payment = parseFloat($('#woc_lcl_adv_payment').val()) || 0;
//    var $_fuel = parseFloat($('#woc_lcl_fuel').val()) || 0;
//    var $_service_charges = parseFloat($('#woc_lcl_service_charges').val()) || 0;
//    var $_commission = parseFloat($('#woc_lcl_commission').val()) || 0;
//    var $_loss_deduction = parseFloat($('#woc_lcl_loss_deduction').val()) || 0;
//    var $_vhr_comm = parseFloat($('#woc_lcl_vhr_comm').val()) || 0;
//    var $_texas = parseFloat($('#woc_lcl_texas').val()) || 0;
//    var $_grand_total = (parseFloat($_adv_payment) + $_fuel + $_service_charges + $_commission + $_loss_deduction + $_vhr_comm + $_texas).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_lcl_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total_frieght) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total_frieght) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_lcl_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);
    var $_due_balance = parseFloat($_net_freight - $_adv_payment).toFixed(2) || 0;


    $('#woc_lcl_freight').val($_freight);
    $('#woc_lcl_loading_charges').val($_loading_charges);
    $('#woc_lcl_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_lcl_weighbride_charges').val($_weighbride_charges);
    $('#woc_lcl_other_charges').val($_other_charges);
    $('#woc_lcl_total_frieght').val($_total_frieght);
    $('#woc_lcl_total').val($_grand_total);
    $('#woc_lcl_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_lcl_sales_tax_amount').val($_sales_tax);
    $('#woc_lcl_grand_total').val($_grand_total);
    $('#woc_lcl_income_tax_percent').val($_income_tax_percent);
    $('#woc_lcl_income_tax_amount').val($_income_tax);
    $('#woc_lcl_net_freight').val($_net_freight);
    $('#woc_lcl_balance').val($_due_balance);

});


$(document).on('change','#CommodityBags input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_bags_freight').val()) || 0;
    var $_loading_charges = parseFloat($('#woc_bags_loading_charges').val()) || 0;
    var $_labour_charges = parseFloat($('#woc_bags_labour_charges').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_bags_deposit_charges').val()) || 0;
    var $_excess_wgt_charges = parseFloat($('#woc_bags_excess_wgt_charges').val()) || 0;
    var $_weighbride_charges = parseFloat($('#woc_bags_weighbride_charges').val()) || 0;
    var $_other_charges = parseFloat($('#woc_bags_other_charges').val()) || 0;
    var $_total_freight = ($_freight + $_loading_charges + $_excess_wgt_charges + $_weighbride_charges  + $_deposit_charges + $_labour_charges + $_other_charges).toFixed(2);

    var $_adv_payment = parseFloat($('#woc_bags_adv_payment').val()) || 0;
//    var $_fuel = parseFloat($('#woc_bags_fuel').val()) || 0;
//    var $_service_charges = parseFloat($('#woc_bags_service_charges').val()) || 0;
//    var $_commission = parseFloat($('#woc_bags_commission').val()) || 0;
//    var $_loss_deduction = parseFloat($('#woc_bags_loss_deduction').val()) || 0;
//    var $_vhr_comm = parseFloat($('#woc_bags_vhr_comm').val()) || 0;
//    var $_texas = parseFloat($('#woc_bags_texas').val()) || 0;
//    var $_grand_total = (parseFloat($_adv_payment) + $_fuel + $_service_charges + $_commission + $_loss_deduction + $_vhr_comm + $_texas).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_bags_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total_freight) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total_freight) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_bags_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);

    var $_due_balance = parseFloat($_net_freight - $_adv_payment).toFixed(2) || 0;


    $('#woc_bags_freight').val($_freight);
    $('#woc_bags_loading_charges').val($_loading_charges);
    $('#woc_bags_excess_wgt_charges').val($_excess_wgt_charges);
    $('#woc_bags_weighbride_charges').val($_weighbride_charges);
    $('#woc_bags_deposit_charges').val($_deposit_charges);
    $('#woc_bags_labour_charges').val($_labour_charges);
    $('#woc_bags_other_charges').val($_other_charges);
    $('#woc_bags_total_frieght').val($_total_freight);
    $('#woc_bags_total').val($_grand_total);
    $('#woc_bags_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_bags_sales_tax_amount').val($_sales_tax);
    $('#woc_bags_grand_total').val($_grand_total);
    $('#woc_bags_income_tax_percent').val($_income_tax_percent);
    $('#woc_bags_income_tax_amount').val($_income_tax);
    $('#woc_bags_net_freight').val($_net_freight);
    $('#woc_bags_balance').val($_due_balance);

});


$(document).on('change','#CommodityContainers input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_containers_freight').val()) || 0;
    var $_deposit_charges = parseFloat($('#woc_containers_deposit_fee').val()) || 0;
    var $_lolo = parseFloat($('#woc_containers_lolo').val()) || 0;
    var $_empty_clariance = parseFloat($('#woc_containers_empty_clariance').val()) || 0;
    var $_detention_charges = parseFloat($('#woc_containers_detention_charges').val()) || 0;
    var $_other = parseFloat($('#woc_containers_other').val()) || 0;
    var $_other_charges = parseFloat($('#woc_containers_other_charges').val()) || 0;

    var $_total_freight = ($_freight + $_deposit_charges + $_lolo + $_empty_clariance + $_detention_charges + $_other  + $_other_charges).toFixed(2);

    var $_adv_payment = parseFloat($('#woc_containers_adv_payment').val()) || 0;
//    var $_fuel = parseFloat($('#woc_containers_fuel').val()) || 0;
//    var $_service_charges = parseFloat($('#woc_containers_service_charges').val()) || 0;
//    var $_commission = parseFloat($('#woc_containers_commission').val()) || 0;
//    var $_demmage = parseFloat($('#woc_containers_demmage').val()) || 0;
//    var $_line_detention = parseFloat($('#woc_containers_line_detention').val()) || 0;
//    var $_vhr_comm = parseFloat($('#woc_containers_vhr_comm').val()) || 0;
//    var $_texas = parseFloat($('#woc_containers_texas').val()) || 0;
//    var $_grand_total = (parseFloat($_adv_payment) + $_fuel + $_service_charges + $_commission + $_demmage + $_line_detention + $_vhr_comm + $_texas).toFixed(2);


    var $_sales_tax_percent = parseFloat($('#woc_containers_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total_freight) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total_freight) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_containers_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);
    var $_due_balance = parseFloat($_net_freight - $_adv_payment).toFixed(2) || 0;


    $('#woc_containers_freight').val($_freight);
    $('#woc_containers_deposit_fee').val($_deposit_charges);
    $('#woc_containers_lolo').val($_lolo);
    $('#woc_containers_other_charges').val($_other_charges);
    $('#woc_containers_total_frieght').val($_total_freight);
    $('#woc_containers_total').val($_grand_total);
    $('#woc_containers_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_containers_sales_tax_amount').val($_sales_tax);
    $('#woc_containers_grand_total').val($_grand_total);
    $('#woc_containers_income_tax_percent').val($_income_tax_percent);
    $('#woc_containers_income_tax_amount').val($_income_tax);
    $('#woc_containers_net_freight').val($_net_freight);
    $('#woc_containers_balance').val($_due_balance);

});

$(document).on('change','#CommodityPallets input.fDecimal', function() {
    var $_freight = parseFloat($('#woc_pallets_freight').val()) || 0;
    var $_stop_charges = parseFloat($('#woc_pallets_extra_stop_charges').val()) || 0;
    var $_over_wgt_charges = parseFloat($('#woc_pallets_over_wgt_charges').val()) || 0;
    var $_shifting_charges = parseFloat($('#woc_pallets_shifting_charges').val()) || 0;
    var $_station_charges = parseFloat($('#woc_pallets_extra_station_charges').val()) || 0;
    var $_detention_as_fact = parseFloat($('#woc_pallets_detention_at_fact').val()) || 0;
    var $_detention_as_cust = parseFloat($('#woc_pallets_detention_at_cust').val()) || 0;
    var $_other = parseFloat($('#woc_pallets_other').val()) || 0;
    var $_other_charges = parseFloat($('#woc_pallets_other_charges').val()) || 0;

    var $_total_freight = ($_freight + $_stop_charges + $_over_wgt_charges + $_shifting_charges + $_station_charges + $_detention_as_fact + $_detention_as_cust + $_other + $_other_charges).toFixed(2);

    var $_adv_payment = parseFloat($('#woc_pallets_adv_payment').val()) || 0;
//    var $_fuel = parseFloat($('#woc_pallets_fuel').val()) || 0;
//    var $_service_charges = parseFloat($('#woc_pallets_service_charges').val()) || 0;
//    var $_commission = parseFloat($('#woc_pallets_commission').val()) || 0;
//    var $_demmage = parseFloat($('#woc_pallets_demmage').val()) || 0;
//    var $_losses = parseFloat($('#woc_pallets_losses').val()) || 0;
//    var $_vhr_comm = parseFloat($('#woc_pallets_vhr_comm').val()) || 0;
//    var $_texas = parseFloat($('#woc_pallets_texas').val()) || 0;
//    var $_grand_total = (parseFloat($_adv_payment) + $_fuel + $_service_charges + $_commission + $_demmage + $_losses + $_vhr_comm + $_texas).toFixed(2);

    var $_sales_tax_percent = parseFloat($('#woc_pallets_sales_tax_percent').val()) || 0;
    var $_sales_tax = (parseFloat($_total_freight) * parseFloat($_sales_tax_percent) / 100).toFixed(2);
    var $_grand_total = (parseFloat($_total_freight) + parseFloat($_sales_tax)).toFixed(2);

    var $_income_tax_percent = parseFloat($('#woc_pallets_income_tax_percent').val()) || 0;
    var $_income_tax = (parseFloat($_grand_total) * parseFloat($_income_tax_percent) / 100).toFixed(2);
    var $_net_freight = (parseFloat($_grand_total) + parseFloat($_income_tax)).toFixed(2);
    var $_due_balance = parseFloat($_net_freight - $_adv_payment) || 0;


    $('#woc_pallets_freight').val($_freight);
    $('#woc_pallets_other_charges').val($_other_charges);
    $('#woc_pallets_total_frieght').val($_total_freight);
    $('#woc_pallets_total').val($_grand_total);
    $('#woc_pallets_sales_tax_percent').val($_sales_tax_percent);
    $('#woc_pallets_sales_tax_amount').val($_sales_tax);
    $('#woc_pallets_grand_total').val($_grand_total);
    $('#woc_pallets_income_tax_percent').val($_income_tax_percent);
    $('#woc_pallets_income_tax_amount').val($_income_tax);
    $('#woc_pallets_net_freight').val($_net_freight);
    $('#woc_pallets_balance').val($_due_balance);

});




