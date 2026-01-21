<?php



require_once "config.php";

$result = array();



if(empty($blockedFilesArr))die();

//echo $_SERVER['HTTP_X_FORWARDED_FOR'];



// if ($_SERVER['HTTP_X_FORWARDED_FOR'] !== '212.159.178.222') {





//     $result[] = "Unauthorised access";



//     echo $data = json_encode($result);



//     die;

// }



//if ($_SERVER['HTTP_X_FORWARDED_FOR'] !== '103.240.163.64') die('Unauthorised access');



if ($errorReporting == "true") {

    ini_set('display_errors', 1);



    ini_set('display_startup_errors', 1);



    

}



//require $_SERVER['DOCUMENT_ROOT'] . '/sftpext/vendor/autoload.php';

require $_SERVER['DOCUMENT_ROOT'] . 'git/sftpext/vendor/autoload.php';



use phpseclib3\Net\SFTP;

use phpseclib3\Net\SFTP\Stream;

//require_once("sftpext/vendor/autoload.php"); 



$sftp = new SFTP($host);



$sftp->login($user, $password);





$sftp_path = $_POST['Directory'];

if ($sftp_path == "all") {

    $sftp_path = "devteam";

}


//$sftp_path = "/";


$Type = $_POST["Type"];







switch ($Type) {

    case "ReadFolder":

        // $sftp->setListOrder(true); // shift all folders at top

        $sftp->setListOrder('filename', SORT_ASC); // arrage all folders and file asc

        $files = $sftp->rawlist($sftp_path);



        if ($files === false) {



            $result = null;

        } else {



            // blockedFiles



           



            $i = 0;

            foreach ($files as $name => $attrs) {



                if ((!in_array($name, $blockedFilesArr))) {



                    if ($attrs["type"] == NET_SFTP_TYPE_DIRECTORY) {



                        $result[] = $name;

                    } else {

                        $curr = strtotime("now");

                        $modified = $attrs["mtime"];

                        if (


                            date("Y-m-d", $modified) == date("Y-m-d", $curr) &&

                            strtotime(date("H:i:s", $curr)) >= strtotime(date("H:i:s", $modified))

                        ) {

                            $isModified = "Modified";

                        } else {

                            $isModified =  "Not Modified";

                        }

                        $result[] = $name . "isFile" . $isModified; //last modified time

                    }

                }

                $i++;

            }

            $result[] = $sftp_path;

            echo $data = json_encode($result);

        }

        break;

    case "ReadFile":

        $path = $_POST["Directory"];

        $protocol = 'sftp';





        Stream::register($protocol);



        $context = [

            $protocol => ['sftp' => $sftp]

        ];

        $context = stream_context_create($context);



        $fp = fopen($protocol . '://' . $host . '/' . $path, 'r', false, $context);

        $temp = '';

        while (!feof($fp)) {

            $temp .= fread($fp, 1024);

        }

        fclose($fp);

        $result[] = $temp;





        echo $data = json_encode($result);



        break;



    case "CompareFile":

        $path = $_POST["Directory"];

        $protocol = 'sftp';

        $fileName = array_pop(explode("/", $path));



        if ((!in_array($fileName, $blockedFilesArr))) {

            Stream::register($protocol);



            $context = [

                $protocol => ['sftp' => $sftp]

            ];

            $context = stream_context_create($context);

    

            $fp = fopen($protocol . '://' . $host . '/' . $path, 'r', false, $context);

            $temp = '';

            while (!feof($fp)) {

                $temp .= fread($fp, 1024);

            }

            fclose($fp);

            // $result[] = $temp;

    

            $livePath = str_replace("devteam/", $prodPath, $path); // live path 

            // $result[] = $livePath;

            // Include the diff class

            $p =dirname(__FILE__);

            require_once dirname(__FILE__) . '/diffChecker/lib/Diff.php';

    

            // Include two sample files for comparison

            $a = explode("\n", $temp);

            $b = explode("\n", file_get_contents($livePath));

            

    

            // Options for generating the diff

            $options = array(

                //'ignoreWhitespace' => true,

                //'ignoreCase' => true,

            );

    

            // Initialize the diff class

            $diff = new Diff($a, $b, $options);

    

            // Generate a side by side diff

            require_once dirname(__FILE__) . '/diffChecker/lib/Diff/Renderer/Html/SideBySide.php';

            $renderer = new Diff_Renderer_Html_SideBySide;

            $result[] = $diff->Render($renderer);

    

            // // Generate an inline diff

            // require_once dirname(__FILE__) . '/../lib/Diff/Renderer/Html/Inline.php';

            // $renderer = new Diff_Renderer_Html_Inline;

            // echo $diff->render($renderer);

    

            // $fpl = fopen($livePath, 'r');

            // $templ = '';

            // if ($fpl) {

            //     while (!feof($fpl)) {

            //         $templ .= fread($fpl, 1024);

            //     }

            //     fclose($fpl);

            //     $result[] = $templ;

            // }

        }else{

            $result[] = "File Restricted";

        }

       

        echo $data = json_encode($result);



        break;



       case "CopyFile":

        $path = $_POST["Directory"];
        $copyType = $_POST["CopyType"];


        $protocol = 'sftp';

        $fileName = array_pop(explode("/", $path));
        $livePath = str_replace("devteam/", $prodPath, $path); // live path


        if ($copyType == "copyToTest") {

          $result[] = $sftp->put($path, file_get_contents($livePath));
        } else {

         $result[] = $sftp->get($path, $livePath);
        }

	//$result[] = "false";
        echo $data = json_encode($result);



        break;

}

  
