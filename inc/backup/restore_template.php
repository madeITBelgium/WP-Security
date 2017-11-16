<?php
/***************************************************************************/
/*                                                                         */
/* This is the restore/duplicate script                                    */
/*                                                                         */
/***************************************************************************/
if (!file_exists('restore-config.php')) {
    exit;
}
include 'restore-config.php';

$cli = php_sapi_name() == 'cli';

function getPHPVersion()
{
    return phpversion();
}

function getMySQLVersion()
{
    return mysql_get_server_info();
}

function installWordPress()
{
    $downloadUrl = 'https://wordpress.org/wordpress-'.$wp_version.'.zip';
    $downloadUrl = 'http://downloads.wordpress.org/release/'.$wp_locale.'/wordpress-'.$wp_version.'.zip';
    file_put_contents('wordpress.zip', file_get_contents($downloadUrl));

    $zip = new ZipArchive();
    $x = $zip->open('wordpress.zip');
    if ($x === true) {
        $zip->extractTo('.');
        $zip->close();

        $directory = __DIR__.'/wordpress';
        $dir = dir($directory);

        while (false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..' && $file != 'wp-config.php') {
                rename($directory.'/'.$file, __DIR__.'/'.$file);
            }
        }
        rmdir('wordpress');

        unlink('wordpress.zip');

        return true;
    } else {
        return false;
    }
}

function installContent()
{
    $zip = new ZipArchive();
    $x = $zip->open('wp-content.zip');
    if ($x === true) {
        $zip->extractTo('.');
        $zip->close();
        unlink('wp-content.zip');

        return true;
    } else {
        return false;
    }
}

function setUpDBSettings()
{
    $urls = generateUrls();

    //Create wp-config
    $wp_config = file_get_contents('wp-config.php');
    str_replace($path, __DIR__, $wp_config); //Replace path
    str_replace(backSlash($path), backSlash(__DIR__), $wp_config); //Replace path
    str_replace("define('DB_NAME', '".$db_database."');", "define('DB_NAME', '".$_POST['db_name']."');", $wp_config); //database name
    str_replace("define('DB_USER', '".$db_username."');", "define('DB_USER', '".$_POST['db_user']."');", $wp_config); //database user
    str_replace("define('DB_HOST', '".$db_password."');", "define('DB_PASSWORD', '".$_POST['db_pass']."');", $wp_config); //database pass
    str_replace("define('DB_USER', '".$db_host."');", "define('DB_USER', '".$_POST['db_host']."');", $wp_config); //database host

    foreach ($urls as $oldUrl => $newUrl) {
        str_replace($oldUrl, $newUrl, $wp_config);
    }
    file_put_contents('wp-config.php', $wp_config);

    //Create db script
    $dbscript = file_get_contents('database.sql');
    str_replace($path, __DIR__, $dbscript); //Replace path
    str_replace(backSlash($path), backSlash(__DIR__), $dbscript); //Replace path

    foreach ($urls as $oldUrl => $newUrl) {
        str_replace($oldUrl, $newUrl, $dbscript);
    }
    file_put_contents('database.sql', $dbscript);

    $link = mysqli_connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_name']);

    /* check connection */
    if (mysqli_connect_errno()) {
        return false;
    }

    /* execute multi query */
    if (mysqli_multi_query($link, $dbscript)) {
        return true;
    } else {
        return false;
    }
}

function generateUrls()
{
    //Old URL
    $oldUrl = [];
    $oldUrlData = parse_url($url);

    $addPath = '';
    if (isset($oldUrlData['path'])) {
        $addPath = '/'.$oldUrlData['path'];
        $addPathEsc = "\/".$oldUrlData['path'];
    }

    return [
        'http://'.$oldUrlData['host'].$addPath             => $_POST['url'],
        'https://'.$oldUrlData['host'].$addPath            => $_POST['url'],
        'http://'.$oldUrlData['host']                      => $_POST['url'],
        'https://'.$oldUrlData['host']                     => $_POST['url'],
        backSlash('http://'.$oldUrlData['host'].$addPath)  => backSlash($_POST['url']),
        backSlash('https://'.$oldUrlData['host'].$addPath) => backSlash($_POST['url']),
        backSlash('http://'.$oldUrlData['host'])           => backSlash($_POST['url']),
        backSlash('https://'.$oldUrlData['host'])          => backSlash($_POST['url']),
    ];
}

function backSlash($str)
{
    $str = str_replace('\/', '/', $str);

    return str_replace('/', '\/', $str);
}

function checkDBSettings($dbhost, $dbname, $dbuser, $dbpass)
{
    $link = mysqli_connect($dbhost, $dbuser, $dbpass) or die(json_encode(['success' => false, 'error' => 'Cannot connect to the database server.']));
    mysqli_select_db($link, $dbname) or die(json_encode(['success' => false, 'error' => 'Cannot open the database.']));

    echo json_encode(['success' => true]);
    exit;
}

if ($cli) {
    //Run restore

    exit;
}

