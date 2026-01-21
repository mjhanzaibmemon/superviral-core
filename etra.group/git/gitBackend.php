<?php



require_once "config.php";

$result = array();



if(empty($blockedFilesArr))die();




if ($errorReporting == "true") {

    ini_set('display_errors', 1);



    ini_set('display_startup_errors', 1);



    

}




//require $_SERVER['DOCUMENT_ROOT'] . '/sftpext/vendor/autoload.php';

require '/home/etra/public_html/etra.group/git/sftpext/vendor/autoload.php';




use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SFTP\Stream;
use phpseclib3\Crypt\PublicKeyLoader;


$key = PublicKeyLoader::load($key);


$sftp = new SFTP($host);



$sftp->login($user, $key);





$sftp_path = $_POST['Directory'];

if ($sftp_path == "all") {

    $sftp_path = "/home/etra/public_html/test/";


}





$Type = $_POST["Type"];





  

switch ($Type) {

    case "ReadFolder":

     $sftp->setListOrder(true); // shift all folders at top

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

//////////////////////////////////////////////////////////////////////////// Diff checker and functions

    case "CompareFile":

        $path = $_POST["Directory"];
        $path = str_replace('//','/',$path);

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

           

            //$livePath = str_replace("/home/etra/public_html/", $prodPath, $path); // live path 
            $livePath = $path;
            $livePath = str_replace('test/','',$livePath);
            $testPath = $path;
            // $result[] = $livePath;

            // Include the diff class

            $p ='/home/etra/public_html/';



            require_once '/home/etra/public_html/etra.group/git/diffChecker/lib/Diff.php';

            require_once('/home/etra/public_html/etra.group/git/diffChecker/lib/Diff/Renderer/Html/SideBySide.php');
            

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



    

            $renderer = new Diff_Renderer_Html_SideBySide;



            $result[] = $diff->Render($renderer);

            


        }else{

            $result[] = "File Restricted";

        }

       

        echo $data = json_encode($result);



        break;





       case "CopyFile":

        $testPath = $_POST["Directory"];
        $copyType = $_POST["CopyType"];


        $protocol = 'sftp';

        $fileName = array_pop(explode("/", $testPath));
        $livePath = $testPath;
        $livePath = str_replace('test/','',$livePath);

        if ($copyType == "copyToTest") {

          $result[] = $sftp->put($testPath, file_get_contents($livePath));
        } else {

         $result[] = $sftp->get($testPath, $livePath);
        }

    	//$result[] = "false";
        echo $data = json_encode($result);



        break;

        


        

}

  
