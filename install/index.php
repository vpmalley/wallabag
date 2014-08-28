<?php
/**
 * wallabag, self hostable application allowing you to not miss any content anymore
 *
 * @category   wallabag
 * @author     Nicolas Lœuillet <nicolas@loeuillet.org>
 * @copyright  2013
 * @license    http://opensource.org/licenses/MIT see COPYING file
 */

$errors = array();
$successes = array();
$installed = false;


/** 
 * First step : check php configuration 
 */

$app_name = 'wallabag';
$phpconfig = [];

$phpconfig['php'] = (function_exists('version_compare') && version_compare(phpversion(), '5.3.3', '>='));
$phpconfig['pcre'] = extension_loaded('pcre');
$phpconfig['zlib'] = extension_loaded('zlib');
$phpconfig['mbstring'] = extension_loaded('mbstring');
$phpconfig['iconv'] = extension_loaded('iconv');
$phpconfig['tidy'] = function_exists('tidy_parse_string');
$phpconfig['curl'] = function_exists('curl_exec');
$phpconfig['parse_ini'] = function_exists('parse_ini_file');
$phpconfig['parallel'] = ((extension_loaded('http') && class_exists('HttpRequestPool')) || ($phpconfig['curl'] && function_exists('curl_multi_init')));
$phpconfig['allow_url_fopen'] = (bool)ini_get('allow_url_fopen');
$phpconfig['filter'] = extension_loaded('filter');
$phpconfig['gettext'] = function_exists("gettext");
$phpconfig['gd'] = extension_loaded('gd');

if (extension_loaded('xmlreader')) {
    $xml_ok = true;
} elseif (extension_loaded('xml')) {
    $parser_check = xml_parser_create();
    xml_parse_into_struct($parser_check, '<foo>&amp;</foo>', $values);
    xml_parser_free($parser_check);
    $xml_ok = isset($values[0]['value']);
} else {
    $xml_ok = false;
}
$phpconfig['xml'] = $xml_ok;


/* Function taken from at http://php.net/manual/en/function.rmdir.php#110489
 * Idea : nbari at dalmp dot com
 * Rights unknown
 * Here in case of .gitignore files to delete
 */
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
      (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
  }

/**
 * Call to delete install directory if already installed
 */
if (isset($_GET['clean'])) {
    clean();
    header('Location: index.php');
}

/**
 * Delete install directory
 */
function clean() {
    if (is_dir('install')){
    delTree('install');    
    }
}


/**
 * Download vendor package
 */
if (isset($_POST['download'])) {
    if (!file_put_contents("cache/vendor.zip", fopen("http://static.wallabag.org/files/vendor.zip", 'r'))) {
        $errors[] = 'Impossible to download vendor.zip. Please <a href="http://wllbg.org/vendor">download it manually</a> and unzip it in your wallabag folder.';
    }
    else {
        if (extension_loaded('zip')) {
            $zip = new ZipArchive();
            if ($zip->open("cache/vendor.zip") !== TRUE){
                $errors[] = 'Impossible to open cache/vendor.zip. Please unzip it manually in your wallabag folder.';
            }
            if ($zip->extractTo(realpath(''))) {
                @unlink("cache/vendor.zip");
                $successes[] = 'twig is now installed, you can install wallabag.';
            }
            else {
                $errors[] = 'Impossible to extract cache/vendor.zip. Please unzip it manually in your wallabag folder.';
            }
            $zip->close();
        }
        else {
            $errors[] = 'zip extension is not enabled in your PHP configuration. Please unzip cache/vendor.zip in your wallabag folder.';
        }
    }
}

/**
 * Installation
 */