if (isset($_POST['step']) && $_POST['step'] == 1) {
    echo json_encode(['success' => true]);
    exit;
} elseif (isset($_POST['step']) && $_POST['step'] == 2) { //Test DB connection
    checkDBSettings($_POST['database_host'], $_POST['database'], $_POST['database_user'], $_POST['database_password']);
    exit;
} elseif (isset($_POST['step']) && $_POST['step'] == 3) { //Install WP
    if ($_POST['url'] == $url) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => true]);
        exit;
    }
    exit;
} elseif (isset($_POST['step']) && $_POST['step'] == 4) { //Restore website
    if (isset($_GET['partion']) && $_GET['partion'] == 1 || !isset($_GET['partition'])) {
        installWordPress();
    }
    if (isset($_GET['partion']) && $_GET['partion'] == 2 || !isset($_GET['partition'])) {
        installContent();
    }
    if (isset($_GET['partion']) && $_GET['partion'] == 3 || !isset($_GET['partition'])) {
        setUpDBSettings();
    }
    echo json_encode(['success' => true]);
    exit;
} else {
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
                                    <th>WP Locale</th>
                                    <td></td>
                                    <td><?php echo $wp_locale; ?></td>
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
                        <form action="" id="form_1">
                            <input type="hidden" name="step" value="1">
                        </form>
                        <a href="#" class="next-step btn btn-success pull-right" data-step="2">Next step</a>
                    </div>
                </div>
            </div>
            <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step2">
                <div class="row">
                    <div class="col">
                        <h2 class="h4">Install WordPress</h2>
                        <form action="" id="form_2">
                            <input type="hidden" name="step" value="2">
                            <div class="form-group">
                                <label for="database">Database name:</label>
                                <input type="text" name="database" value="<?php echo $db_database; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="database">Database user:</label>
                                <input type="text" name="database_user" value="<?php echo $db_username; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="database">Database Password:</label>
                                <input type="text" name="database_password" value="<?php echo $db_password; ?>" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="database">Database Host:</label>
                                <input type="text" name="database_host" value="<?php echo $db_host; ?>" class="form-control">
                            </div>
                        </form>
                        <a href="#" class="next-step btn btn-success pull-right" data-step="3">Next step</a>
                    </div>
                </div>
            </div>
            <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step3">
                <div class="row">
                    <div class="col">
                        <h2 class="h4">Restoring under other url?</h2>
                        <form action="" id="form_3">
                            <input type="hidden" name="step" value="3">
                            <div class="form-group">
                                <label for="database">Weburl:</label>
                                <input type="text" name="url" id="url" value="<?php echo $url; ?>" class="form-control">
                            </div>
                        </form>
                        <a href="#" class="next-step btn btn-success pull-right" data-step="4">Next step</a>
                    </div>
                </div>
            </div>
            <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step4">
                <div class="row">
                    <div class="col">
                        <h2 class="h4">Start restore.</h2>
                        
                        <form action="" id="form_4">
                            <input type="hidden" name="step" value="4">
                            <input type="hidden" name="url" id="final_url" value="">
                            <input type="hidden" name="db_host" value="">
                            <input type="hidden" name="db_pass" value="">
                            <input type="hidden" name="db_user" value="">
                            <input type="hidden" name="db_name" value="">
                        </form>
                        <a href="#" class="next-step btn btn-success pull-right" data-step="5">Start</a>
                    </div>
                </div>
            </div>
            <div class="container" style="min-height:50% margin-top: 50px; display: none" id="step5">
                <div class="row">
                    <div class="col">
                        <h2 class="h4">Start restore.</h2>
                        <h3>
                            Restore starting ...
                        </h3>
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
            <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
            <script>
            $(function() {
                var db_host;
                var db_user;
                var db_pass;
                var db_name;
                var url;
                var partion = 1;
                
                $('.next-step').click(function(e) {
                    e.preventDefault();
                    var nextStep = $(this).attr('data-step');
                    var prevStep = nextStep - 1;
                    var form = $('#form_' + prevStep);
                    
                    if(prevStep == 2) {
                        db_host = $('[name=database_host]').val();
                        db_user = $('[name=database_user]').val();
                        db_pass = $('[name=database_password]').val();
                        db_name = $('[name=database]').val();
                        
                        $('[name=db_host]').val(db_host);
                        $('[name=db_user]').val(db_user);
                        $('[name=db_pass]').val(db_pass);
                        $('[name=db_name]').val(db_name);
                    }
                    if(prevStep == 3) {
                        url = $('#url').val();
                        $('#final_url').val(url);
                    }
                    
                    if(prevStep == 4) {
                        $('#step' + (nextStep - 1)).hide();
                        $('#step' + nextStep).show();
                        if(partion == 1) {
                            $('#step5 h3').html('Initializing WordPress');
                            doRestore();
                            partion++;
                        }
                        if(partion == 2) {
                            $('#step5 h3').html('Restoring content');
                            doRestore();
                            partion++;
                        }
                        if(partion == 3) {
                            $('#step5 h3').html('Restoring database');
                            doRestore();
                            partion++;
                        }
                        if(partion == 4) {
                            $('#step5 h3').html('Restore completed');
                        }
                        
                    }
                    else {
                        $.post(window.location, form.serialize(), function(data) {
                            if(data.success) {
                                $('#step' + (nextStep - 1)).hide();
                                $('#step' + nextStep).show();
                            }
                            else {
                                alert(data.error);
                            }
                        }, 'json');
                    }
                });
                
                function doRestore() {
                    $.post(window.location, {'step': 4, 'db_host': db_host, 'db_user': db_user, 'db_pass': db_pass, 'db_name': db_name, 'url': url, 'partion': partion}, function(data) {
                        if(data.success) {

                        }
                        else {
                            alert(data.error);
                        }
                    }, 'json');
                }
            });
            </script>
        </body>
    </html>
    <?php
}
?>