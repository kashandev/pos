<?php 
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $server = $_POST['server'];
        $expiry_date = $_POST['expiry_date'];
        
        $arrKey = array(
            'server' => $server,
            'expiry_date' => $expiry_date,
            );
        
        $security_key = base64_encode(serialize($arrKey));
    } else {
        $server = $_SERVER['HTTP_HOST'];
        $expiry_date = "";
        $security_key = "";
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Generate Security Key</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
        <div>
            <form action="security_key.php" method="post" enctype="multipart/form-data" id="form">
                <table>
                    <tr>
                        <td>Server Name:</td>
                        <td><input type="text" id="server" name="server" value="<?php echo $server; ?>" /></td>
                    </tr>
                    <tr>
                        <td>Expiry Date:</td>
                        <td><input type="text" id="expiry_date" name="expiry_date" value="<?php echo $expiry_date; ?>" /></td>
                    </tr>
                    <tr>
                        <td><input type="submit" id="get_key" name="get_key" value="Get Key" /></td>
                        <td><input type="text" id="security_key" name="security_key" value="<?php echo $security_key; ?>" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
