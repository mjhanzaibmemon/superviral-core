<?php

    include_once 'header.php';

    $xmlString = '<?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
            

            <url>
            <loc>https://superviral.io</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/" />
          </url>
          <url>
            <loc>https://superviral.io/buy-instagram-followers/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/buy-instagram-followers/" />
          </url>
          
          <url>
            <loc>https://superviral.io/buy-instagram-likes/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/buy-instagram-likes/" />
          </url>
          
          <url>
            <loc>https://superviral.io/buy-instagram-views/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/buy-instagram-views/" />
          </url>
          
          <url>
            <loc>https://superviral.io/automatic-instagram-likes/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/automatic-instagram-likes/" />
          </url>
          
          <url>
            <loc>https://superviral.io/track-my-order/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/track-my-order/" />
          </url>
          
          <url>
            <loc>https://superviral.io/about-us/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/about-us/" />
          </url>
          
          <url>
            <loc>https://superviral.io/faq/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/faq/" />
          </url>
          
          <url>
            <loc>https://superviral.io/contact-us/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/contact-us/" />
          </url>
          
          <url>
            <loc>https://superviral.io/terms-of-service/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/terms-of-service/" />
          </url>
          
          <url>
            <loc>https://superviral.io/faq/payments/</loc>
            <xhtml:link rel="alternate" hreflang="en-gb" href="https://superviral.io/uk/faq/payments" />
          </url>
                      
            
    ';

    $q = mysql_query("SELECT * FROM `articles` WHERE `brand`='sv' AND superadmin_approve = 1 AND `article_type`='public' ORDER BY `written` DESC");

    while($info = mysql_fetch_array($q)){
        if($info['written'] > time()){continue;}

        if($info['country']=='us'){
            $loc = 'en-us';
            $url = 'https://superviral.io/blog/'.$info['url'];
        }
        if($info['country']=='uk'){
            $loc = 'en-gb';
            $url = 'https://superviral.io/uk/blog/'.$info['url'];
        }

        $xmlString .=  '<url>
            <loc>'.$url.'</loc>
        </url>';

    }

    $xmlString .= '</urlset>';

    $dom = new DOMDocument;
    $dom->preserveWhiteSpace = false;
    $dom->loadXML($xmlString);
    $dom->save("./sitemap.xml");

    echo 'done. check <a href="sitemap_test.xml">sitemap.xml</a>';