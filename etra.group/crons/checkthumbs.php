<?php


include('../sm-db.php');
require dirname($_SERVER["DOCUMENT_ROOT"]).'/etra.group/common/s3/S3.php';


$s3 = new S3($amazons3key, $amazons3password);

$i = 1;


// superviral/FB code
    //Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

    $q = mysql_query("SELECT * FROM `ig_dp` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` DESC LIMIT 250");

    if(mysql_num_rows($q)=='0'){ echo'All Done<br>';}


    else{

       
    	echo 'Lets go<br>';

    	while($info = mysql_fetch_array($q)){

    		$actualimagename = $info['dp'];

    		$check = S3::getObjectInfo('cdn.superviral.io', 'dp/'.$actualimagename.'.jpg');



    		if(!empty($check['time'])){

    			$existsornot = '<font color="green">Exists</font>';

    		}
    			else{


    				$existsornot = '<font color="red">Not exist - Delete!</font>';

    				mysql_query("DELETE FROM `ig_dp` WHERE `id` = '{$info['id']}' LIMIT 1");


    			}


    				echo $i.'. '.$info['shortcode'].' - '.$actualimagename.': '.$existsornot.'<hr>';
            

    		$i++;

    		mysql_query("UPDATE `ig_dp` SET `checked` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

    		unset($check);


    	}

    echo '<meta http-equiv="refresh" content="0">';

    }



    $i = 1;


    //Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

    $q = mysql_query("SELECT * FROM `ig_thumbs` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` ASC LIMIT 300");

    if(mysql_num_rows($q)=='0'){echo'All Done for thumbs<br>';} 


    else{

    while($info = mysql_fetch_array($q)){

    	$actualimagename = md5('superviralrb'.$info['shortcode']);

    	$check = S3::getObjectInfo('cdn.superviral.io', 'thumbs/'.$actualimagename.'.jpg');



    	if(!empty($check['time'])){

    		$existsornot = '<font color="green">Exists</font>';

    	}
    		else{


    			$existsornot = '<font color="red">Not exist - Delete!</font>';

    			mysql_query("DELETE FROM `ig_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");

    			echo $i.'. '.$info['shortcode'].' - '.$actualimagename.': '.$existsornot.'<hr>';

    		}


        

    	$i++;

    	mysql_query("UPDATE `ig_thumbs` SET `checked` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

    	unset($check);


    }


    }
// end superviral/FB code

// tikoid code
    //Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

    $q = mysql_query("SELECT * FROM `tt_dp` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` DESC LIMIT 250");

    if(mysql_num_rows($q)=='0'){ echo'All Done<br>';}


    else{

        echo 'Lets go<br>';

        while($info = mysql_fetch_array($q)){


            $actualimagename = $info['dp'];

            $check = S3::getObjectInfo('cdn.superviral.io', 'tt-dp/'.$actualimagename.'.jpg');



            if(!empty($check['time'])){

                $existsornot = '<font color="green">Exists</font>';

            }
                else{


                    $existsornot = '<font color="red">Not exist - Delete!</font>';

                    mysql_query("DELETE FROM `tt_dp` WHERE `id` = '{$info['id']}' LIMIT 1");


                }


                    echo $i.'. '.$info['shortcode'].' - '.$actualimagename.': '.$existsornot.'<hr>';
            

            $i++;

            mysql_query("UPDATE `tt_dp` SET `checked` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

            unset($check);


        }

    echo '<meta http-equiv="refresh" content="0">';

    }



    $i = 1;


    //Showing rows 0 - 24 (492416 total, Query took 0.0006 seconds.) [id: 499711... - 499687...]

    $q = mysql_query("SELECT * FROM `tt_thumbs` WHERE `checked` = '0' AND `dnow` = '0' ORDER BY `id` ASC LIMIT 300");

    if(mysql_num_rows($q)=='0'){echo'All Done for thumbs<br>';} 


    else{

    while($info = mysql_fetch_array($q)){


        $actualimagename = md5('superviralrb'.$info['shortcode']);

        $check = S3::getObjectInfo('cdn.superviral.io', 'tt-thumbs/'.$actualimagename.'.jpg');



        if(!empty($check['time'])){

            $existsornot = '<font color="green">Exists</font>';

        }
            else{


                $existsornot = '<font color="red">Not exist - Delete!</font>';

                mysql_query("DELETE FROM `tt_thumbs` WHERE `id` = '{$info['id']}' LIMIT 1");

                echo $i.'. '.$info['shortcode'].' - '.$actualimagename.': '.$existsornot.'<hr>';

            }
        

        $i++;

        mysql_query("UPDATE `tt_thumbs` SET `checked` = '1' WHERE `id` = '{$info['id']}' LIMIT 1");

        unset($check);


    }


    }
// end tikoid code

