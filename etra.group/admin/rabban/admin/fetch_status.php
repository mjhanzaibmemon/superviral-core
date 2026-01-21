<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

include('adminheader.php');
include('../orderfulfillraw.php');

$order_ids = json_decode(stripslashes($_POST['fulfill_id']),true);
$order_ids2 = json_decode(stripslashes($_POST['fulfill_id']),true);

//$order_ids = addslashes($_GET['fulfill_id']);
//$order_ids2 = addslashes($_GET['fulfill_id']);


foreach($order_ids as $eachId){


    $eachId = trim(addslashes($eachId));

    $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$eachId' LIMIT 1");

    while($info = mysql_fetch_array($q)){


        $order_idtoload[] = $info['fulfill_id'];

    }


}


$order_idtoload = implode('', $order_idtoload);
$order_idtoload = str_replace('  ', ' ', $order_idtoload);
$order_idtoload = explode(' ', $order_idtoload);
$order_idtoload= array_filter($order_idtoload);
$order_idtoload = implode(',', $order_idtoload);





            $order1 = $api->multiStatus($order_idtoload);

            $allretrievedinfo = $order1;
            //$allinfo = json_decode($allinfo);

            $allretrievedinfo = json_decode(json_encode($allretrievedinfo), true);


/*            echo '<pre>';

           print_r($allretrievedinfo[399489621]);
            print_r($allretrievedinfo);

            echo '</pre>';

*/




$status =[];
$count =[];
$hello =[];

//$order_ids2 = explode(',', $order_ids2);


foreach($order_ids2 as $sendresultbackid){

//for($i =0; $i < count($order_ids2); $i++){


    $thisorderid = addslashes($sendresultbackid);

    //$thisorderid = addslashes($fulfill_id[$i]);


    $q = mysql_query("SELECT * FROM `orders` WHERE `id` = '$thisorderid' LIMIT 1");

    while($info = mysql_fetch_array($q)){




            if($info['packagetype']=='followers'){



                    $dbfulfill_id = $info['fulfill_id'];
                    $dbfulfill_id = trim($dbfulfill_id);



                    $status[] = $allretrievedinfo[$dbfulfill_id]['status'];
                    $count[] = $allretrievedinfo[$dbfulfill_id]['start_count'];
                    $fulfillmentIDmakesure[] = $dbfulfill_id;



            }




            if(($info['packagetype']=='likes')||($info['packagetype']=='views')){


                    $dbfulfill_id = $info['fulfill_id'];




                    $dbfulfill_id = trim($dbfulfill_id);

                    $dbfulfill_ids = explode(' ',$dbfulfill_id);

                    foreach($dbfulfill_ids as $fulfillids243){

               

                        $this_status .= $allretrievedinfo[$fulfillids243]['status'].'<br>';
                        $this_count .= $allretrievedinfo[$fulfillids243]['start_count'].'<br>';
                        $this_fulfillmentIDmakesure .= $fulfillids243.'<br>';



                    }

                    $this_status = rtrim($this_status,'<br>');
                    $this_count = rtrim($this_count,'<br>');
                    $this_fulfillmentIDmakesure = rtrim($this_fulfillmentIDmakesure,'<br>');

                    $status[] = $this_status;
                    $count[] = $this_count;
                    $fulfillmentIDmakesure[] = $this_fulfillmentIDmakesure;



                    unset($dbfulfill_id);
                    unset($dbfulfill_id);
                    unset($this_status);
                    unset($this_count);
                    unset($this_fulfillmentIDmakesure);

            }



    }


}


echo json_encode(['status' => $status, 'startCount' => $count, 'fulfillmentID' => $fulfillmentIDmakesure]);



?>