else if (isset($_POST['install'])) {
    if (!is_dir('vendor')) {
        $errors[] = 'You must install twig before.'; # useless unless JS not enabled
    }
    else {
        $continue = true;
        // Create config.inc.php
        if (!copy('inc/poche/config.inc.default.php', 'inc/poche/config.inc.php')) {
            $errors[] = 'Installation aborted, impossible to create inc/poche/config.inc.php file. Maybe you don\'t have write access to create it.';
            $continue = false;
        }
        else {
            function generate_salt() {
                mt_srand(microtime(true)*100000 + memory_get_usage(true));
                return md5(uniqid(mt_rand(), true));
            }

            $content = file_get_contents('inc/poche/config.inc.php');
            $salt = generate_salt();
            $content = str_replace("define ('SALT', '');", "define ('SALT', '".$salt."');", $content);
            file_put_contents('inc/poche/config.inc.php', $content);
        }

        if ($continue) {

            // User informations
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $email = trim($_POST['email']);
            $salted_password = sha1($password . $username . $salt);

            // Database informations
            if ($_POST['db_engine'] == 'sqlite') {
                if (!copy('install/poche.sqlite', 'db/poche.sqlite')) {
                    $errors[] = 'Impossible to create inc/poche/config.inc.php file.';
                    $continue = false;
                }
                else {
                    $db_path = 'sqlite:' . realpath('') . '/db/poche.sqlite';
                    $handle = new PDO($db_path);
                    $sql_structure = "";
                }
            }
            else {
                $content = file_get_contents('inc/poche/config.inc.php');

                if ($_POST['db_engine'] == 'mysql') {
                    $db_path = 'mysql:host=' . $_POST['mysql_server'] . ';dbname=' . $_POST['mysql_database'];
                    $content = str_replace("define ('STORAGE_SERVER', 'localhost');", "define ('STORAGE_SERVER', '".$_POST['mysql_server']."');", $content);
                    $content = str_replace("define ('STORAGE_DB', 'poche');", "define ('STORAGE_DB', '".$_POST['mysql_database']."');", $content);
                    $content = str_replace("define ('STORAGE_USER', 'poche');", "define ('STORAGE_USER', '".$_POST['mysql_user']."');", $content);
                    $content = str_replace("define ('STORAGE_PASSWORD', 'poche');", "define ('STORAGE_PASSWORD', '".$_POST['mysql_password']."');", $content);
                    $handle = new PDO($db_path, $_POST['mysql_user'], $_POST['mysql_password']); 

                    $sql_structure = file_get_contents('install/mysql.sql');
                }
                else if ($_POST['db_engine'] == 'postgres') {
                    $db_path = 'pgsql:host=' . $_POST['pg_server'] . ';dbname=' . $_POST['pg_database'];
                    $content = str_replace("define ('STORAGE_SERVER', 'localhost');", "define ('STORAGE_SERVER', '".$_POST['pg_server']."');", $content);
                    $content = str_replace("define ('STORAGE_DB', 'poche');", "define ('STORAGE_DB', '".$_POST['pg_database']."');", $content);
                    $content = str_replace("define ('STORAGE_USER', 'poche');", "define ('STORAGE_USER', '".$_POST['pg_user']."');", $content);
                    $content = str_replace("define ('STORAGE_PASSWORD', 'poche');", "define ('STORAGE_PASSWORD', '".$_POST['pg_password']."');", $content);
                    $handle = new PDO($db_path, $_POST['pg_user'], $_POST['pg_password']);

                    $sql_structure = file_get_contents('install/postgres.sql');
                }

                $content = str_replace("define ('STORAGE', 'sqlite');", "define ('STORAGE', '".$_POST['db_engine']."');", $content);
                file_put_contents('inc/poche/config.inc.php', $content);
            }

            if ($continue) {

                function executeQuery($handle, $sql, $params) {
                    try
                    {
                        $query = $handle->prepare($sql);
                        $query->execute($params);
                        return $query->fetchAll();
                    }
                    catch (Exception $e)
                    {
                        return FALSE;
                    }
                }

                // create database structure
                $query = executeQuery($handle, $sql_structure, array());

                // Create user
                $handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = 'INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)';
                $params = array($username, $salted_password, $username, $email);
                $query = executeQuery($handle, $sql, $params);

                $id_user = $handle->lastInsertId();

                $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
                $params = array($id_user, 'pager', '10');
                $query = executeQuery($handle, $sql, $params);

                $sql = 'INSERT INTO users_config ( user_id, name, value ) VALUES (?, ?, ?)';
                $params = array($id_user, 'language', 'en_EN.UTF8');
                $query = executeQuery($handle, $sql, $params);

                clean();
                $installed = true;
                $successes[] = 'wallabag is now installed. You can now <a href="index.php?clean=0">access it !</a>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0">
        <meta charset="utf-8">
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=10">
        <![endif]-->
        <title>wallabag - installation</title>
        <link rel="shortcut icon" type="image/x-icon" href="themes/baggy/img/favicon.ico" />
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="themes/baggy/img/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="themes/baggy/img/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" href="themes/baggy/img/apple-touch-icon-precomposed.png">
        <link href='//fonts.googleapis.com/css?family=PT+Sans:700' rel='stylesheet' type='text/css'>
        <link rel="stylesheet" href="themes/baggy/css/ratatouille.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/font.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/main.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/messages.css" media="all">
        <link rel="stylesheet" href="themes/baggy/css/print.css" media="print">
        <script src="themes/default/js/jquery-2.0.3.min.js"></script>
        <script src="themes/baggy/js/init.js"></script>
        <style type="text/css">
            table#chart {
                border-collapse:collapse;
            }

            table#chart th {
                background-color:#eee;
                padding:2px 3px;
                border:1px solid #fff;
            }

            table#chart td {
                text-align:center;
                padding:2px 3px;
                border:1px solid #eee;
            }
            .good{
            background-color:#52CC5B;
            }
            .bad{
            background-color:#F74343;
            font-style:italic;
            font-weight: bold;
            }
            .pass{
            background-color:#FF9500;
            }

            .nextstep {
                background-color: #52CC5B;
                padding: 10px;
                border-radius: 5px;
                display: inline-block;
            }
            .reload {
                background-color: #154472;
                padding: 10px;
                border-radius: 5px;
                display: inline-block;
            }
        </style>
    </head>
    <body>
        <header class="w600p center mbm">
            <h1 class="logo">
                <img width="100" height="100" src="themes/baggy/img/logo-w.png" alt="logo poche" />
            </h1>
        </header>
        <div id="main">
            <button id="menu" class="icon icon-menu desktopHide"><span>Menu</span></button>
            <ul id="links" class="links">
                <li><a href="http://www.wallabag.org/frequently-asked-questions/">FAQ</a></li>
                <li><a href="http://doc.wallabag.org/">doc</a></li>
                <li><a href="http://www.wallabag.org/help/">help</a></li>
                <li><a href="http://www.wallabag.org/">wallabag.org</a></li>
            </ul>
            <?php if (!$installed) { ?>
            <?php if (!empty($errors)) : ?>
                <div class='messages error install'>
                    <p>Errors during installation:</p>
                    <p>
                        <ul>
                        <?php foreach($errors as $error) :?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </p>
                </div>
            <?php endif; ?>
            <?php if (!empty($successes)) : ?>
                <div class='messages success install'>
                    <p>
                        <ul>
                        <?php foreach($successes as $success) :?>
                            <li><?php echo $success; ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </p>
                </div>
            <?php else : ?>
                <?php if (file_exists('inc/poche/config.inc.php') && is_dir('vendor')) : ?>
                <div class='messages success install'>
                    <p>
                        wallabag seems already installed. If you wanted to update it, you now have to <a href="index.php?clean=0">delete the install directory</a>.
                    </p>
                </div>
                <?php endif; ?>    
            <?php endif; ?>

            <div id="step1" class="chunk">
            <h2 style="text-align:center;"><?php echo $app_name; ?>: Compatibility Test</h2>
            <table cellpadding="0" cellspacing="0" border="0" width="100%" id="chart">
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Should Be</th>
                        <th>What You Have</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="<?php echo ($phpconfig['php']) ? 'enabled' : 'disabled'; ?>">
                        <td>PHP</td>
                        <td>5.3.3 or higher</td>
                        <td class="<?php echo ($phpconfig['php']) ? 'good' : 'disabled'; ?>"><?php echo phpversion(); echo ($phpconfig['php']) ? '' : 'You are running an unsupported version of PHP.' ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['xml']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/xml">XML</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['xml']) ? '<td class="good">Enabled, and sane</span>' : '<td class="bad">Disabled, or broken<br />Your PHP installation doesn\'t support XML parsing.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['pcre']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/pcre">PCRE</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['pcre']) ? '<td class="good">Enabled' : '<td class="bad">Disabled<br />Your PHP installation doesn\'t support Perl-Compatible Regular Expressions.'; ?></td>
                    </tr>
<!--                    <tr class="<?php echo ($phpconfig['zlib']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/zlib">Zlib</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['zlib']) ? '<td class="good">Enabled' : '<td class="bad">Disabled<br />Extension not available.  SimplePie will ignore any GZIP-encoding, and instead handle feeds as uncompressed text.'; ?></td>
                    </tr> -->
