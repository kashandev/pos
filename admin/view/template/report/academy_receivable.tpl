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
    </div>
</section>
<!-- Main content -->
<section class="content">
<div class="row">
<div class="col-sm-12">
<div id="accordion" class="box-group">
<div class="panel panel-info">
<div class="panel-heading">
    <h4 class="box-title">
        <a id="panelFilter" href="#collapseFilter" data-parent="#accordion" data-toggle="collapse" aria-expanded="false" class="collapsed">
            Filter
        </a>
    </h4>
</div>
<div class="panel-collapse collapse in" id="collapseFilter" aria-expanded="false" >
    <div class="panel-body">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo $lang['sport']; ?></label>
                            <select id="sport_id" name="sport_id" class="form-control">
                                <option value="">&nbsp;</option>
                                <?php foreach($sports as $sport): ?>
                                <option value="<?php echo $sport['sport_id']; ?>"><?php echo $sport['sport_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo $lang['mohallah']; ?></label>
                            <select id="mohallah_id" name="mohallah_id" class="form-control">
                                <option value="">&nbsp;</option>
                                <?php foreach($mohallahs as $mohallah): ?>
                                <option value="<?php echo $mohallah['mohallah_id']; ?>"><?php echo $mohallah['mohallah_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo $lang['member']; ?></label>
                            <select id="member_id" name="member_id" class="form-control">
                                <option value="">&nbsp;</option>
                                <?php foreach($members as $member): ?>
                                <option value="<?php echo $member['member_id']; ?>"><?php echo $member['member_name'].' ['.$member['mobile_no'].']'; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="balance_only" name="balance_only" value="balance_only"><?php echo $lang['balance_only']; ?>
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="receipt_only" name="receipt_only" value="receipt_only"><?php echo $lang['receipt_only']; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div id="_available_column" class="panel panel-default">
                            <div class="panel-heading">
                                <label class="panel-title"><?php echo $lang['available_column']; ?></label>
                            </div>
                            <div class="panel-body list-group" style="height: 350px; overflow-y: auto;">
                                <a class="list-group-item" data-column_name="sport_name" data-display_name="Sport Name" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Sport Name</p>
                                </a>
                                <a class="list-group-item" data-column_name="activity_name" data-display_name="Activity Name" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Activity Name</p>
                                </a>
                                <a class="list-group-item" data-column_name="mohallah_name" data-display_name="Mohallah Name" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Mohallah Name</p>
                                </a>
                                <a class="list-group-item" data-column_name="member_name" data-display_name="Member Name" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Member Name</p>
                                </a>
                                <a class="list-group-item" data-column_name="mobile_no" data-display_name="Member Mobile" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Member Mobile</p>
                                </a>
                                <a class="list-group-item" data-column_name="email" data-display_name="Member Email" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Member Email</p>
                                </a>
                                <a class="list-group-item" data-column_name="reg_no" data-display_name="Member Reg. No." href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Member Reg. No.</p>
                                </a>
                                <a class="list-group-item" data-column_name="receivable_date" data-display_name="Receivable Month" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Receivable Month</p>
                                </a>
                                <a class="list-group-item" data-column_name="activity_charges" data-display_name="Activity Charges" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Activity Charges</p>
                                </a>
                                <a class="list-group-item" data-column_name="member_amount" data-display_name="Member Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Member Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="donor_amount" data-display_name="Donor Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Donor Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="discount_amount" data-display_name="Discount Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Discount Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="receivable_amount" data-display_name="Receivable Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Receivable Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="receipt_amount" data-display_name="Receipt Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Receipt Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="balance_amount" data-display_name="Balance Amount" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Balance Amount</p>
                                </a>
                                <a class="list-group-item" data-column_name="receipt_date" data-display_name="Receipt Date" href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Receipt Date</p>
                                </a>
                                <a class="list-group-item" data-column_name="receipt_identity" data-display_name="Receipt No." href="javascript:void(0);">
                                    <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                    <p class="list-group-item-text">Receipt No.</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div id="_display_column" class="panel panel-default">
                            <div class="panel-heading">
                                <label class="panel-title"><?php echo $lang['display_column']; ?></label>
                                <button onclick="$(this).parent().siblings('.panel-body').html('');" class="btn btn-info btn-xs pull-right"><i class="fa fa-refresh"></i></button>
                            </div>
                            <div class="panel-body list-group" style="height: 350px; overflow-y: auto;"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><?php echo $lang['report_title']; ?></label>
                            <input type="text" id="report_title" name="report_title" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label><?php echo $lang['page_size']; ?></label>
                            <select id="page_size" name="page_size" class="form-control">
                                <option value="A4">A4</option>
                                <option value="A3">A3</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php echo $lang['page_orientation']; ?></label>
                            <select id="page_orientation" name="page_orientation" class="form-control">
                                <option value="P">Portrait</option>
                                <option value="L">Landscape</option>
                            </select>
                        </div>
                        <div id="_load_report" class="panel panel-default">
                            <div class="panel-heading">
                                <label class="panel-title"><?php echo $lang['load_report']; ?></label>
                            </div>
                            <div class="panel-body list-group" style="height: 128px; overflow-y: auto;">
                                <?php foreach($reports as $report): ?>
                                <a class="list-group-item" data-report_criteria_id="<?php echo $report['report_criteria_id']; ?>" data-report_id="<?php echo $report['report_id']; ?>" href="javascript:void(0);">
                                    <button onclick="removeReport(this)" class="btn btn-danger btn-xs pull-right">
                                        <i class="fa fa-remove"></i>
                                    </button>
                                    <p class="list-group-item-text"><?php echo $report['report_title']; ?></p>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <button id="btnFilter" type="button" class="btn btn-primary form-control">Filter</button>
                        </div>
                        <div class="form-group">
                            <button id="btnPDF" type="button" class="btn btn-default form-control">PDF</button>
                        </div>
                        <div class="form-group">
                            <button id="btnExcel" type="button" class="btn btn-default form-control">Excel</button>
                        </div>
                        <div class="form-group">
                            <button id="btnSave" type="button" class="btn btn-info form-control">Save Report</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="panel panel-success">
    <div class="panel-heading">
        <h4 class="box-title">
            <a id="panelResult" href="#collapseResult" data-parent="#accordion" data-toggle="collapse" aria-expanded="false" class="collapsed">
                Result
            </a>
        </h4>
    </div>
    <div class="panel-collapse collapse" id="collapseResult" aria-expanded="false" style="height: 0px;">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div id="divTable">

                    </div>
                </div>
            </div>
        </div>
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
<script type="text/javascript" src="dist/js/pages/report/academy_receivable.js"></script>
<script type="text/javascript">
    var $UrlFilterReport = '<?php echo $action_filter_academy_receivable; ?>';
    var $UrlPdfReport = '<?php echo $action_pdf_academy_receivable; ?>';
    var $UrlExcelReport = '<?php echo $action_excel_academy_receivable; ?>';

    var $UrlSaveReport = '<?php echo $action_save_academy_receivable; ?>';
    var $UrlRemoveReport = '<?php echo $action_remove_academy_receivable; ?>';
    var $UrlLoadReport = '<?php echo $action_load_academy_receivable; ?>';
</script>
<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>