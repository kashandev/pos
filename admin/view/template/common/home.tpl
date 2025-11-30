<!DOCTYPE html>
<html>
<?php echo $header; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php echo $page_header; ?>
<?php echo $column_left; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Dashboard</h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
  </section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <a style="font-size: 20px;font-weight: bold" href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class=""><?php echo $lang['insert_link']; ?></a>
                    </h4>
                </div>
                <div class="panel-collapse in" id="collapseOne" style="height: auto;">
                    <div class="panel-body">
                        <div class="row">
                            <div style="font-size: 16px" class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo $inventory_sale_tax_invoice; ?>"><?php echo $lang['inventory_sale_tax_invoice']; ?></a></div>
                            <div style="font-size: 16px" class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo $inventory_purchase_invoice; ?>"><?php echo $lang['inventory_purchase_invoice']; ?></a></div>
                            <div style="font-size: 16px" class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo $party_ledger_report; ?>"><?php echo $lang['party_ledger_report']; ?></a></div>
                            <!-- <div style="font-size: 16px" class="col-md-3"><i class="fa fa-arrow-right fa-lg"></i>&nbsp;&nbsp;<a target="_blank" href="<?php echo $inventory_sale_invoice; ?>"><?php echo $lang['inventory_sale_invoice']; ?></a></div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
    

<!---Graph Start--->
<div class="row">
    <div class="col-md-12" id="container" >

    </div>
</div>
<hr>
<div class="row">
    <div class="col-md-12" id="container1" >

    </div>
</div>

<!-- Graph End -->
    
    


</section><!-- /.content -->
</div>
<!-- /.content-wrapper -->
<script type="text/javascript">

    var $UrlGetSaleMonthChart = '<?php echo $href_get_sale_month_chart; ?>';
    var $UrlGettop5customers = '<?php echo $href_get_top_5_customers; ?>';z

</script>

<script src="plugins/highcharts500/highcharts.js"></script>
<script src="plugins/canvasjs/canvasjs.js"></script>

<script type="text/javascript" src="../admin/view/js/home.js"></script>

<?php echo $page_footer; ?>
<?php echo $column_right; ?>
</div><!-- ./wrapper -->
<?php echo $footer; ?>
</body>
</html>