<!--                    <tr class="<?php echo ($phpconfig['mbstring']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/mbstring">mbstring</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['mbstring']) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
                    </tr> -->
<!--                    <tr class="<?php echo ($phpconfig['iconv']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/iconv">iconv</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['iconv']) ? '<td class="good">Enabled' : '<td class="bad">Disabled'; ?></td>
                    </tr> -->
                    <tr class="<?php echo ($phpconfig['gd']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="">GD Library</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['gd']) ? '<td class="good">Enabled' : '<td class="pass">Disabled<br />Extension not available. Locally saving pictures on server will not be possible'; ?></td>
                    </tr>     

                    <tr class="<?php echo ($phpconfig['filter']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://uk.php.net/manual/en/book.filter.php">Data filtering</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['filter']) ? '<td class="good">Enabled' : '<td class="pass">Disabled<br />Your PHP configuration has the filter extension disabled. '; ?></td>
                    </tr>                   
                    <tr class="<?php echo ($phpconfig['tidy']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/tidy">Tidy</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['tidy']) ? '<td class="good">Enabled' : '<td class="pass">Disabled<br />Extension not available. ' . $app_name . ' should still work with most feeds, but you may experience problems with some.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['curl']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/curl">cURL</a></td>
                        <td>Enabled</td>
                        <?php echo (extension_loaded('curl')) ? '<td class="good">Enabled' : '<td class="pass">Disabled<br />Extension is not available.  SimplePie will use <code>fsockopen()</code> instead.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['parse_ini']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://uk.php.net/manual/en/function.parse-ini-file.php">Parse ini file</td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['parse_ini']) ? '<td class="good">Enabled' : '<td class="bad">Disabled<br />Bad luck : your webhost has decided to block the use of the <em>parse_ini_file</em> function.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['parallel']) ? 'enabled' : 'disabled'; ?>">
                        <td>Parallel URL fetching</td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['parallel']) ? '<td class="good">Enabled' : '<td class="pass">Disabled<br /><code>HttpRequestPool</code> or <code>curl_multi</code> support is not available.  <?php echo $app_name; ?> will use <code>file_get_contents()</code> instead to fetch URLs sequentially rather than in parallel.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['allow_url_fopen']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://www.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen">allow_url_fopen</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['allow_url_fopen']) ? '<td class="good">Enabled' : '<td class="bad">Disabled<br />Your PHP configuration has allow_url_fopen disabled.'; ?></td>
                    </tr>
                    <tr class="<?php echo ($phpconfig['gettext']) ? 'enabled' : 'disabled'; ?>">
                        <td><a href="http://php.net/manual/en/book.gettext.php">gettext</a></td>
                        <td>Enabled</td>
                        <?php echo ($phpconfig['gettext']) ? '<td class="good">Enabled' : '<td class="bad">Disabled<br />Extension not available. The system we use to display wallabag in various languages is not available.'; ?></td>
                    </tr>
                </tbody>
            </table>
            <p>Status : </p>
            <?php if($phpconfig['php'] && $phpconfig['xml'] && $phpconfig['pcre'] && $phpconfig['parse_ini'] && $phpconfig['allow_url_fopen'] && $phpconfig['gettext']) {
                if ($phpconfig['filter'] && $phpconfig['tidy'] && $phpconfig['curl'] && $phpconfig['parallel'] && $phpconfig['gd']) {
                    echo '<p>Your webserver has all what it needs for ' . $app_name . ' to work properly.</p><p><a class="nextstep" id="nextstep1" href="#step2">Next Step</a></p>';
                } else {
                    echo '<p>Your webserver hasn\'t got the perfect configuration for ' . $app_name .  ' to work properly, but it should work anyway.<br />You can try to fix some problems highlighted above.</p><p><a class="nextstep" style="background-color:#FF9500" id="nextstep1" href="#step2">Next Step</a></p><a class="reload" href="#step1">Reload</a>';
                }
            } else {
                echo '<p>' . $app_name . ' can\'t work on this webserver. Please fix the problems highlighted above.</p>';
            }

            ?>
            </p>
        </div>
        <div id="step2">
        <h2 style="text-align:center;">Twig installation</h2>
            <form method="post">
                <fieldset>
                    <?php if (!is_dir('vendor')) { ?>
                        <div>wallabag needs twig, a template engine (<a href="http://twig.sensiolabs.org/">?</a>). Two ways to install it:<br />
                        <ul>
                            <li>automatically download and extract vendor.zip into your wallabag folder. 
                            <p><input type="submit" name="download" value="Download vendor.zip" /></p>
                            <?php if (!extension_loaded('zip')) : ?>
                                <b>Be careful, zip extension is not enabled in your PHP configuration. You'll have to unzip vendor.zip manually.</b>
                            <?php endif; ?>
                                <em>This method is mainly recommended if you don't have a dedicated server.</em></li>
                            <li>use <a href="http://getcomposer.org/">Composer</a> :<pre><code>curl -s http://getcomposer.org/installer | php
php composer.phar install</code></pre><p>Then, please reload the page.</p></li>
                        </ul>
                        </div>
                        <a class="reload" href="#step2">Reload</a>
                    <?php } else { ?>
                    <div class='messages success install'>Twig seems to be already installed. All good !</div>
                    <a class="nextstep" id="nextstep2" href="#step3">Next Step</a>
                    <?php } ?>
                </fieldset>
        </div>
        <div id="step3">
            <h2 style="text-align:center;">Database installation</h2>
            You must choose a database engine.
                <fieldset>
                    <p>
                        Database engine:
                        <ul>
                            <li><label for="sqlite">SQLite</label> <input name="db_engine" type="radio" checked="" id="sqlite" value="sqlite" />
                            <div id="pdo_sqlite" class='messages error install'>
                                <p>You have to enable <a href="http://php.net/manual/ref.pdo-sqlite.php">pdo_sqlite extension</a>.</p>
                            </div>
                            </li>
                            <li>
                                <label for="mysql">MySQL</label> <input name="db_engine" type="radio" id="mysql" value="mysql" />
                                <ul id="mysql_infos">
                                    <li><label for="mysql_server">Server</label> <input type="text" placeholder="localhost" id="mysql_server" name="mysql_server" /></li>
                                    <li><label for="mysql_database">Database</label> <input type="text" placeholder="wallabag" id="mysql_database" name="mysql_database" /></li>
                                    <li><label for="mysql_user">User</label> <input type="text" placeholder="user" id="mysql_user" name="mysql_user" /></li>
                                    <li><label for="mysql_password">Password</label> <input type="text" placeholder="p4ssw0rd" id="mysql_password" name="mysql_password" /></li>
                                </ul>
                            </li>
                            <li>
                                <label for="postgres">PostgreSQL</label> <input name="db_engine" type="radio" id="postgres" value="postgres" />
                                <ul id="pg_infos">
                                    <li><label for="pg_server">Server</label> <input type="text" placeholder="localhost" id="pg_server" name="pg_server" /></li>
                                    <li><label for="pg_database">Database</label> <input type="text" placeholder="wallabag" id="pg_database" name="pg_database" /></li>
                                    <li><label for="pg_user">User</label> <input type="text" placeholder="user" id="pg_user" name="pg_user" /></li>
                                    <li><label for="pg_password">Password</label> <input type="text" placeholder="p4ssw0rd" id="pg_password" name="pg_password" /></li>
                                </ul>
                            </li>
                        </ul>
                    </p>
                </fieldset>
                <div>
                <ul>
                    <li><strong>SQLite</strong> is the simplest of those three database engines. It just writes data into a file. You don't have to configure login informations of any kind.<br>
                    The downfall is that it might be slower than other database engines with very large piece of data. <br> 
                    It is therefore recommanded if you begin with wallabag, or you don't want to deal with extra configuration.</li>
                    <li><strong>MySQL</strong> (also known as <strong>MariaDB</strong>) is a very common database engine on most servers. <br>
                    It should be faster than SQLite in most cases. <br>
                    You have to enter credentials to access it. Contact your server administrator if needed.</li>
                    <li><strong>PostgreSQL</strong> is another database engine very similar to MySQL, but less frequent on most hosting plans. However, some people prefer it since it may be faster than MySQL in some cases.</li>
                </ul>
                </div>
                <a class="nextstep" id="nextstep3" href="#step4">Next step</a>
            </div>
            <div id="step4">
                <h2 style="text-align:center;">User settings</h2>
                <fieldset>
                    <p>
                        <label for="username">Username</label>
                        <input type="text" required id="username" name="username" value="wallabag" />
                    </p>
                    <p>
                        <label for="password">Password</label>
                        <input type="password" required id="password" name="password" value="wallabag" />
                    </p>
                    <p>
                        <label for="show">Show password:</label> <input name="show" id="show" type="checkbox" onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'">
                    </p>
                    <p>
                        <label for="email">Email (not required)</label>
                        <input type="email" name="email" />
                    </p>
                </fieldset>

                <input type="submit" id="install_button" value="Install wallabag" name="install" />
            </form>
        </div>
        <script>
            $("#mysql_infos").hide();
            $("#pg_infos").hide();

            <?php
            if (!extension_loaded('pdo_sqlite')) : ?>
            $("#install_button").hide();
            <?php
            else :
            ?>
            $("#pdo_sqlite").hide();
            <?php
            endif;
            ?>

            $("input[name=db_engine]").click(function() 
                {
                    if ( $("#mysql").prop('checked')) {
                        $("#mysql_infos").show();
                        $("#pg_infos").hide();
                        $("#pdo_sqlite").hide();
                        $("#install_button").show();
                    }
                    else {
                        if ( $("#postgres").prop('checked')) {
                            $("#mysql_infos").hide();
                            $("#pg_infos").show();
                            $("#pdo_sqlite").hide();
                            $("#install_button").show();
                        }
                        else {
                            $("#mysql_infos").hide();
                            $("#pg_infos").hide();
                            <?php
                            if (!extension_loaded('pdo_sqlite')) : ?>
                            $("#pdo_sqlite").show();
                            $("#install_button").hide();
                            <?php
                            endif;
                            ?>
                        }
                    }
                });
            $("#step2").hide();
            $("#step3").hide();
            $("#step4").hide();

            $("#nextstep1").click(function()
            {
                $("#step1").hide();
                $("#step2").show();
            });

            $("#nextstep2").click(function()
            {
                $("#step2").hide();
                $("#step3").show();
            });

            $("#nextstep3").click(function()
            {
                $("#step3").hide();
                $("#step4").show();
            });

            $(".reload").click(function(){
                location.reload();
            });
        </script>
    <?php } else { ?>
    <div class='messages success install'>
    wallabag is now installed. You can now <a href="index.php">access it !</a>
    </div>
    <?php } ?>
    </body>
</html>
