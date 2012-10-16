<?php
if (isset($_GET[finishOTA]))
{
    echo "<font color=lime>" . $strings["upgrade-ota-downloaded"] . "</font>";
    $md5 = explode("|", trim(@file_get_contents("http://cloud.wifipineapple.com/index.php?downloads&currentVersion")));
    if (exec("md5sum /tmp/upgrade.bin | grep -w " . $md5[1]) != "")
    {

        $message = "<font color=lime>" . $strings["upgrade-working"] . "</font><br /><img src=\"includes/upgrade.gif\">";
        exec("echo \"<?php echo 'working'; ?>\" > /pineapple/upgrade/upgradeStatus.php");

        echo "
                                <script type='text/javascript' src='includes/jquery.min.js'></script>
                                <script type='text/javascript'>

                                $.ajax({
                                  url: 'upgrade/doUpgrade.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                  }
                                });

                                var loop=self.setInterval('checkUpgrade()',5000);

                                function checkUpgrade(){

                                $.ajax({
                                  url: 'upgrade/upgradeStatus.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                        if(response != 'working') window.location = 'index.php?upgrade&done';
                                  }
                                });

                                }
                                </script>";
    }
    else
        echo "<font color=red>" . $strings["upgrade-ota-md5"] . "</font>";
}

if (isset($_GET[doOTA]))
{

    $connection = @file_get_contents("http://cloud.wifipineapple.com/ip.php");
    if (trim($connection) != "")
    {
        echo "<font color=lime>" . $strings["upgrade-ota-downloading"] . "</font><br /><br />";
        exec("echo \"<?php echo 'working'; ?>\" > /pineapple/upgrade/otaStatus.php");
        echo "
                                <script  type='text/javascript' src='includes/jquery.min.js'></script>
                                <script type='text/javascript'>

                                $.ajax({
                                  url: 'upgrade/doOTA.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                  }
                                });

                                var loop=self.setInterval('checkUpgrade()',5000);

                                function checkUpgrade(){

                                $.ajax({
                                  url: 'upgrade/otaStatus.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                        if(response != 'working') window.location = 'index.php?upgrade&finishOTA';
                                  }
                                });

                                }

                                </script>";
    }
    else
        echo "<font color=red>" . $strings["upgrade-ota-connectionError"] . "</font>";
}

if (isset($_GET[checkUpgrade]))
{
    $remoteFile = explode("|", trim(@file_get_contents("http://cloud.wifipineapple.com/index.php?downloads&currentVersion")));
    $remoteMD5 = $remoteFile[1];
    $remoteVersion = explode(".", $remoteFile[0]);
    $localVersion = explode(".", file_get_contents("includes/fwversion"));

    if (trim($remoteFile[0]) == "")
    {
        echo "<font color=red>" . $strings["upgrade-connectError"] . "</font><br /><br />";
    }
    else
    {
        if ($remoteVersion[0] > $localVersion[0])
        {
            $upgradeMessage = $strings["upgrade-found"] . " ($remoteVersion[0].$remoteVersion[1].$remoteVersion[2]) | <a href=\"?upgrade&doOTA\">" . $strings["upgrade-doUpgrade"] . "</a>";
        }
        else if ($remoteVersion[0] == $localVersion[0])
        {
            if ($remoteVersion[1] > $localVersion[1])
            {
                $upgradeMessage = $strings["upgrade-found"] . " ($remoteVersion[0].$remoteVersion[1].$remoteVersion[2]) | <a href=\"?upgrade&doOTA\">" . $strings["upgrade-doUpgrade"] . "</a>";
            }
            else if ($remoteVersion[1] == $localVersion[1])
            {
                if ($remoteVersion[2] > $localVersion[2])
                {
                    $upgradeMessage = $strings["upgrade-found"] . " ($remoteVersion[0].$remoteVersion[1].$remoteVersion[2]) | <a href=\"?upgrade&doOTA\">" . $strings["upgrade-doUpgrade"] . "</a>";
                }
                else
                    $upgradeMessage = $strings["upgrade-notFound"] . " <a href=\"?upgrade&doOTA\">" . $strings["upgrade-reflash"] . " $remoteFile[0]?</a>";
            }
            else
                $upgradeMessage = $strings["upgrade-notFound"] . " <a href=\"?upgrade&doOTA\">" . $strings["upgrade-reflash"] . " $remoteFile[0]?</a>";
        }
        else
            $upgradeMessage = $strings["upgrade-notFound"] . " <a href=\"?upgrade&doOTA\">" . $strings["upgrade-reflash"] . " $remoteFile[0]?</a>";
    }
}

