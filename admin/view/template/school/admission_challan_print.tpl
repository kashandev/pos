<table style="width:100%; font-size: 16px;">
    <tr>
        <td style="width: 33%; border-right: 1px dotted #808080; text-align: center;padding: 10px 20px;">
            <table style="width:100%;">
                <tr>
                    <td style="width:33%; text-align: left;">Challan No:<strong><?php echo $challan['challan_identity']; ?></strong></td>
                    <td style="width:33%; text-align: center;">Bank Voucher</td>
                    <td style="width:33%; text-align: right;"><strong>Bank Copy</strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><strong><?php echo$config['bank_name']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><u><?php echo nl2br($config['bank_address']); ?></u></td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Credit: <strong><?php echo $config['bank_account_title']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Acc. No.: <strong><?php echo $config['bank_account_no']; ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border-radius: 15px; border: 1px solid #CDCDCD;padding:10px; margin-top: 20px;">
                <tr>
                    <td style="width:40%; text-align:left">Month:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['challan_title']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">REG No:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['preregistration_identity']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['student_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Father Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['father_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Sur Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['sur_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Class:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['class_name']; ?></td>
                </tr>
            </table>
            <table style="width:100%;padding:10px; margin-top: 20px;">
                <?php $total_current_amount = 0; ?>
                <?php foreach($challan_details as $challan_detail): ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $challan_detail['fee_name']; ?>:</td>
                    <td style="width:30%; text-align:right;"><?php echo number_format($challan_detail['challan_amount'],2); ?></td>
                </tr>
                <?php $total_current_amount += $challan_detail['challan_amount']; ?>
                <?php endforeach; ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $lang['within_due_date']; ?>:</td>
                    <td style="width:30%; text-align:right; border-top: 1px solid #CDCDCD;"><?php echo number_format($total_current_amount,2); ?></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['issue_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['due_month']); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['last_date']); ?></strong></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['late_payment']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($config['late_fee_amount'],2); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['after_due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($total_current_amount+$config['late_fee_amount'],2); ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border: 1px solid #000;margin-top: 20px;">
                <tr>
                    <td style="width: 100%; text-align: center;">This Challan is Valid Till: <b><?php echo stdDate($challan['validity_date']); ?></b></td>
                </tr>
            </table>
        </td>
        <td style="width: 33%; border-right: 1px dotted #808080; text-align: center;padding: 10px 20px;">
            <table style="width:100%;">
                <tr>
                    <td style="width:33%; text-align: left;">Challan No:<strong><?php echo $challan['challan_identity']; ?></strong></td>
                    <td style="width:33%; text-align: center;">Bank Voucher</td>
                    <td style="width:33%; text-align: right;"><strong>School Copy</strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><strong><?php echo$config['bank_name']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><u><?php echo nl2br($config['bank_address']); ?></u></td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Credit: <strong><?php echo $config['bank_account_title']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Acc. No.: <strong><?php echo $config['bank_account_no']; ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border-radius: 15px; border: 1px solid #CDCDCD;padding:10px; margin-top: 20px;">
                <tr>
                    <td style="width:40%; text-align:left">Month:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['challan_title']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">REG No:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['preregistration_identity']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['student_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Father Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['father_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Sur Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['sur_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Class:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['class_name']; ?></td>
                </tr>
            </table>
            <table style="width:100%;padding:10px; margin-top: 20px;">
                <?php $total_current_amount = 0; ?>
                <?php foreach($challan_details as $challan_detail): ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $challan_detail['fee_name']; ?>:</td>
                    <td style="width:30%; text-align:right;"><?php echo number_format($challan_detail['challan_amount'],2); ?></td>
                </tr>
                <?php $total_current_amount += $challan_detail['challan_amount']; ?>
                <?php endforeach; ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $lang['within_due_date']; ?>:</td>
                    <td style="width:30%; text-align:right; border-top: 1px solid #CDCDCD;"><?php echo number_format($total_current_amount,2); ?></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['issue_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['due_month']); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['last_date']); ?></strong></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['late_payment']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($config['late_fee_amount'],2); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['after_due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($total_current_amount+$config['late_fee_amount'],2); ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border: 1px solid #000;margin-top: 20px;">
                <tr>
                    <td style="width: 100%; text-align: center;">This Challan is Valid Till: <b><?php echo stdDate($challan['validity_date']); ?></b></td>
                </tr>
            </table>
        </td>
        <td style="width: 33%; border-right: 1px dotted #808080; text-align: center;padding: 10px 20px;">
            <table style="width:100%;">
                <tr>
                    <td style="width:33%; text-align: left;">Challan No:<strong><?php echo $challan['challan_identity']; ?></strong></td>
                    <td style="width:33%; text-align: center;">Bank Voucher</td>
                    <td style="width:33%; text-align: right;"><strong>Parent Copy</strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><strong><?php echo$config['bank_name']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3"><u><?php echo nl2br($config['bank_address']); ?></u></td>
                </tr>
                <tr><td>&nbsp;</td></tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Credit: <strong><?php echo $config['bank_account_title']; ?></strong></td>
                </tr>
                <tr>
                    <td style="width: 100%;text-align: center;" colspan="3">Acc. No.: <strong><?php echo $config['bank_account_no']; ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border-radius: 15px; border: 1px solid #CDCDCD;padding:10px; margin-top: 20px;">
                <tr>
                    <td style="width:40%; text-align:left">Month:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['challan_title']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">REG No:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['preregistration_identity']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['student_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Father Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['father_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Sur Name:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['sur_name']; ?></td>
                </tr>
                <tr>
                    <td style="width:40%; text-align:left">Class:</td>
                    <td style="width:60%; text-align:left"><?php echo $challan['class_name']; ?></td>
                </tr>
            </table>
            <table style="width:100%;padding:10px; margin-top: 20px;">
                <?php $total_current_amount = 0; ?>
                <?php foreach($challan_details as $challan_detail): ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $challan_detail['fee_name']; ?>:</td>
                    <td style="width:30%; text-align:right;"><?php echo number_format($challan_detail['challan_amount'],2); ?></td>
                </tr>
                <?php $total_current_amount += $challan_detail['challan_amount']; ?>
                <?php endforeach; ?>
                <tr>
                    <td style="width:70%; text-align:left;"><?php echo $lang['within_due_date']; ?>:</td>
                    <td style="width:30%; text-align:right; border-top: 1px solid #CDCDCD;"><?php echo number_format($total_current_amount,2); ?></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['issue_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['due_month']); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo stdDate($challan['last_date']); ?></strong></td>
                </tr>
                <tr><td colspan="3">&nbsp;</td></tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['late_payment']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($config['late_fee_amount'],2); ?></strong></td>
                </tr>
                <tr>
                    <td style="width:70%; text-align:left;"><strong><?php echo $lang['after_due_date']; ?>:</strong></td>
                    <td style="width:30%; text-align:right;"><strong><?php echo number_format($total_current_amount+$config['late_fee_amount'],2); ?></strong></td>
                </tr>
            </table>
            <table style="width:100%;border: 1px solid #000;margin-top: 20px;">
                <tr>
                    <td style="width: 100%; text-align: center;">This Challan is Valid Till: <b><?php echo stdDate($challan['validity_date']); ?></b></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
