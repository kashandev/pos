<?php echo $header; ?>
<?php echo $column_left; ?>


<div class="pageheader">
    <h2><i class="fa fa-table"></i> <?php echo $heading_title; ?></h2>

</div>

<div id="page-wrapper">
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_chart_of_account; ?></a>
                                    </h4>
                                </div>
                                <div class="panel-collapse in" id="collapseOne" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $coa_level1; ?>"><?php echo $lang['coa_level1']; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $coa_level2; ?>"><?php echo $lang['coa_level2']; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $coa_level3; ?>"><?php echo $lang['coa_level3']; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $copy_coa; ?>"><?php echo $lang['copy_coa']; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $mapping_account; ?>"><?php echo $lang['mapping_account']; ?></a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_transaction; ?></a>
                                    </h4>
                                </div>
                                <div class="panel-collapse in" id="collapseTwo" style="height: auto;">

                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $opening_account; ?>"><?php echo $text_opening_account; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $bank_payment; ?>"><?php echo $text_bank_payment; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $cash_payment; ?>"><?php echo $text_cash_payment; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $bank_receipt; ?>"><?php echo $text_bank_receipt; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $cash_receipt; ?>"><?php echo $text_cash_receipt; ?></a></div>
                                            <div class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;<a href="<?php echo $journal_voucher; ?>"><?php echo $text_journal_voucher; ?></a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a href="#collapseThree" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $text_advances; ?></a>
                                    </h4>
                                </div>
                                <div class="panel-collapse in" id="collapseThree" style="height: auto;">
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-3"> <span class="iccon" style="background-image: url('view/image/icons/advance_payment.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $advance_payment; ?>"><?php echo $text_advance_payment; ?></a></div>
                                            <div class="col-md-3"> <span class="iccon" style="background-image: url('view/image/icons/advance_receipt.png');">&nbsp;</span>&nbsp;&nbsp;<a href="<?php echo $advance_receipt; ?>"><?php echo $text_advance_receipt; ?></a></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>