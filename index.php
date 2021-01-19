<!DOCTYPE html>
<html lang="en">
<?php
/*
	index.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

ini_set('display_errors', true);
ini_set('display_startup_errors', true);
error_reporting(E_ALL);






$urlPrefix = '/';





require_once('./functions-jcumap.php');
require_once('./functions-html.php');
require_once('./functions-course.php');
require_once('./functions-major.php');
require_once('./functions-program.php');
require_once('./functions-fdd.php');





$accreditationDisplayScript = '';
if ( isset($_GET['accreditationDisplay']) || ( isset($displayInformationForAccreditation) && $displayInformationForAccreditation) )
{
	$accreditationDisplayScript = 'accreditation.php';
}
$urlDisplayType = 'pretty';//'php'
$urlScript = ( $accreditationDisplayScript ) ? $accreditationDisplayScript : 'index.php';





// Display information for the given program/major/course code
$code = false;
$codeCourse = false;
$codeMajor = false;
$codeProgram = false;
$codeFDD = false;
if ( isset($_GET['program']) && strlen($_GET['program']) )
{
	$tmpCode = strtoupper(trim($_GET['program']));
	if ( 5 <= strlen($tmpCode) && strlen($tmpCode) <= 6 && ctype_upper($tmpCode) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeProgram = $tmpCode;
}
$programDefinitions = getDefinition('programs');
if ( isset($_GET['major']) && strlen($_GET['major']) )
{
	$tmpCode = strtoupper(trim($_GET['major']));
	if ( strlen($tmpCode) == 8 && ctype_upper( substr($tmpCode, 0, 4) ) && ctype_upper( substr($tmpCode, -3) ) && substr($tmpCode, 4, 1) == '-' )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeMajor = $tmpCode;
}
if ( isset($_GET['course']) && strlen($_GET['course']) )
{
	$tmpCode = strtoupper(trim($_GET['course']));
	if ( strlen($tmpCode) == 8 && ctype_upper( substr($tmpCode, 0, 4) ) && ctype_digit( substr($tmpCode, -4) ) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeCourse = $tmpCode;
}
if ( $accreditationDisplayScript && isset($_GET['fdd']) && strlen($_GET['fdd']) )
{
	$tmpCode = strtoupper(trim($_GET['fdd']));
	if ( 3 <= strlen($tmpCode) && ctype_upper($tmpCode) && !in_array($tmpCode, $programDefinitions) )
	{
		$_GET['code'] = $tmpCode;
	}
	$codeFDD = $tmpCode;
}
if ( isset($_GET['code']) && strlen($_GET['code']) )
{
	$code = strtoupper(trim($_GET['code']));
	$name = '';
	$shortname = '';
	$type = false;
	$program = false;
	$major = false;
	$course = false;
	$fdd = false;
	$courseCodes = array();
	if ( $codeCourse && strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$type = 'course';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeMajor && strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == '-' )
	{
		$type = 'major';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeProgram && 5 <= strlen($code) && strlen($code) <= 6 && ctype_upper($code) )
	{
		$type = 'program';
		$courseCodes = listAllCourseCodes();
	}
	else if ( $codeFDD && 3 <= strlen($code) && ctype_upper($code) )
	{
		$type = 'fdd';
	}
	else
	{
		$code = false;
	}
	switch ($type)
	{
		case 'course':
			createCourseDetails($code, $courseCodes, $course, $name, $shortname);
			break;
		case 'major':
			createMajorDetails($code, $codeProgram, $major, $program, $name, $shortname);
			break;
		case 'program':
			createProgramDetails($code, $program, $name, $shortname);
			break;
		case 'fdd':
			createFDDDetails($code, $fdd, $name, $shortname);
			break;
	}
	if ( !$code || !$type )
	{
		$code = false;
	}
	
	echo '<head>' . PHP_EOL;
	echo '	<meta charset="utf-8">' . PHP_EOL;
	echo '	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' . PHP_EOL;
	echo '	<meta name="description" content="CECS Professional Skills Mapping">' . PHP_EOL;
	echo '	<meta name="author" content="Rado Faletič">' . PHP_EOL;
	echo '	<meta name="keywords" content="CECS,EA,Engineers Australia,engineering,mapping">' . PHP_EOL;
	echo '	<meta name="format-detection" content="telephone=no">' . PHP_EOL;
	echo customCSS('	', PHP_EOL);
	echo '	<title>' . $shortname . ' :: Professional Skills Mapping :: CECS :: ANU</title>' . PHP_EOL;
	echo '</head>' . PHP_EOL;
	echo '<body class="container">' . PHP_EOL;
	echo '	<header><h1 class="display-1 text-center"><a class="text-reset" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '">CECS Professional Skills Mapping</a></h1></header>' . PHP_EOL;
	if ( $code && $type && $name )
	{
		switch ($type)
		{
			case 'course':
				displayCoursePage($name, $course, $urlPrefix, $accreditationDisplayScript);
				break;
			case 'major':
				displayMajorPage($name, $shortname, $major, $program, $codeProgram, $courseCodes, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript);
				break;
			case 'program':
				displayProgramPage($name, $program, $courseCodes, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript);
				break;
			case 'fdd':
				displayFDDPage($name, $fdd, $urlPrefix);
				break;
		}
	}
	else
	{
		echo '	' . resourceNotFound($type, $code) . PHP_EOL;
	}
	echo '	' . htmlPageFooter() . PHP_EOL;
	echo customJS('	', PHP_EOL, $urlPrefix);
	echo '</body>' . PHP_EOL;
}
else
{
	displayDefaultLandingPage($programDefinitions, $urlDisplayType, $urlPrefix, $urlScript, $accreditationDisplayScript);
}
?>
</html>
