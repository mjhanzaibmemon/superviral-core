<?php


die;

///////////STEP 1

	$url = 'https://www.instagram.com/'.$info['igusername'].'/?__a=1';

	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	//curl_setopt($curl, CURLOPT_COOKIE, 'sessionid=8800287890%3AaGM3iBtR4PF8Aa%3A12');
	curl_setopt($curl, CURLOPT_COOKIE, $igsessionid);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);

	$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	$get = curl_exec($curl);

	$get = json_decode($get);


	curl_close($curl);

	if(!empty($get)){


	$arrays = $get -> graphql -> user -> edge_owner_to_timeline_media -> edges;

		foreach($arrays as $thumbnail){

		$isvideo = $thumbnail -> node -> is_video;

		if(($videosonly=='1')&&($isvideo=='0'))continue;

		$thumbnailurl = $thumbnail -> node -> thumbnail_src;
		$shortcode = $thumbnail -> node -> shortcode;



			$newimgname = md5('superviralrb'.$shortcode);
			if (!file_exists('thumbs/'.$newimgname.'.jpg')) {

			$ch = curl_init($thumbnailurl);
			$fp = fopen('thumbs/'.$newimgname.'.jpg', 'wb');
			curl_setopt($ch, CURLOPT_COOKIE, $igsessionid);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);

			}

		$imgs.='<div data-value="'.$shortcode.'###https://superviral.io/thumbs/'.$newimgname.'.jpg" class="img-responsive">
		<div class="amount">+399 '.$packagetype .'</div><img  src="https://superviral.io/thumbs/'.$newimgname.'.jpg" /></div>';

	}

	}

////////////STEP 2 - FIRST FAIL SAFE


/*	if(empty($imgs)){


	$url = 'https://buzzoid.com/api/instagram/'.$info['igusername'].'/posts';

	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);

	$get2 = curl_exec($curl);
	$get2 = json_decode($get2);

	curl_close($curl);
		if(!empty($get2)){
			$array2 = $get2 -> items;


				foreach($array2 as $thumbnail){

				$isvideo = $thumbnail -> type;

				if(($videosonly=='1')&&($isvideo=='IMAGE'))continue;

				$thumbnailurl = $thumbnail -> thumbnail;
				$shortcode = $thumbnail -> pcode;


				$imgs.='<div data-value="'.$shortcode.'###'.$thumbnailurl.'" class="img-responsive">
				<div class="amount">+399 '.$packagetype .'</div><img  src="'.$thumbnailurl.'" /></div>';
				}
		}
	}*/


////////////STEP 3 - SECOND FAIL SAFE
/*

	if(empty($imgs)){


	$url = 'https://goread.io/api/insta/'.$info['igusername'];

	$curl = curl_init(); 
	curl_setopt($curl, CURLOPT_URL, $url); 
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true); 
	curl_setopt($curl, CURLOPT_TIMEOUT, 15);



	$get3 = curl_exec($curl);
	$get3 = json_decode($get3);

	curl_close($curl);

	$array3 = $get3 -> items;


		foreach($array3 as $thumbnail){

		$isvideo = $thumbnail -> type;

		if(($videosonly=='1')&&($isvideo=='IMAGE'))continue;

		$thumbnailurl = $thumbnail -> thumbnail;
		$shortcode = $thumbnail -> pcode;


		$imgs.='<div data-value="'.$shortcode.'###'.$thumbnailurl.'" class="img-responsive">
		<div class="amount">+399 '.$packagetype .'</div><img  src="'.$thumbnailurl.'" /></div>';
		}


	}*/

if(empty($imgs))$nopostfound=1;

?>