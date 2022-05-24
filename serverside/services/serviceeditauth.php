<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, height=device-height, target-densitydpi=medium-dpi" />
    <meta http-equiv="Cache-Control" content="no-cache" />
    <title>Service Edit Authentication</title>
    <link rel="stylesheet" href="lib/w3.css" />
    <style>
    </style>
</head>
<body>
    <div class="w3-container" style="background-color:antiquewhite">
        <h2 class="w3-brown w3-text-white">SERVICE LISTING AUTHENTICATION</h2>
        <p>
            To update your business listing, please enter your password below.
        </p>

<form class="w3-container" name="business" method="post" action="serviceupdate.php">
    <b>
    <?php echo $_GET['business']; ?> 
    </b><br/><br/>
   <label class="w3-label">Password: </label><input class="w3-input w3-border" type="text" name="password"  id="password" size="20" required="required"  maxlength="20" /> <br/>
    </label><input class="w3-check w3-border" type="checkbox" name="forgotpw" id="forgotpw" value="yes" onclick="DeleteM()"/>I forgot my password. (An email with your password will be sent to your email address of record) <br/><br/>
<br />
    <input type="hidden" name="id" value="<?php echo $_GET['id']; ?>" />
    <input type="submit" value="Next" />

</form>
</div>
<script>
    /////////////////////////////////////////////////////////////////////////
    //  DeleteM - fill in the pw
        function DeleteM() {
        if(document.getElementById('forgotpw').checked == true) {
            document.getElementById('password').value = "X";
            return;
        }

    }
</script>
</body>
</html>
