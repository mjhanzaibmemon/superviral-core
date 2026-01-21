<?php

include_once('db.php');

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){

    if ($_SERVER['HTTP_X_FORWARDED_PROTO']=="http") {
    $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
            exit;
        }

    }



///
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];


    $headerscript = 'function myFunction() {
  document.getElementById("myDropdownnew").classList.toggle("show");
}

';


$nonwwlink = str_replace($_SERVER['HTTP_HOST'].$loclink,$_SERVER['HTTP_HOST'],'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

$footerlanguageshow = 'English ('.strtoupper($loc).' - '.$locas[$loc]['currencypp'].') - <a class="footerchangelanguagehref" title="American English (US)" href="'.str_replace($_SERVER['HTTP_HOST'],$_SERVER['HTTP_HOST'],$nonwwlink).'">English (US - USD)</a>
/ <a class="footerchangelanguagehref" title="British English (GB)" href="'.str_replace($_SERVER['HTTP_HOST'],$_SERVER['HTTP_HOST'].'/uk',$nonwwlink).'">English (UK - GBP)</a>';


/*

OLD FOOTER LINKS THAT CONTAIN /us/ 

if($loc=='ww'){

    $footerlanguageshow = 'English (EN) - 
    <a class="footerchangelanguagehref" title="English (EN)" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">English (EN)</a>
    / <a class="footerchangelanguagehref" title="American English (US)" href="https://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'].'">English (US - USD)</a>
    / <a class="footerchangelanguagehref" title="British English (GB)" href="https://'.$_SERVER['HTTP_HOST'].'/uk'.$_SERVER['REQUEST_URI'].'">English (UK - GBP)</a>

    ';

}else{

}
*/

if($blogsection=='1')unset($footerlanguageshow);


    $header = ' 
    
    <script type="application/ld+json">
    {
    "@context": "https://schema.org/",
    "@type": "Organization",
    "url": "https://superviral.io/",
    "logo": {
        "@type": "ImageObject",
        "url": "https://superviral.io/imgs/logo.png"
    },
    "name": "Superviral",
    "email": "support@superviral.io"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "WebSite",
        "name": "Superviral",
        "url": "https://superviral.io/{loclocation}",
        "inLanguage": "{contentlanguage}",
        "description": "{home_metadesc}"
    }
    </script>
    
    <script>
            var measurementId = "'. $measurementId .'";
    </script>
    
    <div class="headernew" align="center">

            <div class="cnwidth">

                <a class="mobilenavigationbtnnew" onclick="myFunction()" href="#">

                  <svg class="mobilemenubtnnew" xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 124 124"><path d="M112 6H12C5.4 6 0 11.4 0 18s5.4 12 12 12h100c6.6 0 12-5.4 12-12S118.6 6 112 6z"/><path d="M112 50H12C5.4 50 0 55.4 0 62c0 6.6 5.4 12 12 12h100c6.6 0 12-5.4 12-12C124 55.4 118.6 50 112 50z"/><path d="M112 94H12c-6.6 0-12 5.4-12 12s5.4 12 12 12h100c6.6 0 12-5.4 12-12S118.6 94 112 94z"/></svg>

                </a>

               <div class="navigationnew">




                    <ul class="desktopnavnew">
                        <li><a title="{htitle2}" href="'.$loclink.'{hhref2}">{hlink2}</a></li>
                        <li><a title="{htitle1}" href="'.$loclink.'{hhref1}">{hlink1}</a></li>
                        <li><a title="{htitle3}" href="'.$loclink.'{hhref3}">{hlink3}</a></li>
                        <li><a title="{htitle11}" href="'.$loclink.'{hhref11}">{hlink11}</a></li>
                        <li><a title="{htitle7}" href="'.$loclink.'{hhref7}">{hlink7}</a></li>
                        <li><a title="{hlinkaccount}" href="'.$loclink.'{hhref10}"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 510 510"><path d="M255 0C114.8 0 0 114.8 0 255s114.8 255 255 255 255-114.7 255-255S395.3 0 255 0zM255 76.5c43.4 0 76.5 33.2 76.5 76.5s-33.1 76.5-76.5 76.5c-43.3 0-76.5-33.1-76.5-76.5S211.7 76.5 255 76.5zM255 438.6c-63.7 0-119.8-33.1-153-81.6 0-51 102-79 153-79S408 306 408 357C374.9 405.5 318.8 438.6 255 438.6z"/></svg> {hlinkaccount}</a></li>

                    </ul>

                </div>




                <div class="logonew"><a title="Superviral" href="'.$loclink.'/" style="position:relative;">

<svg xmlns="http://www.w3.org/2000/svg" id="logo" x="0" y="0" viewBox="0 0 567.3 114" style="enable-background:new 0 0 567.3 114;version:1"><linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="11.9" y1="17.7" x2="90.6" y2="96.3"><stop offset="0" stop-color="#DA4453"/><stop offset="0.55" stop-color="#89216B"/><stop offset="1" stop-color="#4A00E8"/></linearGradient><path d="M18.5 92.7c-2.5-0.2-5.1-0.2-7.5-0.7C2.5 90.3-2.2 81.6 1 73.5c1.3-3.2 3-6.2 4.9-9.1 3.2-5 3.3-9.8 0-14.8 -1.7-2.6-3.3-5.3-4.5-8.1 -4.3-9.8 1.9-19.6 12.5-20 2.9-0.1 5.9-0.1 8.8 0 5.4 0.1 9.3-2.1 11.9-6.9 1.5-2.8 3.1-5.6 4.9-8.2 3.6-5.3 8.6-7.2 14.9-6C57.9 1 60.6 3 62.6 5.8c1.7 2.4 3.3 4.9 4.5 7.5 2.9 6 7.6 8.5 14.2 8.2 3.4-0.2 6.9-0.2 10.3 0.5 8.4 1.6 13.1 10.3 9.9 18.4 -1.2 2.9-2.7 5.8-4.5 8.4 -3.7 5.5-3.8 10.8 0 16.4 1.6 2.3 3 4.9 4.1 7.5 4.2 9.6-1.9 19.4-12.4 19.9 -2.9 0.1-5.9 0.1-8.8 0 -5.5-0.1-9.5 2.1-12.1 7 -1.5 2.8-3.1 5.6-4.9 8.2 -3.5 5.2-8.5 7.1-14.7 6 -3.6-0.7-6.3-2.7-8.4-5.6 -1.6-2.3-3.1-4.6-4.3-7.1 -3-6.4-7.9-9.1-14.8-8.5 -0.7 0.1-1.5 0-2.3 0C18.5 92.6 18.5 92.7 18.5 92.7z" style="clip-rule:evenodd;fill-rule:evenodd;fill:url(#SVGID_1_)"/><path d="M159.1 43.7c-2.7 0-4.7 1.8-4.7 4.3 0 2.6 0.3 2.8 8.6 5.4 10.7 3.3 14.1 7.2 14.1 14.7 0 9.6-8 17.1-18.2 17.1 -10.1 0-17.4-5.4-18.6-17.1h12.3c1.2 4.3 3.2 6.1 6.8 6.1 3.1 0 5.5-2.1 5.5-4.9 0-2.9-0.5-3.7-8.6-6.4 -10.1-3.3-14.1-7.5-14.1-14.9 0-8.6 7.4-15.3 16.9-15.3 8.6 0 16.3 5.6 16.7 15h-11.9C163.4 45 161.7 43.7 159.1 43.7L159.1 43.7zM218.5 84v-4.8c-4.6 4.4-8.4 6-14.6 6 -11.8 0-19.4-6.8-19.4-24.5V33.8h12.2v24.6c0 13.3 3.8 15.8 9.6 15.8 4.1 0 7.4-1.7 9.3-4.6 1.4-2.3 1.9-5.3 1.9-12V33.8h12.2V84H218.5L218.5 84zM267.9 85.2c-6.2 0-11-1.8-15.6-5.9v21.5H240v-67h11.2v5.9c3.9-4.5 9.8-7.1 16.7-7.1 14.7 0 25.4 11 25.4 26.1C293.3 73.9 282.6 85.2 267.9 85.2L267.9 85.2zM266.4 43.7c-8.5 0-14.9 6.5-14.9 15.1 0 8.8 6.4 15.3 15.1 15.3 8.1 0 14.4-6.6 14.4-15.1C281 50.3 274.7 43.7 266.4 43.7L266.4 43.7zM351.5 64.2h-39.4c1.5 6.1 6.8 9.9 14.1 9.9 5.1 0 8.1-1.4 11.2-5h13.3c-3.4 10.3-14 16-24.2 16 -15.5 0-27.6-11.4-27.6-26.1 0-14.8 11.8-26.5 26.8-26.5 15.2 0 26.3 11.4 26.3 27C352 61.4 351.9 62.5 351.5 64.2L351.5 64.2zM325.9 43.7c-7.3 0-12.2 3.5-14.1 10H340C338.4 47.2 333.4 43.7 325.9 43.7L325.9 43.7zM372.6 56.1V84h-12.2V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C376.2 44.4 372.6 48.3 372.6 56.1L372.6 56.1zM416.9 84h-9.7l-20.5-50.2h13.6L412.1 67l11.6-33.3h13.8L416.9 84 416.9 84zM443.6 26.5V14.5h12.2v12.1H443.6L443.6 26.5zM455.7 84h-12.2V33.8h12.2V84L455.7 84zM478.2 56.1V84H466V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C481.8 44.4 478.2 48.3 478.2 56.1L478.2 56.1zM534.3 84v-6.5c-4.9 5.4-9.7 7.7-16.9 7.7 -14.9 0-25.6-11-25.6-26.1 0-15.3 10.8-26.5 25.9-26.5 7.4 0 12.4 2.4 16.6 7.9v-6.7h11.2V84H534.3L534.3 84zM519 43.7c-8.7 0-14.9 6.5-14.9 15.7 0 8.8 6.2 14.8 15.1 14.8 9.3 0 14.8-6.5 14.8-14.9C533.9 50.1 527.7 43.7 519 43.7L519 43.7zM555.1 84V17h12.2v67H555.1L555.1 84z" fill="#231F20"/></svg>
<span style="position: absolute;right: -3px;bottom: -2px;font-size: 11px;color: #1a73e7;font-weight: bold;">{hsince}</span>
 </a></div>

                <a class="accountbtn" title="{hlinkaccount}" href="'.$loclink.'{hhref10}">

                  <svg class="mobilemenubtnnew" xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 510 510"><path d="M255 0C114.8 0 0 114.8 0 255s114.8 255 255 255 255-114.7 255-255S395.3 0 255 0zM255 76.5c43.4 0 76.5 33.2 76.5 76.5s-33.1 76.5-76.5 76.5c-43.3 0-76.5-33.1-76.5-76.5S211.7 76.5 255 76.5zM255 438.6c-63.7 0-119.8-33.1-153-81.6 0-51 102-79 153-79S408 306 408 357C374.9 405.5 318.8 438.6 255 438.6z"/></svg>

                </a>

            </div>

        </div>


        <div id="myDropdownnew" class="navcontainer dropdown-content">
                    <a title="{htitle0}" href="'.$loclink.'{hhref0}">
                    <svg class="mobicons1" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 27 26.82" style="enable-background:new 0 0 27 26.82;" xml:space="preserve">
                    <path class="st0" d="M23.43,15.05h0.88c1.46,0,2.19-1.76,1.16-2.79l-10.7-10.7c-0.7-0.7-1.83-0.7-2.53,0L1.56,12.23
                        c-1.03,1.03-0.3,2.79,1.16,2.79h0.87c0.25,0,0.45,0.2,0.45,0.45v8.22c0,1.16,0.94,2.1,2.1,2.1h14.79c1.16,0,2.1-0.94,2.1-2.1
                        l-0.03-8.19C22.99,15.25,23.19,15.05,23.43,15.05z"></path>
                    </svg>
                    {hlink0}</a>

                    <a title="{htitle2}" href="'.$loclink.'{hhref2}">
                    <svg class="mobicons2" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 24.07" style="enable-background:new 0 0 27 24.07;" xml:space="preserve">
                    <path class="st0" d="M24.07,2.95c-2.51-2.51-6.59-2.51-9.1,0L13.5,4.43l-1.47-1.47c-2.51-2.51-6.59-2.51-9.1,0
                        c-2.51,2.51-2.51,6.59,0,9.1l1.47,1.47l9.1,9.1l9.1-9.1l1.47-1.47C26.58,9.54,26.58,5.46,24.07,2.95z"/>
                    </svg>
                    {hlink2}</a>

                    <a title="{htitle1}" href="'.$loclink.'{hhref1}">
                    <svg class="mobicons3" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 25.4" style="enable-background:new 0 0 27 25.4;" xml:space="preserve">
                    <circle class="st0" cx="13.5" cy="8.01" r="6.91"/>
                    <path class="st0" d="M25.93,24.38v-1.69c0-2.5-2.03-4.53-4.53-4.53H5.6c-2.5,0-4.53,2.03-4.53,4.53v1.69H25.93z"/>
                    </svg>
                    {hlink1}</a>

                    <a title="{htitle3}" href="'.$loclink.'{hhref3}">
                    <svg class="mobicons4" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 15.39" style="enable-background:new 0 0 27 15.39;" xml:space="preserve">
                    <g>
                        <path class="st0" d="M25.76,7.18c-0.53-0.59-1.99-2.12-4.1-3.49c-2.63-1.7-5.34-2.58-8.05-2.62C9.32,1,5.17,3.05,1.25,7.16
                            c-0.29,0.3-0.29,0.76,0,1.07c3.85,4.05,7.94,6.1,12.16,6.1c0.06,0,0.13,0,0.19,0c2.72-0.04,5.43-0.92,8.05-2.62
                            c2.11-1.37,3.57-2.9,4.1-3.49C26.03,7.91,26.03,7.48,25.76,7.18z"/>
                    </g>
                    <ellipse class="st0" cx="13.45" cy="7.7" rx="5.13" ry="6.63"/>
                    </svg>
                    {hlink3}</a>

                    <a title="{htitle11}" href="'.$loclink.'{hhref11}">
                    <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" width="842.65" height="827.26" viewBox="0 0 842.65 827.26" style="width: 27px;height: 30px;"><defs><style>.cls-1{fill:none;stroke:#000;stroke-miterlimit:10;stroke-width:46.28px;}</style></defs><path class="cls-1" d="M933.81,930.9l-229-40.29a40,40,0,0,0-22.69,2.6,385.46,385.46,0,0,1-152.45,31C314.31,924,139.37,745.76,143,530.45,146.57,319.83,318.36,150.15,529.8,150c212.55-.14,386.78,173.44,387.38,386a385.45,385.45,0,0,1-66.41,218,40,40,0,0,0,.62,45.36L938.46,923.6A4.69,4.69,0,0,1,933.81,930.9Z" transform="translate(-119.81 -126.86)"></path></svg>
                    {hlink11}</a>




                    <a title="Automatic Instagram Likes" href="'.$loclink.'/automatic-instagram-likes/">
                    <svg class="mobicons9" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 69.5 68.6" style="enable-background:new 0 0 69.5 68.6;" xml:space="preserve">
                    <path class="st5" d="M49.6,23.1c-3.5-3.5-9.2-3.5-12.8,0l-2.1,2.1l-2.1-2.1c-3.5-3.5-9.2-3.5-12.8,0c-3.5,3.5-3.5,9.2,0,12.8L22,38
                        l12.8,12.7L47.5,38l2.1-2.1C53.1,32.4,53.1,26.7,49.6,23.1z"/>
                    <polygon class="st6" points="60.1,15.2 65.2,12.2 65.3,18.1 65.4,24 60.2,21.1 55.1,18.3 "/>
                    <polygon class="st6" points="6.4,54.7 5.8,48.9 11.2,51.3 16.6,53.7 11.8,57.2 7,60.6 "/>
                    <path class="st7" d="M62.9,19.1C50.8-3,17.9-2.7,6.3,19.7C2.9,26.2,1.8,33.8,3.3,41"/>
                    <path class="st7" d="M8.6,53.1c12,16.8,38.7,17.3,51.5,1.1c4.6-5.9,7-13.4,6.7-20.9"/>
                    </svg>

                    Automatic Instagram Likes</a>



                    <a title="{htitle4}" href="'.$loclink.'{hhref4}">
                    <svg class="mobicons5" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 19.43 27.09" style="enable-background:new 0 0 19.43 27.09;" xml:space="preserve">
  
                    <path d="M9.71,0.04c-5.34,0-9.67,4.33-9.67,9.67c0,0.92,0.13,1.81,0.37,2.65c0.14,0.48,0.3,1.01,0.59,1.55
                        c0.92,1.68,2.88,4.84,4.63,7.59c1.74,2.76,3.26,5.1,3.27,5.1l0,0c0.39,0.6,1.26,0.6,1.64,0l0,0c0,0,1.52-2.35,3.27-5.1
                        c1.75-2.76,3.71-5.91,4.63-7.6c0.29-0.53,0.45-1.07,0.59-1.55c0.24-0.84,0.37-1.73,0.37-2.65C19.39,4.37,15.06,0.04,9.71,0.04z
                         M17.13,11.83c-0.13,0.45-0.26,0.84-0.42,1.14c-0.86,1.58-2.83,4.75-4.56,7.49c-0.87,1.37-1.68,2.22-2.28,3.14
                        c-0.05,0.08-0.1,0.16-0.15,0.24c-0.05-0.08-0.1-0.16-0.15-0.24c-0.89-1.39-2.28-3.13-3.6-5.25c-1.32-2.12-2.6-4.2-3.24-5.38
                        c-0.17-0.3-0.3-0.69-0.42-1.14C2.1,11.15,2,10.45,2,9.72c0-2.13,0.86-4.06,2.26-5.46C5.66,2.86,7.58,2,9.71,2
                        c2.13,0,4.06,0.86,5.46,2.26c1.4,1.4,2.26,3.32,2.26,5.46C17.43,10.45,17.33,11.15,17.13,11.83z M9.71,4.91
                        c-2.65,0-4.81,2.15-4.81,4.81c0,1.13,0.39,2.17,1.05,2.99c0.88,1.1,2.24,1.82,3.76,1.82h0c1.52,0,2.88-0.71,3.76-1.82
                        c0.65-0.82,1.05-1.86,1.04-2.99C14.52,7.06,12.37,4.91,9.71,4.91z M11.94,11.49c-0.53,0.66-1.33,1.08-2.23,1.08v0
                        c-0.9,0-1.7-0.42-2.23-1.08C7.1,11,6.87,10.39,6.86,9.72c0-0.79,0.32-1.49,0.84-2.01c0.52-0.52,1.23-0.84,2.01-0.84
                        c0.79,0,1.49,0.32,2.01,0.84c0.52,0.52,0.84,1.23,0.84,2.01C12.56,10.39,12.33,11,11.94,11.49z"/>
                    </svg>
                    {hlink4}</a>

                    <a title="{htitle12}" href="{hhref12}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 16 20" style="height: 29px;width: 26px;top: 18px;"><path fill="#000" fill-rule="evenodd" d="M13 1.25H3A1.75 1.75 0 0 0 1.25 3v14c0 .966.784 1.75 1.75 1.75h10A1.75 1.75 0 0 0 14.75 17V3A1.75 1.75 0 0 0 13 1.25ZM3 0a3 3 0 0 0-3 3v14a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V3a3 3 0 0 0-3-3H3Z" clip-rule="evenodd"></path><path fill="#000" fill-rule="evenodd" d="M3.375 11c0-.345.28-.625.625-.625h4a.625.625 0 1 1 0 1.25H4A.625.625 0 0 1 3.375 11ZM3.375 8c0-.345.28-.625.625-.625h6a.625.625 0 1 1 0 1.25H4A.625.625 0 0 1 3.375 8ZM3.375 5c0-.345.28-.625.625-.625h8a.625.625 0 1 1 0 1.25H4A.625.625 0 0 1 3.375 5Z" clip-rule="evenodd"></path></svg>
                        {hlink12}
                    </a>

                    <a title="{htitle5}" href="'.$loclink.'{hhref5}">
                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 27" style="enable-background:new 0 0 27 27;" xml:space="preserve">

                    <path class="st0" d="M13.5,1.07L13.5,1.07c-6.84,0-12.38,5.54-12.38,12.38v12.52H13.5c6.84,0,12.38-5.54,12.38-12.38v-0.14
                        C25.88,6.61,20.34,1.07,13.5,1.07z"/>
                    <g>
                        <path class="st1" d="M12.42,15.79c0.1-0.36,0.25-0.69,0.46-1c0.21-0.3,0.52-0.65,0.91-1.03c0.44-0.44,0.77-0.78,0.97-1.03
                            c0.21-0.24,0.38-0.54,0.51-0.87c0.13-0.34,0.2-0.74,0.2-1.21c0-0.76-0.2-1.37-0.6-1.81c-0.4-0.45-0.98-0.67-1.73-0.67
                            c-0.47,0-0.9,0.09-1.27,0.28c-0.37,0.19-0.66,0.46-0.88,0.82c-0.21,0.36-0.32,0.77-0.33,1.26H9.57c0.01-0.69,0.17-1.28,0.49-1.79
                            c0.32-0.5,0.75-0.89,1.29-1.16c0.54-0.27,1.14-0.4,1.8-0.4c0.72,0,1.34,0.14,1.86,0.43c0.52,0.29,0.91,0.69,1.18,1.22
                            c0.27,0.52,0.4,1.13,0.4,1.81c0,0.53-0.09,1.01-0.27,1.45c-0.18,0.44-0.42,0.85-0.73,1.23c-0.31,0.38-0.7,0.79-1.18,1.23
                            c-0.37,0.32-0.63,0.7-0.79,1.12c-0.16,0.43-0.24,0.91-0.24,1.44h-1.11C12.27,16.59,12.32,16.15,12.42,15.79z M13.44,19.69
                            c0.14,0.14,0.21,0.32,0.21,0.53c0,0.21-0.07,0.39-0.21,0.53c-0.14,0.14-0.32,0.21-0.56,0.21c-0.23,0-0.41-0.07-0.55-0.21
                            c-0.14-0.14-0.21-0.32-0.21-0.53c0-0.22,0.07-0.39,0.21-0.53c0.14-0.14,0.32-0.21,0.55-0.21C13.11,19.48,13.3,19.55,13.44,19.69z"
                            />
                    </g>
                    </svg>
                    {hlink5}</a>

                    <a title="{htitle6}" href="'.$loclink.'{hhref6}">
                    <svg class="mobicons7" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 29.51" style="enable-background:new 0 0 27 29.51;" xml:space="preserve">
                    <path class="st0" d="M9.31,5.19l0.86-1.94c1.28-2.9,5.39-2.9,6.66,0l0.86,1.94c0.35,0.79,1.16,1.26,2.01,1.16l2.11-0.23
                        c3.15-0.34,5.2,3.22,3.33,5.77l-0.53,0.73c-0.94,1.28-0.94,3.02,0,4.3l0.53,0.73c1.87,2.55-0.19,6.11-3.33,5.77l-2.11-0.23
                        c-0.85-0.09-1.67,0.38-2.01,1.16l-0.86,1.94c-1.28,2.9-5.39,2.9-6.66,0l-0.86-1.94c-0.35-0.79-1.16-1.26-2.01-1.16l-2.11,0.23
                        c-3.15,0.34-5.2-3.22-3.33-5.77l0.53-0.73c0.94-1.28,0.94-3.02,0-4.3l-0.53-0.73c-1.87-2.55,0.19-6.11,3.33-5.77L7.3,6.35
                        C8.15,6.45,8.97,5.98,9.31,5.19z"/>
                    </svg>
                    {hlink6}</a>

                    <a title="{htitle7}" href="'.$loclink.'{hhref7}">
                    <svg class="mobicons8" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 27 20.22" style="enable-background:new 0 0 27 20.22;" xml:space="preserve">
                    <path class="st0" d="M23.34,19.17H3.66c-1.42,0-2.57-1.15-2.57-2.57V3.65c0-1.42,1.15-2.57,2.57-2.57h19.68
                        c1.42,0,2.57,1.15,2.57,2.57V16.6C25.91,18.02,24.76,19.17,23.34,19.17z"/>
                    <path class="st0" d="M24.7,2.59l-10.81,8.9c-0.22,0.18-0.57,0.18-0.79,0L2.3,2.59"/>
                    </svg>
                    {hlink7}</a>

        </div>';


    if($loclink == "/uk"){
            $dispNoneForUK = 'style ="display: none;"';

    }
    
    $footer = ' 

    <svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
      <linearGradient id="color3" x2="1" y2="1">
        <stop offset="0%" stop-color="#4a00e0" />
        <stop offset="100%" stop-color="#8e2de2" />
      </linearGradient>
    </svg>
    
        <div class="footer color1" align="center">
    
            <div class="cnwidth">
                    
                <div class="row1">
                        <div class="logo"><svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg></div>
                        
                   
                    <div class="linksContainer">
                       
                    <ul class="footerlinks">
                        <li>{htitlemain}</li>
                        <li><a title="{htitle1}" href="'.$loclink.'{hhref1}">{hlink1}</a></li>
                        <li><a title="{htitle2}" href="'.$loclink.'{hhref2}">{hlink2}</a></li>
                        <li><a title="{htitle3}" href="'.$loclink.'{hhref3}">{hlink3}</a></li>
                        <li><a title="{htitle11}" href="'.$loclink.'{hhref11}">{hlink11}</a></li>
                        <li><a title="Automatic Instagram Likes" href="'.$loclink.'/automatic-instagram-likes/">Get Automatic Instagram Likes</a></li>
                    </ul>   
                  
                    <ul class="footerlinks" '. $dispNoneForUK .'>
                        <li>{htitlemain4}</li>
                        <li><a title="{htitle13}" href="{hhref13}">{hlink13}</a></li>
                        <li><a title="{htitle14}" href="{hhref14}">{hlink14}</a></li>
                        <li><a title="{htitle15}" href="{hhref15}">{hlink15}</a></li>
                        <li><a title="{htitle16}" href="{hhref16}">{hlink16}</a></li>
                    </ul>

                    <ul class="footerlinks">
                        <li>{htitlemain2}</li>
                        <li><a title="{htitle8}" href="https://superviral.io/blog/">{hlink8}</a></li>
                        <li><a title="{htitle6}" href="'.$loclink.'{hhref6}">{hlink6}</a></li>
                        <li><a title="{htitle9}" href="'.$loclink.'{hhref9}">{hlink9}</a></li>
                    </ul>
    
                    <ul class="footerlinks">
                        <li>{htitlemain3}</li>
                        <li><a title="{htitle4}" href="'.$loclink.'{hhref4}">{hlink4}</a></li>
                        <li><a title="{htitle5}" href="'.$loclink.'{hhref5}">{hlink5}</a></li>
                        <li><a title="{htitle7}" href="'.$loclink.'{hhref7}">{hlink7}</a></li>
                    </ul>
                    </div>
                </div>
              
                <div class="row2">
                        <p class="copyright">'.$footerlanguageshow.'</p>
                    </div>
                    <div class="row2">
                        <p class="copyright">{footersupport}</p>
                        <p class="copyright">'.$locas[$loc]['footercopyright'].'</p>
                        <div class="payment-logos">
                        <div class="accepted"></div>
                        </div>
                    </div>
                    
            </div>
    
        </div>



        <script src="https://www.google.com/recaptcha/api.js"></script>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
        new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
        \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,\'script\',\'dataLayer\',"'. $gtmId .'");</script>
        <!-- End Google Tag Manager -->        
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id='. $gtmId .'"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        </script>




        ';

        $contentq = mysql_query("SELECT * FROM `content` WHERE brand='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home' AND `name`='metadesc') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home' AND `name`='metadesc')) LIMIT 1");
        while($cinfo = mysql_fetch_array($contentq)){$header = str_replace('{home_metadesc}',$cinfo['content'],$header);}


?>