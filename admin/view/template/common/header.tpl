<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <base href="<?php echo $base; ?>"/>
    <title><?php echo $page_title; ?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bootstrap/css/font-awesome.min.css">
    <!-- Ionicons -->
    <!-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> -->
    <!-- jvectormap -->
    <!-- <link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css"> -->
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="plugins/fastclick/fastclick.min.js"></script>
    <script src="dist/js/app.js"></script>
    <script type="text/javascript">
        var $URLOpenFileManager = '<?php echo $action_open_file_manager; ?>';
        var $URLUploadFile = '<?php echo $action_upload_file; ?>';
        var $UrlGetPartner = '<?php echo $href_get_partner; ?>';

        var $UrlGetDocumentLedger = '<?php echo $href_get_document_ledger; ?>';
        var $UrlGetProductByCode = '<?php echo $href_get_product_by_code; ?>';
        var $UrlGetProductById = '<?php echo $href_get_product_by_id; ?>';
        var $UrlGetWarehouseStock = '<?php echo $href_get_warehouse_stock; ?>';
        var $UrlGetCustomerUnit = '<?php echo $href_get_customer_unit; ?>';
        var $UrlGetPartnerById = '<?php echo $href_get_partner_by_id; ?>';

        // setTimeout(function(){ location.reload(); }, 1200000);
        var $UrlRendomRequest = '<?php echo $href_random_request; ?>';
        setInterval(function(){
            $.ajax({
                url:$UrlRendomRequest,
                method:'post',
                beforeSend: function() {},
                complete: function() {},
                success: function(json) {
                    if(json.success){}
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(xhr.responseText);
                }
            });
        }, 300000);   
    </script>
    <script type="text/javascript" src="plugins/jquery.format.1.05.js"></script>
    <script type="text/javascript" src="plugins/jquery.maskedinput.js"></script>

    <script src="plugins/datetimepicker/moment.min.js" type="text/javascript"></script>
    <script src="plugins/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
    <link href="plugins/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />

    <link href="plugins/select2/select2.min.css" rel="stylesheet">
    <script type="text/javascript" src="plugins/select2/select2.min.js"></script>

    <script src="plugins/ckeditor/ckeditor.js" type="text/javascript"></script>

    <script src="plugins/bootbox.min.js" type="text/javascript"></script>
    <script type="text/javascript">

        $(document).ready(function () {
            setFieldFormat();
        });

        var roundUpto = function(number, upto){
            var $number = parseFloat(number);
            return Number($number.toFixed(upto));
        }

        function setInputFormat(){

            $('.dtpDate').datetimepicker({
                format: "<?php echo PICKER_DATE; ?>",
                pickTime: false,
                autoclose: true
            });

            $('.dtpDateTime').datetimepicker({
                format: "<?php echo PICKER_DATE_TIME; ?>",
                autoclose: true
            });

            $('.dtpTime').datetimepicker({
                format: "<?php echo PICKER_TIME; ?>",
                autoclose: true,
                pickDate: false
            });

            $('.fInteger').on('focus', function () {
                $(this).format({precision: 0,autofix:true});
            });
            $('.fPInteger').on('focus', function () {
                $(this).format({precision: 0,allow_negative:false,autofix:true});
            });
            $('.fDecimal').on('focus', function () {
                $(this).format({precision: 4,autofix:true});
            });
            $('.fPDecimal').on('focus', function () {
                $(this).format({precision: 2,allow_negative:false,autofix:true});
            });
            $('.fFloat').on('focus', function () {
                $(this).format({precision: 6,autofix:true});
            });
            $('.fPFloat').on('focus', function () {
                $(this).format({precision: 6,allow_negative:false,autofix:true});
            });
            $('.fEmail').on('focus', function () {
                $(this).format({type:"email"}, function () {
                    if ($(this).val() != "") alert("Wrong Email format!");
                });
            });
            $('.fString').on('focus', function () {
                $(this).format({type:"alphabet",autofix:true});
            });

            $(".fPhone").mask("+99 999 9999999");
            $(".fCNIC").mask("99999-9999999-9");

        }

        function setSelectFormat(){

            $('select').select2({ width: '100%' });
            
        }


        function setFieldFormat() {

            setInputFormat();
            setSelectFormat();
        }

        function ConfirmDelete(url, $post=0) {
            bootbox.dialog({
                message: "Are you sure you want to delete record? Record once deleted will not be retrieved back.",
                title: "Delete Record?",
                buttons: {
                    success: {
                        label: "Yes",
                        className: "btn-success",
                        callback: function() {
                            if($post==1) {
                                $('#form').attr('action', url);
                                $('#form').submit();
                            } else {
                                location.href=url;
                            }
                        }
                    },
                    danger: {
                        label: "No",
                        className: "btn-danger"
                    }
                }
            });
        }
    </script>
    <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/skin-blue.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.css">
    <!-- Modify CSS -->
    <link rel="stylesheet" href="dist/css/modify.css">
    <style>
        .fInteger,
        .fPInteger,
        .fDecimal,
        .fPDecimal,
        .fFloat,
        .fPFloat{
            text-align:right !important;
        }
        .select2-hidden-accessible{
            opacity: 0 !important;
            visibility: hidden !important;
        }
    </style>
</head>
