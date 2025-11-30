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
                    <h3><?php echo $lang['service_invoice']; ?></h3>
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
                <td style="width: 25%"><strong><?php echo $lang['document_no']; ?></strong>:&nbsp;<?php echo $document_no; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"><strong><?php echo $lang['document_date']; ?></strong>:&nbsp;<?php echo stdDate($document_date); ?></td>
            </tr>
            <tr>
                <td style="width: 25%"><strong><?php echo $lang['partner']; ?></strong>:&nbsp;<?php echo $partner_name; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"><strong><?php echo $lang['phone_no']; ?></strong>:&nbsp;<?php echo $phone_no; ?></td>
            </tr>
            <tr>
                <td style="width: 25%"><strong><?php echo $lang['address']; ?></strong>:&nbsp;<?php echo $address; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
            </tr>
            <tr>
                <td colspan="2"><strong><?php echo $lang['remarks']; ?></strong>:&nbsp;<?php echo $remarks; ?></td>
                <td style="width: 25%"></td>
                <td style="width: 25%"></td>
            </tr>
        </table>
        <table class="page_body" style="margin-top: 15px;">
            <thead>
            <tr>
                <th style="width: 77%;"><?php echo $lang['remarks']; ?></th>
                <th style="width: 23%;"><?php echo $lang['amount']; ?></th>
            </tr>
            </thead>
            <tbody>
            <?php $total_amount = 0.00; ?>
            <?php foreach($details as $detail): ?>
            <?php $total_amount += $detail['amount']; ?>
            <tr>
                <td><?php echo $detail['remarks']; ?></td>
                <td style="text-align: right;"><?php echo number_format($detail['amount'],2); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td style="text-align: right;"><strong><?php echo $lang['total']; ?></strong></td>
                <td style="text-align: right;"><strong><?php echo number_format($total_amount,2); ?></strong></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;"><?php echo Number2Words($total_amount); ?></td>
            </tr>
            </tbody>
        </table>
        <div style="text-align: right; padding: 80px 50px 0 0;"><?php echo $company['name']; ?></div>
    </div>
</page>
