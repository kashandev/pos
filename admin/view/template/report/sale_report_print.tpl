<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
    <title><?php echo $title; ?></title>
    <base href="<?php echo $base; ?>" />
    <link rel="stylesheet" type="text/css" href="view/stylesheet/invoice.css" />
</head>
<body>
<div style="page-break-after: always;">
    <!-- <div><img src="view/image/hgh_logo.png" title="<?php echo $heading_project; ?>" style="text-align: center; width: 100%" /></div> -->
    <h1><?php echo $text_halar; ?></h1>
    <h2><?php echo $text_lab_receipt; ?></h2>
    <div id="heading">
    </div>
    <div id="content">
        <?php $total_discount=0; $total_tax=0; $total_other_expence=0; $total_amount=0; ?>
        <?php foreach($results as $group => $rows): ?>
        <div style="page-break-after: always;">
            <h3><?php echo $group; ?></h3>
            <table width="100%" class="product">
                <thead class="heading">
                <tr>
                    <td width="7%" align="center"><?php echo $column_s_no; ?></td>
                    <td width="7%" align="center"><?php echo $column_date; ?></td>
                    <td width="7%" align="center"><?php echo $column_invoice_no; ?></td>
                    <td width="7%" align="center"><?php echo $column_warehouse; ?></td>
                    <td width="7%" align="center"><?php echo $column_customer; ?></td>
                    <td width="7%" align="center"><?php echo $column_category; ?></td>
                    <td align="center"><?php echo $column_product; ?></td>
                    <td width="7%" align="center"><?php echo $column_qty; ?></td>
                    <td width="7%" align="center"><?php echo $column_rate; ?></td>
                    <td width="7%" align="center"><?php echo $column_discount; ?></td>
                    <td width="7%" align="center"><?php echo $column_tax; ?></td>
                    <td width="7%" align="center"><?php echo $column_other_expence; ?></td>
                    <td width="7%" align="center"><?php echo $column_amount; ?></td>
                </tr>
                </thead>
                <?php $discount=0; $tax=0; $other_expence=0; $amount=0; ?>
                <?php foreach($dates as $date => $rows): ?>
                <tbody>
                <?php $total_amount=0; $total_discount=0; ?>
                <?php $total_doctor_amount=0; $total_hospital_amount=0; ?>
                <?php foreach ($rows as $result): ?>
                <?php $row++; ?>
                <tr>
                    <td align="left"><?php echo $row; ?></td>
                    <td align="left"><?php echo $result['date']; ?></td>
                    <td align="left"><?php echo $result['slip_id']; ?></td>
                    <td align="left"><?php echo $result['patient_name']; ?></td>
                    <td align="left"><?php echo $result['phone']; ?></td>
                    <td align="left"><?php echo $result['service_name']; ?></td>
                    <td align="right"><?php echo number_format($result['discount'],2); ?></td>
                    <td align="right"><?php echo number_format($result['amount'],2); ?></td>
                    <td align="right"><?php echo number_format($result['hospital_amount'],2); ?></td>
                    <td align="right"><?php echo number_format($result['doctor_amount'],2); ?></td>
                    <td align="left"><?php echo $result['created_at']; ?></td>
                    <td align="left"><?php echo $result['user_name']; ?></td>
                </tr>
                <?php $total_amount += $result['amount']; ?>
                <?php $amount += $result['amount']; ?>
                <?php $total_doctor_amount += $result['doctor_amount']; ?>
                <?php $doctor_amount += $result['doctor_amount']; ?>
                <?php $total_hospital_amount += $result['hospital_amount']; ?>
                <?php $hospital_amount += $result['hospital_amount']; ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="6" align="right"><label><?php echo $date ?></label></td>
                    <td align="right"><strong><?php echo number_format($total_discount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($total_amount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($total_hospital_amount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($total_doctor_amount,2); ?></strong></td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                </tr>
                </tbody>
                <?php endforeach; ?>
                <tfoot>
                <tr>
                    <td colspan="6" align="right"><label><?php echo $doctor ?></label></td>
                    <td align="right"><strong><?php echo number_format($discount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($amount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($hospital_amount,2); ?></strong></td>
                    <td align="right"><strong><?php echo number_format($doctor_amount,2); ?></strong></td>
                    <td align="left">&nbsp;</td>
                    <td align="left">&nbsp;</td>
                </tr>
                </tfoot>
            </table>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</body>
<script type="text/javascript"><!--
    window.print();
    //--></script>
</html>