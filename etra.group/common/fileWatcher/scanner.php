<?php
//  
class scanner
{

    function scanner()
    {

        //Add folder paths to scan	
	$this->path[] = '/var/www/html/test'; // test server path 
        //$this->path[] = 'C:/xampp/htdocs/WorkSpaceAnuj'; // local path
        // $this->path[] = 'path 2';
        // $this->path[] = 'path 3';

        //Add exceptions (if path+filename contains exception it will not be scanned)
        $this->exceptions[] = 'cache';
        $this->exceptions[] = 'webstat';

        // set time zone
        date_default_timezone_set("Europe/London");

        //Set mysqli host, login and password
        /*$this->mysqlihost = 'localhost';
        $this->mysqlidb = 'scanner';
        $this->mysqliuser = 'root';
        $this->mysqlipass = 'root';*/	


        $this->mysqlihost = 'localhost';

        $this->mysqlidb = 'scanner';

        $this->mysqliuser = 'devteam';

        $this->mysqlipass = 'sd2"93*3\'a3';


       
        $this->link = new mysqli($this->mysqlihost, $this->mysqliuser, $this->mysqlipass, $this->mysqlidb);
        if (!$this->link) {
            die('Could not connect: ' . $this->link->connect_error);
        }


        $this->blocblockedFilesArr = [];

        $this->blockedFilesArr = [".", "..", ".cache", ".git", "phpmailer", "vendor", ".vscode"]; // this will exclude scanning to detecting changes

    }

    function my_list($path)
    {

        $files = scandir($path);
        return $files;
    }

    function GetListFiles($folder, &$all_files)
    {
        $fp = opendir($folder);
        while ($cv_file = readdir($fp)) {
            if (is_file($folder . "/" . $cv_file)) {
                $all_files[] = $folder . "/" . $cv_file;
            } elseif (!in_array($cv_file, $this->blockedFilesArr) && is_dir($folder . "/" . $cv_file)) {
                $this->GetListFiles($folder . "/" . $cv_file, $all_files);
            }
        }
        closedir($fp);
    }

    function is_exception($file)
    {

        $out = false;
        foreach ($this->exceptions as $exception) {

            if (strpos($file, $exception)) $out = true;
        }

        return $out;
    }

    function scan_path($path)
    {

        $all_files = array();
        $this->GetListFiles($path, $all_files);
        foreach ($all_files as $file) {
            if (!($this->is_exception($file))) {
                $this->files[] = $file;
            }
        }
    }

    function scan()
    {

        foreach ($this->path as $p) { // $this->path is Folders Array 

            $this->scan_path($p);
        }
    }

    function db()
    {
       
        
        $add = 1;
        $update = 1;
        $addOut = "";
        $updateOut = "";
        foreach ($this->files as $file) {

            $q = "select * from scanner where path = '" . $file . "'";
            $res = mysqli_query($this->link, $q);

            if (mysqli_num_rows($res) == 0) {

                $q2 = "insert into scanner (path, date, hash) values ('" . $file . "', " . filemtime($file) . ", '" . md5(file_get_contents($file)) . "');";
                $res2 = mysqli_query($this->link, $q2);
                $addOut .= '<p style="color:red"><b>' . intval($add) . '.) ' . $file . '</b> has been <a href="#0" onclick="return alert(\'Please add this file to Live server, to see changes\');"><u>added!</u></a>' . "</p><br/>";
                $add++;
            } else {

                $hash = md5(file_get_contents($file));
                $mtime = filemtime($file);
                $row = mysqli_fetch_object($res);

                if (($hash != $row->hash) || $row->isLiveUpdated == 0) {
                    $url = base64_encode(str_replace($this->path, "devteam", $file));
                    $domain = "https://etra.group/git/compare.php?cpath="; // Live url diff
                    $site = $domain.$url;
                    $updateOut .= '<p style="color:red"><b>' . intval($update) . '.) ' . $file . '</b> has been <a href="'. $site .'" target="_blank"><u>changed!</u></a>' . "</p><br/>";
                    $q2 = "update scanner set hash = '" . $hash . "', date = " . $mtime . ", isLiveUpdated = 0 where path = '" . $file . "';";
                    $res2 = mysqli_query($this->link, $q2);
                    $update++;
                }
            }
        }

        mysqli_close($this->link);

        return $addOut.$updateOut;
    }

    function allUpdated(){
        $q2 = "update scanner set isLiveUpdated = 1;";
        $res2 = mysqli_query($this->link, $q2);
    }
}

$scanner = new scanner();
$allUpdated = "";
$allUpdated = addslashes($_POST['allUpdated']);
if($allUpdated != ""){
    $scanner->allUpdated();
}


$scanner->scan();
$out = $scanner->db();

if ($out) {
    $out = "Report till " . date('l jS \of F Y h:i:s A') . "<br/><br/>" . $out;
} else {
        
    $out = "Report till " . date('l jS \of F Y h:i:s A') . "<br/><br/>";
    $out .= "<p style='color:green'><b>All files are up to date.</b></p>";
}
?>

<!DOCTYPE html>



<html>







<head>



    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">

    <!-- jQuery library -->



    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>

    <!-- Latest compiled JavaScript -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <link rel="stylesheet" href="diffChecker/example/styles.css" type="text/css" charset="utf-8" />



</head>


<body>



    <div id="cover-spin"></div>



    <div class="container">



        <h1 class="border-bottom py-3">Etra Group - File management system </h1>



        <div>
        <div>
            <form href="scanner.php" method ="POST"><input type="hidden" name="allUpdated" value="true">
                <button class="btn btn-secondary btn-sm"  onclick="return confirm('Are you sure you want to mark all files updated?')" >Mark all files updated</button>
            </form>
        </div>
        <br>

            <div id="bindDiffCode"><?= $out; ?></div>


       


        </div>















    </div>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>



</body>

</html>