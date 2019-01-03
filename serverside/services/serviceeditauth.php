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
   <label class="w3-label">Password: </label><input class="w3-input w3-border" type="text" name="password"  size="20" required="required"  maxlength="20" /> <br/>
    If you forgot your password, send an email to <a href="mailto:support@anderson-island.org">support@anderson-island.org</a><br/>
<br />
    <input type="hidden" name="business" value="<?php echo $_GET['business']; ?>" />
    <input type="submit" value="Next" />

</form>
</div>
</body>
</html>