if (isset($_GET[done]))
{
    $message = "<font color=lime>" . $strings["upgrade-complete"] . "</font>";
}

if (isset($_POST[doUpgrade]))
{
    if ($_FILES[upgrade][error] > 0)
    {
        $error = $_FILES[upgrade][error];
        $message = $strings["upgrade-fileError"] . " Error $error";
    }
    else if (isset($_FILES[upgrade]) && !checkname())
    {
        $message = $strings["upgrade-nameError"];
    }
    else if (isset($_FILES[upgrade]))
    {
        exec("rm /tmp/upgrade*.bin");
        move_uploaded_file($_FILES[upgrade][tmp_name], "/tmp/upgrade.bin");
        if (exec("md5sum /tmp/upgrade.bin | grep -w " . trim($_POST[md5sum])) == "")
        {
            $message = $strings["upgrade-md5Error"];
        }
        else
        {
            $message = "<font color=lime>" . $strings["upgrade-working"] . "</font><br /><img src=\"includes/upgrade.gif\">";
            exec("echo \"<?php echo 'working'; ?>\" > /pineapple/upgrade/upgradeStatus.php");
            echo "
                                <script  type='text/javascript' src='includes/jquery.min.js'></script>
                                <script type='text/javascript'>

                                $.ajax({
                                  url: 'upgrade/doUpgrade.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                  }
                                });

                                var loop=self.setInterval('checkUpgrade()',5000);

                                function checkUpgrade(){

                                $.ajax({
                                  url: 'upgrade/upgradeStatus.php',
                                  cache: false,
                                  timeout: 10000,
                                  success: function(response){
                                        if(response != 'working') window.location = 'index.php?upgrade&done';
                                  }
                                });

                                }


				</script>";
        }
    }
}

function checkname()
{
    $file = $_FILES[upgrade][name];
    $length = strlen($file);
    $end = $length - 4;
    $starts = (strpos($file, "upgrade") === 0) ? TRUE : FALSE;
    $ends = (strpos($file, ".bin") === $end) ? TRUE : FALSE;
    $hasspaces = (strpos($file, " ") === FALSE) ? TRUE : FALSE;
    return ($starts && $ends && $hasspaces);
}
?>

<div class=content>
    <?php if ($message != "") echo "<font color=lime>" . $message . "<font><br /><br />"; ?>
    <div class=contentTitle><?= $strings["upgrade-check-title"] ?></div>
    <div class=contentContent>
        <center><?= $strings["upgrade-firmware"] ?> <?php include('includes/fwversion'); ?><br />
            <a href="index.php?upgrade&checkUpgrade"><?= $strings["upgrade-check-link"] ?></a></center>
        <?php if ($upgradeMessage != "") echo "<br /><br /><font color=lime>" . $upgradeMessage . "</font>"; ?>
    </div><br /><br />

    <div class=contentTitle><?= $strings["upgrade-doUpgrade-title"] ?></div>
    <div class=contentContent>
        <center>
            <form action="" method="post" enctype="multipart/form-data" onSubmit="return confirm('<?= $strings["upgrade-doUpgrade-confirm"] ?>')">
                <table>
                    <tr><td>Upgrade.bin:</td><td><input type="file" value="upgrade.bin" name="upgrade" id="upgrade" /></td></tr>
                    <tr><td>MD5:</td><td><input type="text" name="md5sum"></td></tr>
                </table>
                <input type="submit" name="doUpgrade" value="<?= $strings["upgrade-doUpgrade-button"] ?>">
            </form>
        </center>
    </div><br /><br />

    <div class=contentTitle><?= $strings["upgrade-warning-title"] ?></div>
    <div class=contentContent>
        <center>
            <pre><?= $strings["upgrade-warning"] ?>
<!-- Do not feed Pineapple after midnight -->
            </pre>
        </center>
    </div><br /><br />

    <div class=contentTitle><?= $strings["upgrade-memory"] ?></div>
    <div class=contentContent>
        <pre>
            <?php
            $cmd = "free";
            exec($cmd, $output);
            foreach ($output as $outputline)
            {
                echo ("$outputline\n");
            }
            ?>
        </pre>
    </div><br /><br />


</div>
