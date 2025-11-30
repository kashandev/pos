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
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><?php echo $lang['from_date']; ?></label>
                                                        <input type="text" id="from_date" name="from_date" class="form-control dtpDate" value="<?php echo $from_date; ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><?php echo $lang['to_date']; ?></label>
                                                        <input type="text" id="to_date" name="to_date" class="form-control dtpDate" value="<?php echo $to_date; ?>" />
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><?php echo $lang['event']; ?></label>
                                                        <select id="event_id" name="event_id" class="form-control">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($events as $event): ?>
                                                            <option value="<?php echo $event['event_id']; ?>"><?php echo $event['event_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><?php echo $lang['ground_area']; ?></label>
                                                        <select id="ground_area_id" name="ground_area_id" class="form-control">
                                                            <option value="">&nbsp;</option>
                                                            <?php foreach($ground_areas as $area): ?>
                                                            <option value="<?php echo $area['ground_area_id']; ?>"><?php echo $area['ground_area']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
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
                                                            <a class="list-group-item" data-column_name="event_name" data-display_name="Event Name" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Event Name</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="booking_date" data-display_name="Booking Date" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Booking Date</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="booking_identity" data-display_name="Booking No." href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Booking No.</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="event_date" data-display_name="Event Date" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Event Date</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="event_day" data-display_name="Event Day" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Event Day</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="from_time" data-display_name="From Time" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">From Time</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="to_time" data-display_name="To Time" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">To Time</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="total_hours" data-display_name="Total Hours" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Total Hours</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="ground_area" data-display_name="Ground Area" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Ground Area</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="member_name" data-display_name="Member Name" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Member Name</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="mobile_no" data-display_name="Mobile No." href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Mobile No.</p>
                                                            </a>
                                                            <a class="list-group-item" data-column_name="amount" data-display_name="Amount" href="javascript:void(0);">
                                                                <span onclick="fnAddToList(this);" class="badge bg-yellow"><i class="fa fa-arrow-right"></i></span>
                                                                <p class="list-group-item-text">Amount</p>
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
                                            <div id="divTable" class="table-responsive">

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
    <script type="text/javascript" src="dist/js/pages/report/ground_schedule.js"></script>
    <script type="text/javascript">
        var $UrlFilterReport = '<?php echo $action_filter_ground_schedule; ?>';
        var $UrlPdfReport = '<?php echo $action_pdf_ground_schedule; ?>';
        var $UrlExcelReport = '<?php echo $action_excel_ground_schedule; ?>';

        var $UrlSaveReport = '<?php echo $action_save_ground_schedule; ?>';
        var $UrlRemoveReport = '<?php echo $action_remove_ground_schedule; ?>';
        var $UrlLoadReport = '<?php echo $action_load_ground_schedule; ?>';
    </script>
    <?php echo $page_footer; ?>
    <?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>