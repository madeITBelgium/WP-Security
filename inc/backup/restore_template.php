<?php
/***************************************************************************/
/*                                                                         */
/* This is the config file to restore/duplicate the website in this backup */
/*                                                                         */
/***************************************************************************/

$php_version = '5.6.30';
$mysql_version = '5.5';
$wp_version = '4.8.2';
$url = 'https://www.madeit.be';
$admin_url = 'https://www.madeit.be/wp-admin';
$path = '/home/madeit/web/madeit.be/public_html';
$zip_backup = '';

function getPHPVersion() {
    return phpversion();
}

function getMySQLVersion() {
    return mysql_get_server_info();
}

function installWordPress() {
    file_put_contents("wordpress.zip", file_get_contents("https://wordpress.org/wordpress-" . $wp_version . ".zip"));
    
    $zip = new ZipArchive();
    $x = $zip->open("wordpress.zip");
    if($x === true) {
        $zip->extractTo(".");
        $zip->close();
        
        $directory = __DIR__"/wordpress";
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' and $file != '..') {
                rename($directory . "/" . $file, __DIR__."/". $file);
            }
        }
         
        unlink("wordpress.zip");
        
        echo json_encode(['success' => true]);
        exit;
    }
    else {
        echo json_encode(['success' => false]);
        exit;
    }
}

if(isset($_GET['action']) && $_GET['action'] == "INSTALL_WP") {
    installWordPress();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Restore WordPress website | WP Security By Made I.T.</title>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    </head>
    <body>
        <div class="container" style="margin-top: 30px;">
            <h1 class="h3">Restore your WordPress website <?php echo $url; ?></h1>
        </div>
        <div class="container" style="min-height:50% margin-top: 50px;" id="step1">
            <div class="row">
                <div class="col">
                    This script will automaticly restore your WordPress website on this location.
                    <table class="table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Current server</th>
                                <th>Original server</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>PHP Version</th>
                                <td><?php echo getPHPVersion(); ?></td>
                                <td><?php echo $php_version; ?></td>
                            </tr>
                            <tr>
                                <th>DB Version</th>
                                <td><?php echo getMySQLVersion(); ?></td>
                                <td><?php echo $mysql_version; ?></td>
                            </tr>
                            <tr>
                                <th>WP Version</th>
                                <td></td>
                                <td><?php echo $wp_version; ?></td>
                            </tr>
                            <tr>
                                <th>URL</th>
                                <td></td>
                                <td><?php echo $url; ?></td>
                            </tr>
                            <tr>
                                <th>install path</th>
                                <td><?php echo __DIR__; ?></td>
                                <td><?php echo $path; ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <a href="#" class="next-step btn btn-success pull-right" data-step="2">Next step</a>
                </div>
            </div>
        </div>
        <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step2">
            <div class="row">
                <div class="col">
                    <h2 class="h4">Install WordPress</h2>
                    <a href="#" class="next-step btn btn-success pull-right" data-step="3">Next step</a>
                </div>
            </div>
        </div>
        <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step3">
            <div class="row">
                <div class="col">
                    <h2 class="h4">Restore Plugins, Themes and uploads</h2>
                    <a href="#" class="next-step btn btn-success pull-right" data-step="4">Next step</a>
                </div>
            </div>
        </div>
        <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step4">
            <div class="row">
                <div class="col">
                    <h2 class="h4">Restore database</h2>
                    <a href="#" class="next-step btn btn-success pull-right" data-step="5">Next step</a>
                </div>
            </div>
        </div>
        <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step4">
            <div class="row">
                <div class="col">
                    <h2 class="h4">Completed</h2>
                    
                </div>
            </div>
        </div>
        
        <div class="container-fluid bg-primary" style="margin-top: 30px;">
            <div class="row" style="padding-top: 15px; padding-bottom: 15px;">
                <div class="col">
                    <div class="text-center">
                        &copy; 2017 - <a href="">Made I.T.</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Optional JavaScript -->
        <!-- jQuery first, then Popper.js, then Bootstrap JS -->
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
        <script>
        $(function() {
            $('.next-step').click(function(e) {
                e.preventDefault();
                var nextStep = $(this).attr('data-step');
                console.log('#step' + (nextStep - 1));
                $('#step' + (nextStep - 1)).hide();
                $('#step' + nextStep).show();
            });
        });
        </script>
    </body>
</html>