<?php

if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();
header('Content-type: text/html; charset=utf-8');
header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (24 * 60 * 60)));

$db=0;
include('header.php');

$tpl = file_get_contents('faq.html');

$_GET['id'] = addslashes($_GET['id']);


if($_GET['id']=='payments'){


		$faq1btn = 'color3';
	$faq2btn = 'faqactive';

	$faq = '<div class="faqchild">
					<div class="faq">

						<div class="opentitle">{div-payment-title-1}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-payment-desc-1}</div>
					</div>
					<div class="faq">

						<div class="opentitle">{div-payment-title-2}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-payment-desc-2}
						</div>
					</div>

					<div class="faq">

						<div class="opentitle">{div-payment-title-3}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-payment-desc-3}
                        </div>
					</div>


					<div class="faq">

						<div class="opentitle">{div-payment-title-4}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-payment-desc-4}</div>
					</div>


                    <div class="faq">

                        <div class="opentitle">{div-payment-title-5}<a class="btnicon" href="#">+</a></div>
                        <div class="message">{div-payment-desc-5}
                        </div>
                    </div>
				</div>';


}else{

	$faq1btn = 'faqactive';
	$faq2btn = 'color3';


	$faq = '
				<div class="faqchild">
					<div class="faq">

						<div class="opentitle">{div-title-1}<a class="btnicon" href="#">+</a></div>
						<div class="message">
						{div-desc-1}
						</div>
					</div>

					<div class="faq">

						<div class="opentitle">{div-title-2}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-desc-2}</div>
					</div>


					<div class="faq">

						<div class="opentitle">{div-title-3}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-desc-3}</div>
					</div>
				</div>

				<div class="faqchild">
					<div class="faq">

						<div class="opentitle"> {div-title-4}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-desc-4}
							</div>
					</div>


					<div class="faq">

						<div class="opentitle"> {div-title-5}<a class="btnicon" href="#">+</a></div>
						<div class="message">{div-desc-5}</div>
					</div>


					</div>';


}

$tpl = str_replace('{header}', $header, $tpl);
$tpl = str_replace('{headerscript}', $headerscript, $tpl);
$tpl = str_replace('{faq1btn}', $faq1btn, $tpl);
$tpl = str_replace('{faq2btn}', $faq2btn, $tpl);
$tpl = str_replace('{faq}', $faq, $tpl);
$tpl = str_replace('{footer}', $footer, $tpl);
$tpl = str_replace('{contentlanguage}', $locas[$loc]['contentlanguage'], $tpl);

$contentq = mysql_query("SELECT * FROM `content` WHERE `country` = '{$locas[$loc]['sdb']}' AND `page` IN ('faq', 'global') AND brand = 'to' ");
while($cinfo = mysql_fetch_array($contentq)){$tpl = str_replace('{'.$cinfo['name'].'}',$cinfo['content'],$tpl);}


echo $tpl;
?>