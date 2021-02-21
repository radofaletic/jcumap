<?php
/*
	functions-html.php

	by © 2020–2021 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-course.php');





// This function returns a string that represents the P&C link logo
function pandcLinkIcon()
{
	return '<img style="width: 1em; height: 1em;" src="//style.anu.edu.au/_anu/images/icons/web/finder.png" onmouseover="this.src=\'//style.anu.edu.au/_anu/images/icons/web/finder-over.png\'" onmouseout="this.src=\'//style.anu.edu.au/_anu/images/icons/web/finder.png\'" alt="P&amp;C" />';
}





// This function generates an internal URL based on given configuration parameters
function createLink($display = 'pretty', $prefix = '/', $base = 'index.php', $q1 = array(), $q2 = array())
{
	$url = '';
	switch ($display)
	{
		case 'pretty':
			$url = $prefix;
			if ( $base != 'index.php' &&  substr($base, -4) == '.php' )
			{
				$url .= substr($base, 0, -4);
			}
			if ( is_array($q1) & count($q1) == 2 )
			{
				if ( substr($url, -1) != '/' )
				{
					$url .= '/';
				}
				if ( $q1[0] == 'fdd' )
				{
					$url .= 'FDD-';
				}
				$url .= $q1[1];
				if ( $q2 && count($q1) == 2 )
				{
					$url .= '/' . $q2[1];
				}
			}
			break;
		case 'php':
			$url = $prefix;
			if ( $base != 'index.php' )
			{
				$url .= $base;
			}
			if ( is_array($q1) && count($q1) == 2 )
			{
				$url .= '?' . $q1[0] . '=' . $q1[1];
				if ( $q2 && count($q2) == 2)
				{
					$url .= '&amp;' . $q2[0] . '=' . $q2[1];
				}
			}
			break;
	}
	return $url;
}





// This function generates an external URL to the Programs & Courses website based solely on the given code
function generateLinkToProgramsAndCourses($code)
{
	$urlPrefix = 'https://programsandcourses.anu.edu.au';
	$programPrefix = '/program';
	$majorPrefix = '/major';
	$coursePrefix = '/course';
	
	// check if code is a course, major, or program
	$code = trim($code);
	$url = $urlPrefix;
	if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$url .= $coursePrefix . '/' . $code;
	}
	else if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == '-' )
	{
		$url .= $majorPrefix . '/' . $code;
	}
	else if ( 3 <= strlen($code) && ctype_upper($code) )
	{
		$url .= $programPrefix . '/' . $code;
	}
	return $url;
}





// This functon returns a string for when a resource is not found
function resourceNotFound($type, $code)
{
	$notFound = '<section>';
	$notFound .= '<div class="msg-error" role="alert"><h3>Error</h3><p>';
	switch ($type)
	{
		case 'course':
			$notFound .= 'Could not find information for course “' . $code . '”.';
			break;
		case 'major':
			$notFound .= 'Could not find information for major “' . $code . '”.';
			break;
		case 'program':
			$notFound .= 'Could not find information for program “' . $code . '”.';
			break;
		default:
			$notFound .= 'Code information incorrect. Go back and try again.';
			break;
	}
	$notFound .= '</p></div>';
	$notFound .= '</section>';
	return $notFound;
}





// This function generates and displayed the HTML <head> content
function displayHTMLHead($title, $urlDisplayType = 'pretty', $urlPrefix = '/', $urlScript = 'index.php', $CSS = false)
{
	echo '<head>' . PHP_EOL;
	echo '	<meta charset="utf-8">' . PHP_EOL;
	echo '	<meta name="viewport" content="width=device-width, initial-scale=1.0" />' . PHP_EOL;
	echo '	<title>' . $title . ' - ANU</title>' . PHP_EOL;
	echo '	<meta name="dcterms.description" content="CECS Professional Skills Mapping" />' . PHP_EOL;
	echo '	<meta name="dcterms.subject" content="CECS,EA,Engineers Australia,engineering,mapping" />' . PHP_EOL;
	echo '	<meta name="dcterms.modified" content="2021-02-18" />' . PHP_EOL;
	echo '	<meta name="dcterms.creator" content="Dean, CECS" />' . PHP_EOL;
	echo '	<meta name="dcterms.creator" content="dean@cecs.anu.edu.au" />' . PHP_EOL;
	echo '	<meta name="description" content="CECS Professional Skills Mapping" />' . PHP_EOL;
	echo '	<meta name="keywords" content="CECS,EA,Engineers Australia,engineering,mapping" />' . PHP_EOL;
	echo '	<meta name="generator" content="ANU.template GW2-4 | ANU.appid GW-Drupal-7 | ANU.GMS 4.23_20160606 | ANU.inc_from style.anu.edu.au" />' . PHP_EOL;
	echo '	<meta name="dcterms.publisher" content="The Australian National University" />' . PHP_EOL;
	echo '	<meta name="dcterms.publisher" content="webmaster@anu.edu.au" />' . PHP_EOL;
	echo '	<meta name="dcterms.rights" content="http://www.anu.edu.au/copyright/" />' . PHP_EOL;
	echo '	<meta property="og:image"  content="http://style.anu.edu.au/_anu/4/images/logos/anu_logo_fb_350.png" />' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/4/images/logos/anu.ico" rel="shortcut icon" type="image/x-icon"/>' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/images/icons/web/anu-app-57.png" rel="apple-touch-icon" sizes="57x57"/>' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/images/icons/web/anu-app-76.png" rel="apple-touch-icon" sizes="76x76"/>' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/images/icons/web/anu-app-120.png" rel="apple-touch-icon" sizes="120x120"/>' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/images/icons/web/anu-app-152.png" rel="apple-touch-icon" sizes="152x152"/>' . PHP_EOL;
	echo '	<link href="https://style.anu.edu.au/_anu/images/icons/web/anu-app-180.png" rel="apple-touch-icon" sizes="180x180"/>' . PHP_EOL;
	echo '	<!--[if !IE]>--><link href="https://style.anu.edu.au/_anu/4/min/cecs.min.css?1" rel="stylesheet" type="text/css" media="screen"/><!--<![endif]-->' . PHP_EOL;
	echo '	<!--[if lt IE 8]><link href="https://style.anu.edu.au/_anu/4/min/cecs.ie7.min.css?1" rel="stylesheet" type="text/css" media="screen"/><![endif]-->' . PHP_EOL;
	echo '	<!--[if gt IE 7]><link href="https://style.anu.edu.au/_anu/4/min/cecs.ie8.min.css?1" rel="stylesheet" type="text/css" media="screen"/><![endif]-->' . PHP_EOL;
    
	echo '	<link href="https://style.anu.edu.au/_anu/4/style/anu-print.css?1" rel="stylesheet" type="text/css" media="print"/>' . PHP_EOL;
	echo '	<!-- jq -->' . PHP_EOL;
	echo '	<script src="https://style.anu.edu.au/_anu/4/scripts/jquery-1.11.3.min.js?1" type="text/javascript"></script>' . PHP_EOL;
	echo '	<script type="text/javascript">var $anujq = jQuery.noConflict();</script>' . PHP_EOL;
	echo '	<script src="https://style.anu.edu.au/_anu/4/scripts/jquery.hoverIntent.js?1" type="text/javascript"></script>' . PHP_EOL;
	echo '	<script src="https://style.anu.edu.au/_anu/4/min/anu-common.min.js?1" type="text/javascript"></script>' . PHP_EOL;
	echo '	<script src="https://style.anu.edu.au/_anu/4/min/anu-mega-menu.min.js?1" type="text/javascript"></script>' . PHP_EOL;
	echo '	<!-- ejq -->' . PHP_EOL;
	echo '	<!-- Google Tag Manager -->' . PHP_EOL;
	echo '	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':' . PHP_EOL;
	echo '		new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],' . PHP_EOL;
	echo '		j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=' . PHP_EOL;
	echo '		\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);' . PHP_EOL;
	echo '		})(window,document,\'script\',\'dataLayer\',\'GTM-5H3R8L\');' . PHP_EOL;
	echo '	</script>' . PHP_EOL;
	echo '	<!-- End Google Tag Manager -->' . PHP_EOL;
	echo '	<meta name="format-detection" content="telephone=no"/>' . PHP_EOL;
	// include customized CSS stripped from Bootstrap's CSS, in order to use their very nice Tooltips, https://getbootstrap.com/docs/5.0/components/tooltips/
	if ( $CSS )
	{
		echo '	<link href="' . createLink($urlDisplayType, $urlPrefix, 'index.php') . 'style/bootstrap.tooltip.css" rel="stylesheet"/>' . PHP_EOL;
	}
	echo '</head>' . PHP_EOL;
}





// This function generates and displays the HTML <body> content prior to the main page content
function displayHTMLBodyStart($title, $urlDisplayType = 'pretty', $urlPrefix = '/', $urlScript = 'index.php', $accreditationDisplayScript = '')
{
	echo '<body>' . PHP_EOL;
	echo '	<div id="devlmsg">THIS IS THE DEVELOPMENT VERSION OF THIS SITE</div>' . PHP_EOL;
	echo '	<!-- Google Tag Manager (noscript) -->' . PHP_EOL;
	echo '	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5H3R8L" height="0" width="0" style="display:none;visibility:hidden;"></iframe></noscript>' . PHP_EOL;
	echo '	<!-- End Google Tag Manager (noscript) -->' . PHP_EOL;
	echo '	<!-- noindex -->' . PHP_EOL;
	echo '	<div id="skipnavholder"><a id="skipnav" href="#content">Skip navigation</a></div>' . PHP_EOL;
	echo '	<div id="print-hdr">' . PHP_EOL;
	echo '		<div class="left"><img src="https://style.anu.edu.au/_anu/4/images/logos/anu_logo_print.png" alt="The Australian National University" height="40" width="115" /></div>' . PHP_EOL;
	echo '		<div class="right"></div>' . PHP_EOL;
	echo '		<div class="blockline"></div>' . PHP_EOL;
	echo '	</div>' . PHP_EOL;
	echo '	<div id="bnr-wrap" class="bnr-gwy-high noborder" role="banner">' . PHP_EOL;
	echo '		<div id="bnr-gwy" class="bnr-gwy-high">' . PHP_EOL;
	echo '			<div id="bnr-left"><a href="http://www.anu.edu.au/" class="anu-logo-png"><img class="text-white" src="https://style.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small.png" onmouseover="this.src=\'https://style.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small_over.png\';" onfocus="this.src=\'https://style.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small_over.png\';" onmouseout="this.src=\'https://style.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small.png\'" onblur="this.src=\'https://style.anu.edu.au/_anu/4/images/logos/2x_anu_logo_small.png\'" alt="The Australian National University" /></a></div>' . PHP_EOL;
	echo '			<div id="bnr-mid"><div class="left"><img src="https://style.anu.edu.au/_anu/4/images/logos/pipe_logo_small.png" alt="" width="66" height="51" class="anu-logo-pipe left" /></div><div class="left" id="bnr-h-lines"><div class="bnr-line-2 bnr-2linetop6"><a href="//cecs.anu.edu.au">ANU College of</a></div><div class="bnr-line-1 bnr-collegeof lnk-cecs bnr-2line"><h1><a href="//cecs.anu.edu.au">Engineering &amp; Computer Science</a></h1></div></div></div>' . PHP_EOL;
	echo '			<div id="bnr-right">' . PHP_EOL;
	echo '				<div class="bnr-gw2-search ">' . PHP_EOL;
	echo '					<form action="//find.anu.edu.au/search" method="get" id="SearchForm" role="search" autocomplete="off">' . PHP_EOL;
	echo '						<div>' . PHP_EOL;  
	echo '							<input type="hidden" name="filter" value="0" />' . PHP_EOL;
	echo '							<input type="hidden" name="client" value="anu_frontend" />' . PHP_EOL;
	echo '							<input type="hidden" name="proxystylesheet" value="anu_frontend" />' . PHP_EOL;
	echo '							<input type="hidden" name="site" value="default_collection" />' . PHP_EOL;
	echo '							<input type="hidden" name="btnG" value="Search" />' . PHP_EOL; 
	echo '							<label for="qt" class="scrnhide">Search query</label><input class="txt" name="q" id="qt" type="search" placeholder="Search ANU web, staff &amp; maps" autocomplete="off"><div class="srch-divide"><div class="srch-divide2"></div></div>' . PHP_EOL;
	echo '							<button value="Go" name="search1" id="search1" onclick="return checkInput(\'qt\',\'You must enter search terms\');" class="btn-go"><span class="scrnhide">Search</span> <img src="//style.anu.edu.au/_anu/4/images/buttons/search-black.png" alt=""></button>' . PHP_EOL; 
	echo '						</div>' . PHP_EOL;
	echo '					</form>' . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	echo '			</div>' . PHP_EOL;
	echo '			<div id="bnr-low" class="clear">' . PHP_EOL;
	echo '				<!--GW_UTILITIES-->' . PHP_EOL;
	echo '			</div>' . PHP_EOL;
	echo '		</div>' . PHP_EOL;
	echo '	</div>' . PHP_EOL;
	echo '	<!--GW_NAV_WRAP-->' . PHP_EOL;
	echo '	<!-- endnoindex --> ' . PHP_EOL;
	echo '	<div id="body-wrap" role="main">' . PHP_EOL;
	echo '		<div id="body">' . PHP_EOL;
	echo '			<!-- noindex -->' . PHP_EOL;
	echo '			<!-- stops ANU search from treating the menu as content-->' . PHP_EOL;
	echo '			<!-- responsive design quick search and collapsed menu -->' . PHP_EOL;
	echo '			<div class="search-boxes full nopadbottom show-rsp noprint">' . PHP_EOL;
	
	echo '				<div class="search-menu">' . PHP_EOL;
	echo '					<a href="#" onclick="slideDownFadeOutToggle(\'#menu\');return false;" style="cursor:pointer;"><img class="absmiddle" alt="" src="https://style.anu.edu.au/_anu/4/images/buttons/btn-menu.gif" /></a> <a href="#" onclick="slideDownFadeOutToggle(\'#menu\');return false;" style="cursor:pointer;">menu</a>' . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	
	echo '				<div class="search-form">' . PHP_EOL;
	echo '					<form action="http://find.anu.edu.au/search" method="get">' . PHP_EOL;
	echo '						<input type="hidden" name="client" value="anu_frontend">' . PHP_EOL;
	echo '						<input type="hidden" name="proxystylesheet" value="anu_frontend">' . PHP_EOL;
	echo '						<input type="hidden" name="site" value="default_collection">' . PHP_EOL;
	echo '						<input type="hidden" name="filter" value="0">' . PHP_EOL;
	echo '						<input type="hidden" name="dnavs" value="inmeta:gsaentity_sitetype=CECS Professional Skills Mapping" />' . PHP_EOL;
	echo '						<input type="hidden" name="q" value="inmeta:gsaentity_sitetype=CECS Professional Skills Mapping" />' . PHP_EOL;
	echo '						<input type="hidden" name="output" value="xml_no_dtd" />' . PHP_EOL;
	echo '						<label for="local-query-mini">Search site</label> <input class="search-query" name="as_q" id="local-query-mini" size="15" type="text" value="" />' . PHP_EOL;
	echo '						<label for="search-mini"><span class="nodisplay">search</span></label><input class="search-button" id="search-mini" title="Search" type="submit" value="GO" />' . PHP_EOL;
	echo '					</form>' . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	
	echo '			</div>' . PHP_EOL;
	echo '			<div id="menu" role="navigation">' . PHP_EOL;
	echo '				<div class="narrow">' . PHP_EOL;

	echo '					<!-- Start of Menu 1-->' . PHP_EOL;
	echo '					<div class="menu-flat menu-main">' . PHP_EOL;
	echo '						<p>Professional Skills Mapping</p>' . PHP_EOL;
	echo '						<ul id="nav-1">' . PHP_EOL;
	echo '							<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '#programs">Degree programs &amp; majors</a></li>' . PHP_EOL;
	echo '							<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '#ENGN">Engineering courses</a></li>' . PHP_EOL;
	echo '							<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '#COMP">Computing courses</a></li>' . PHP_EOL;
	echo '							<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '#other">Other courses</a></li>' . PHP_EOL;
	if ( $accreditationDisplayScript )
	{
		echo '							<li><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '#fdd">Double degrees</a></li>' . PHP_EOL;
	}
	echo '						</ul>	' . PHP_EOL;
	echo '					</div>' . PHP_EOL;
	echo '					<!-- End of Menu 1-->' . PHP_EOL;

	echo '					<!--Start of Menu 2-->	' . PHP_EOL;
	echo '					<div class="menu-flat menu-grey">' . PHP_EOL;
	echo '						<p>Related sites</p>' . PHP_EOL;
	echo '						<ul id="nav-2">' . PHP_EOL;
	echo '							<li><a href="https://programsandcourses.anu.edu.au">Programs and Courses</a></li>' . PHP_EOL;
	echo '							<li><a href="https://wattlecourses.anu.edu.au/">WATTLE</a></li>' . PHP_EOL;
	echo '							<li><a href="https://cecs.anu.edu.au/">ANU College of Engineering and Computer Science</a></li>' . PHP_EOL;
	echo '							<li><a href="https://www.anu.edu.au/students">Current students (ANU)</a></li>' . PHP_EOL;
	echo '						</ul>' . PHP_EOL;
	echo '					</div>' . PHP_EOL;
	echo '					<!--End of Menu 2-->' . PHP_EOL;
	
	echo '					<!-- don\'t touch-->' . PHP_EOL;
	echo '					<!-- endnoindex -->' . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	echo '			</div>' . PHP_EOL;
	echo '			<div id="content">' . PHP_EOL;

	echo '				<!-- START MAIN PAGE CONTENT -->' . PHP_EOL;
	echo '				<div class="doublewide">' . PHP_EOL;
	echo '					<h1 class="nounderline"><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '">' . $title . '</a></h1>' . PHP_EOL;
	echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
}





// This function generates and displays the HTML <body> content after to the main page content
function displayHTMLBodyEnd($urlDisplayType = 'pretty', $urlPrefix = '/', $urlScript = 'index.php', $javaScript = false)
{
	echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	echo '				<!-- END MAIN PAGE CONTENT -->' . PHP_EOL;
	echo '			</div>' . PHP_EOL;
	echo '			<!-- noindex -->' . PHP_EOL;
	echo '			<div id="update-wrap">' . PHP_EOL;
	echo '				<div id="update-details">' . PHP_EOL;
	echo '					<p class="sml-hdr">' . PHP_EOL;
	echo '						<span class="upd-date">Updated:&nbsp;&nbsp;<strong>18 February 2021</strong></span><span class="hpad">/</span>' . PHP_EOL;
	echo '						<span class="upd-officer">Responsible Officer:&nbsp;&nbsp;<a href="mailto:dean@cecs.anu.edu.au"><strong>Dean, CECS</strong></a></span><span class="hpad">/</span>' . PHP_EOL;
	echo '						<span class="upd-contact">Page Contact:&nbsp;&nbsp;<a href="mailto:studentadmin.cecs@anu.edu.au"><strong>CECS Academic Education Services</strong></a></span>' . PHP_EOL;
	echo '					</p>' . PHP_EOL;
	echo '				</div>' . PHP_EOL;
	echo '			</div>' . PHP_EOL;
	echo '			<!-- endnoindex -->' . PHP_EOL;
	echo '		</div>' . PHP_EOL;
	echo '	</div>' . PHP_EOL;

	echo '	<!--GW_SUBFOOTER-->' . PHP_EOL;

	echo '	<!-- noindex -->' . PHP_EOL;
	echo '	<div id="footer-wrap" role="contentinfo" class="gw2-footer">' . PHP_EOL;
	echo '	<div id="anu-footer">' . PHP_EOL;
	echo '		<div id="anu-detail">' . PHP_EOL;
	echo '			<ul>' . PHP_EOL;
	echo '				<li><a href="https://www.anu.edu.au/contact">Contact ANU</a></li>' . PHP_EOL;
	echo '				<li><a href="https://www.anu.edu.au/copyright">Copyright</a></li>' . PHP_EOL;
	echo '				<li><a href="https://www.anu.edu.au/disclaimer">Disclaimer</a></li>' . PHP_EOL;
	echo '				<li><a href="https://www.anu.edu.au/privacy">Privacy</a></li>' . PHP_EOL;
	echo '				<li><a href="https://www.anu.edu.au/freedom-of-information">Freedom of Information</a></li>' . PHP_EOL;
	echo '			</ul>' . PHP_EOL;
	echo '		</div>' . PHP_EOL;
	echo '		<div id="anu-address">' . PHP_EOL;
	echo '			<p>+61 2 6125 5111<br/>' . PHP_EOL;
	echo '				The Australian National University, Canberra<br/>' . PHP_EOL;
	echo '				CRICOS Provider : 00120C<br/>' . PHP_EOL;
	echo '				<span class="NotAPhoneNumber">ABN : 52 234 063 906</span></p>' . PHP_EOL;
	echo '		</div>' . PHP_EOL;
        
	echo '		<div id="anu-groups">' . PHP_EOL;
	echo '			<div class="anu-ftr-go8 hpad vpad"><a href="https://www.anu.edu.au/about/partnerships/group-of-eight"><img class="text-white" src="https://style.anu.edu.au/_anu/4/images/logos/2x_GroupOf8.png" alt="Group of Eight Member" /></a></div>' . PHP_EOL;
	echo '			<div class="anu-ftr-iaru hpad vpad"><a href="https://www.anu.edu.au/about/partnerships/international-alliance-of-research-universities"><img class="text-white" src="https://style.anu.edu.au/_anu/4/images/logos/2x_iaru.png" alt="IARU" /></a></div>' . PHP_EOL;
	echo '			<div class="anu-ftr-apru hpad vpad"><a href="https://www.anu.edu.au/about/partnerships/association-of-pacific-rim-universities"><img class="text-white" src="https://style.anu.edu.au/_anu/4/images/logos/2x_apru.png" alt="APRU" /></a></div>' . PHP_EOL;
	echo '			<div class="anu-ftr-edx hpad vpad"><a href="https://www.anu.edu.au/about/partnerships/edx"><img class="text-white" src="https://style.anu.edu.au/_anu/4/images/logos/2x_edx.png" alt="edX" /></a></div>' . PHP_EOL;

	echo '		</div>' . PHP_EOL;
	echo '		<div class="left ie7only full msg-warn" id="ie7-warn" >You appear to be using Internet Explorer 7, or have compatibility view turned on.  Your browser is not supported by ANU web styles.  <br>&raquo; <a href="https://webpublishing.anu.edu.au/steps/anu-web-environment/no_more_ie_7.php">Learn how to fix this</a>' . PHP_EOL;
            
	echo '			<br><span class="small" id="ignore-ie7-cookie">&raquo; <a href="https://webpublishing.anu.edu.au/steps/anu-web-environment/no_more_ie_7.php?ignore=1" >Ignore this warning in future</a></span>' . PHP_EOL;
	echo '		</div>	</div>' . PHP_EOL;
	echo '	</div>' . PHP_EOL;
	echo '	<!-- endnoindex -->' . PHP_EOL;

	echo '	<script>' . PHP_EOL;
	echo '		if (typeof ga === \'undefined\') {' . PHP_EOL;
	echo '				(function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){' . PHP_EOL;
	echo '				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),' . PHP_EOL;
	echo '				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)' . PHP_EOL;
	echo '				})(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');' . PHP_EOL;
	echo '			}' . PHP_EOL;
	echo '		ga(\'create\', \'UA-5266663-4\', \'auto\', {\'name\': \'allANU\'});' . PHP_EOL;
	echo '		ga(\'allANU.send\', \'pageview\');' . PHP_EOL;
	echo '	</script>' . PHP_EOL;

	echo '	<script type="text/javascript" src="https://style.anu.edu.au/_anu/4/scripts/anu-menu.js"></script>' . PHP_EOL;
	// include Bootstrap's JavaScript in order to use their very nice Tooltips, https://getbootstrap.com/docs/5.0/components/tooltips/
	if ( $javaScript )
	{
		echo '	<script src="' . createLink($urlDisplayType, $urlPrefix, 'index.php') . 'style/bootstrap.bundle.min.js"></script>' . PHP_EOL;
		echo '	<script>var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-bs-toggle="tooltip"]\')); var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });</script>' . PHP_EOL;
	}
	echo '</body>' . PHP_EOL;
}





// This function generates the default landing page
function displayDefaultLandingPage($programDefinitions, $urlDisplayType = 'pretty', $urlPrefix = '/', $urlScript = 'index.php', $accreditationDisplayScript = '')
{
	echo '<p class="msg-info" role="alert">Note: information provided here is indicative only. For full and current information view the official pages on <a href="https://programsandcourses.anu.edu.au/">Programs and Courses</a>.</p>' . PHP_EOL;
	
	echo '<section class="margintop padtop" id="programs">' . PHP_EOL;
	echo '	<h2>Mapped degree programs &amp; majors</h2>' . PHP_EOL;
	
	// get the programs
	$programs = array();
	$majors = array();
	if (isset($programDefinitions) && count($programDefinitions))
	{
		foreach ($programDefinitions as $program)
		{
			$program = getDefinition($program);
			if ( isset($program) && isset($program->name) )
			{
				$programType = 'other';
				if ( substr($program->name, 0, 8) == 'Bachelor' )
				{
					$programType = 'Bachelor';
				}
				else if ( substr($program->name, 0, 6) == 'Master' )
				{
					$programType = 'Master';
				}
				if ( !isset($programs[$programType]) )
				{
					$programs[$programType] = array();
				}
				$programs[$programType][$program->code] = $program;
				if ( isset($program->majors) && $program->majors )
				{
					foreach ($program->majors as $majorCode)
					{
						$major = getDefinition($majorCode);
						if ( isset($major) && isset($major->name) )
						{
							$majors[$major->code] = $major;
						}
					}
				}
			}
		}
	}
	
	if ( $programs ) {
		foreach ($programs as $programType => $progs)
		{
			echo '	<table class="fullwidth tbl-row-bdr noborder anu-long-area">' . PHP_EOL;
			echo '		<caption><h3>' . $programType . ' degrees</h3></caption>' . PHP_EOL;
			echo '		<thead class="anu-sticky-header">' . PHP_EOL;
			echo '			<tr><th class="small">code</th><th>name</th><th class="text-center">P&amp;C</th></tr>' . PHP_EOL;
			echo '		</thead>' . PHP_EOL;
			echo '		<tbody>' . PHP_EOL;
			foreach ($progs as $program)
			{
				echo '			<tr class="bg-college25"><td colspan="2"><h4 class="nopadbottom">';
				echo $program->name;
				echo '</h4></td><td class="text-center">';
				echo '<a href="' . generateLinkToProgramsAndCourses($program->code) . '">' . pandcLinkIcon() . '</a>';
				echo '</td></tr>' . PHP_EOL;
				echo '			<tr><td class="small">';
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code)) . '">' . $program->code . '</a>';
				echo '</td><td>';
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code)) . '">';
				if ( isset($program->majors) && $program->majors )
				{
					echo '<span>engineering core</span>: ';
				}
				echo htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . '</a>';
				echo '</td><td class="text-center">';
				echo '<a href="' . generateLinkToProgramsAndCourses($program->code) . '">' . pandcLinkIcon() . '</a>';
				echo '</td></tr>' . PHP_EOL;
				if ( isset($program->majors) && $program->majors )
				{
					foreach ($program->majors as $majorCode)
					{
						foreach ($majors as $major)
						{
							if ( $major->code == $majorCode )
							{
								echo '			<tr><td class="small">';
								echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . '">' . $major->code . '</a>';
								echo '</td><td>';
								echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . '"><span>major</span>: ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . '</a>';
								echo '</td><td class="text-center">';
								echo '<a href="' . generateLinkToProgramsAndCourses($major->code) . '">' . pandcLinkIcon() . '</a>';
								echo '</td></tr>' . PHP_EOL;
							}
						}
					}
				}
			}
			echo '		</tbody>' . PHP_EOL;
			echo '	</table>' . PHP_EOL;
		}
	} else {
		echo '	<div class="msg-error" role="alert"><h3>Error</h3><p>Could not load programs and majors.</p></div>' . PHP_EOL;
	}
	echo '</section>' . PHP_EOL;
	
	echo '<section class="margintop padtop" id="courses">' . PHP_EOL;
	echo '	<h2>Mapped courses</h2>' . PHP_EOL;
	$courses = listAllCourseCodes();
	$engnCourses = array();
	foreach ($courses as $courseKey => $courseFile)
	{
		if ( substr($courseKey, 0, 4) == "ENGN" )
		{
			$engnCourses[$courseKey] = $courseFile;
		}
	}
	$compCourses = array();
	foreach ($courses as $courseKey => $code)
	{
		if ( substr($courseKey, 0, 4) == "COMP" )
		{
			$compCourses[$courseKey] = $courseFile;
		}
	}
	$otherCourses = array();
	foreach ($courses as $courseKey => $courseFile)
	{
		if ( substr($courseKey, 0, 4) != "ENGN" && substr($courseKey, 0, 4) != "COMP" )
		{
			$otherCourses[$courseKey] = $courseFile;
		}
	}
	if ( $engnCourses )
	{
		echo '	<table class="fullwidth tbl-row-bdr noborder anu-long-area" id="ENGN">' . PHP_EOL;
		echo '		<caption><h3>Engineering</h3></caption>' . PHP_EOL;
		echo '		<thead class="anu-sticky-header">' . PHP_EOL;
		echo '			<tr><th class="small">code</th><th>name</th><th class="text-center">P&amp;C</th></tr>' . PHP_EOL;
		echo '		</thead>' . PHP_EOL;
		echo '		<tbody>' . PHP_EOL;
		foreach ($engnCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '			<tr><td class="small">';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td>';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center">';
			echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . pandcLinkIcon() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '		</tbody>' . PHP_EOL;
		echo '	</table>' . PHP_EOL;
	}
	if ( $compCourses )
	{
		echo '	<table class="fullwidth tbl-row-bdr noborder anu-long-area" id="COMP">' . PHP_EOL;
		echo '		<caption><h3>Computing</h3></caption>' . PHP_EOL;
		echo '		<thead class="anu-sticky-header">' . PHP_EOL;
		echo '			<tr><th class="small">code</th><th>name</th><th class="text-center">P&amp;C</th></tr>' . PHP_EOL;
		echo '		</thead>' . PHP_EOL;
		echo '		<tbody>' . PHP_EOL;
		foreach ($compCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '			<tr><td class="small">';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td>';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center">';
			echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . pandcLinkIcon() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '		</tbody>' . PHP_EOL;
		echo '	</table>' . PHP_EOL;
	}
	if ( !$engnCourses && !$compCourses )
	{
		echo '	<div class="msg-error" role="alert"><h3>Error</h3><p>Could not find any ENGN or COMP course mappings.</p></div>' . PHP_EOL;
	}
	if ( $otherCourses )
	{
		echo '	<table class="fullwidth table tbl-row-bdr noborder anu-long-area" id="other">' . PHP_EOL;
		echo '		<caption><h3>Other</h3></caption>' . PHP_EOL;
		echo '		<thead class="anu-sticky-header">' . PHP_EOL;
		echo '			<tr><th class="small">code</th><th>name</th><th class="text-center">P&amp;C</th></tr>' . PHP_EOL;
		echo '		</thead>' . PHP_EOL;
		echo '		<tbody>' . PHP_EOL;
		foreach ($otherCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '			<tr><td class="small">';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td>';
			if ( $course->name )
			{
				echo '<a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center">';
			echo '<a href="' . generateLinkToProgramsAndCourses($code) . '">' . pandcLinkIcon() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '		</tbody>' . PHP_EOL;
		echo '	</table>' . PHP_EOL;
	}
	echo '</section>' . PHP_EOL;
	
	if ( $accreditationDisplayScript )
	{
		require_once('./functions-program.php');
		echo '<section class="margintop padtop" id="fdd">' . PHP_EOL;
		echo '	<h2>Mapped non-engineering degree programs (for <abbr title="Flexible Double Degree">FDD</abbr>s)</h2>' . PHP_EOL;
		echo '	<p class="msg-warn">Note: not all of these programs are allowable FDD combinations with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations, or consult with CECS Student Services.</p>' . PHP_EOL;
		$fdds = listAllPrograms();
		if ( $fdds )
		{
			$fddPrograms = array();
			foreach ($fdds as $code)
			{
				$fdd = getFDD($code, 'basic');
				if ( !in_array($code, $programDefinitions) && $fdd->code == $code && $fdd->name )
				{
					$fddPrograms[$code] = $fdd->name;
				}
			}
			asort($fddPrograms);
			echo '	<table class="fullwidth tbl-row-bdr noborder anu-long-area">' . PHP_EOL;
			echo '		<thead class="anu-sticky-header">' . PHP_EOL;
			echo '			<tr><th class="small">code</th><th>name</th><th class="text-center">eng. years</th><th class="text-center">P&amp;C</th></tr>' . PHP_EOL;
			echo '		</thead>' . PHP_EOL;
			echo '		<tbody>' . PHP_EOL;
			foreach ($fddPrograms as $code => $name)
			{
				$fdd = getFDD($code, 'full');
				echo '			<tr>';
				echo '<td class="small"><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . '">' . $code . '</a></td>';
				echo '<td><a href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . '">' . htmlspecialchars($name, ENT_QUOTES|ENT_HTML5) . '</a></td>';
				echo '<td class="text-center">' . number_format($fdd->units / 48, 1) . '</td>';
				echo '<td class="text-center"><a href="' . generateLinkToProgramsAndCourses($code) . '">' . pandcLinkIcon() . '</a></td>';
				echo '</tr>' . PHP_EOL;
			}
			echo '		</tbody>' . PHP_EOL;
			echo '	</table>' . PHP_EOL;
		}
		else
		{
			echo '	<div class="msg-warn" role="alert"><h3>Notice</h3><p>Could not find any degree mappings.</p></div>' . PHP_EOL;
		}
		echo '</section>' . PHP_EOL;
	}
}
