<style type="text/css">
    body {
        background: #FFFFFF;
    }
    body, td, th, input, select, textarea, option, optgroup {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        color: #000000;
    }
    h1 {
        text-transform: uppercase;
        text-align: center;
        font-size: 24px;
        font-weight: normal;
        margin: 5px 0;
    }
    h2 {
        text-transform: uppercase;
        text-align: center;
        font-size: 18px;
        font-weight: normal;
        padding: 0;
        margin: 0;
    }
    h3 {
        text-align: center;
        font-size: 16px;
        font-weight: normal;
        padding: 0;
        margin: 5px 0 0 0;
    }
    table.page_header {width: 100%; padding: 2mm; }
    table.page_header td {border: solid 4px transparent; }
    table.page_body {width: 100%; border: solid 1px #DDDDDD; border-collapse: collapse; align="center" }
    table.page_body th {border: solid 1px #000000; border-collapse: collapse; background-color: #CDCDCD; text-align: center; font-size: 12px; padding: 5px;}
    table.page_body td {border: solid 1px #000000; border-collapse: collapse;font-size: 10px; padding: 5px;}
    table.page_footer {width: 100%; padding: 2mm}
</style>

<page backtop="30mm" backbottom="14mm" backleft="1%" backright="1%" style="font-size: 12pt">
    <page_header>
        <table class="page_header">
            <tr>
                <td style="width: 33%; text-align: left;">
                    <?php if($company_logo): ?>
                    <img src="<?php echo $company_logo; ?>" alt="Logo" />
                    <?php else: ?>
                    &nbsp;
                    <?php endif; ?>
                </td>
                <td style="width: 34%; text-align: center">
                    <h1><?php echo $company['name']; ?></h1>
                    <h2><?php echo $company_branch['name']; ?></h2>
                    <h3><?php echo $lang['heading_title']; ?></h3>
                </td>
                <td style="width: 33%;">
                    <table>
                        <tr>
                            <td style="text-align: right; font-weight: bold"><?php echo $lang['entry_from_date']; ?></td><td style="text-align: left;"><?php echo $filter['date_from']; ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold"><?php echo $lang['entry_to_date']; ?></td><td style="text-align: left;"><?php echo $filter['date_to']; ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold"><?php echo $lang['entry_warehouse']; ?></td><td style="text-align: left;"><?php echo $arrWarehouses[$filter['warehouse_id']] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold"><?php echo $lang['entry_product_category']; ?></td><td style="text-align: left;"><?php echo $arrProductCategories[$filter['product_category_id']] ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right; font-weight: bold"><?php echo $lang['entry_product']; ?></td><td style="text-align: left;"><?php echo $arrProducts[$filter['product_id']] ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </page_header>
    <page_footer>
        <table class="page_footer">
            <tr>
                <td style="width: 33%; text-align: left;">
                    &nbsp;
                </td>
                <td style="width: 34%; text-align: center">
                    &nbsp;
                </td>
                <td style="width: 33%; text-align: right">
                    Date: <?php echo date(STD_DATE); ?>
                </td>
            </tr>
        </table>
    </page_footer>
    <div>
        <table class="page_header" >
            <tr>
                <td style="width: 25%"><strong><?php echo $lang['entry_voucher_no']; ?></strong>&nbsp;<?php echo $document_identity; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"><strong><?php echo $lang['entry_voucher_date']; ?></strong>&nbsp;<?php echo stdDate($document_date); ?></td>
            </tr>
            <tr>
                <td style="width: 25%"><strong><?php echo $lang['entry_customer_id']; ?></strong>&nbsp;<?php echo $people_type; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"><strong><?php echo $lang['entry_phone_no']; ?></strong>&nbsp;<?php echo $phone_no; ?></td>

            </tr>
            <tr>
                <td style="width: 25%"><strong><?php echo $lang['entry_address']; ?></strong>&nbsp;<?php echo $address; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"><strong><?php echo $lang['entry_bill_t_number']; ?></strong>&nbsp;<?php echo $builty_no; ?></td>
            </tr>
        </table>
        <table class="page_body" style="margin-top: 15px;">
            <thead>
            <tr>
                <th style="width: 12%;"><?php echo $lang['column_product_code']; ?></th>
                <th style="width: 30%;"><?php echo $lang['column_name']; ?></th>
                <th style="width: 10%;"><?php echo $lang['column_unit']; ?></th>
                <th style="width: 10%;"><?php echo $lang['column_quantity']; ?></th>
                <th style="width: 8%;"><?php echo $lang['column_rate']; ?></th>
                <th style="width: 10%;"><?php echo $lang['column_tax']; ?></th>
                <th style="width: 10%;"><?php echo $lang['column_discount_amount']; ?></th>
                <th style="width: 10%;"><?php echo $lang['column_net_amount']; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $total_amount = 0; ?>
            <?php foreach($details as $detail): ?>
            <?php $total_amount += $detail['total']; ?>
            <tr>
                <td><?php echo $detail['product_code']; ?></td>
                <td><?php echo $detail['product_id']; ?></td>
                <td><?php echo $detail['unit_id']; ?></td>
                <td><?php echo $detail['qty']; ?></td>
                <td><?php echo $detail['rate']; ?></td>
                <td style="text-align: right;"><?php echo $detail['tax_amount']; ?></td>
                <td style="text-align: right;"><?php echo $detail['discount_amount']; ?></td>
                <td style="text-align: right;"><?php echo number_format($detail['total'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="6"> <?php echo Number2Words($total_amount); ?> </td>
                <td colspan="1"><strong> <?php echo $lang['entry_total']; ?></strong> </td>
                <td style="text-align: right;"><strong><?php echo number_format($total_amount,2); ?></strong></td>

            </tr>


            </tbody>

        </table>

        <div style="text-align: right; padding: 80px 50px 0 0;"><?php echo $company['name']; ?></div>


    </div>

</page>
