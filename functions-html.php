<?php
/*
	functions-html.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-course.php');





// This function returns an SVG string that represent an “external link” icon,
// “Box arrow up-right” from the Bootstrap icons https://icons.getbootstrap.com/icons/box-arrow-up-right/
function biBoxArrowUpRight()
{
	return '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-box-arrow-up-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>';
}





// This function returns a string which provides custom CSS for this site
function customCSS($indent = '	', $eol = PHP_EOL)
{
	$indent = ( $indent ) ? '	' : '';
	$eol = ( $eol ) ? PHP_EOL : '';
	
	$customCSS = $indent . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-CuOF+2SnTUfTwSZjCXf01h7uYhfOBuxIhGKPbfEJ3+FqH/s6cIFN9bGr1HmAg4fQ" crossorigin="anonymous">' . $eol;
	return $customCSS;
}





// This function returns a string which provides custom JavaScript for this site
function customJS($indent = '	', $eol = PHP_EOL)
{
	$indent = ( $indent ) ? '	' : '';
	$eol = ( $eol ) ? PHP_EOL : '';
	
	$customJS = $indent . '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-popRpmFF9JQgExhfw5tZT4I9/CI5e2QcuUZPOVXb1m7qUmeR2b50u+YFEYe1wgzy" crossorigin="anonymous"></script>' . $eol;
	$customJS .= $indent . '<script>var tooltipTriggerList = [].slice.call(document.querySelectorAll(\'[data-toggle="tooltip"]\')); var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); })</script>' . $eol;
	return $customJS;
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
			if ( $q1 & count($q1) == 2 )
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
			if ( $q1 && count($q1) == 2 )
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





// This function returns a string that represent the generic page footer
function htmlPageFooter()
{
	return '<footer></footer>';
}





// This functon returns a string for when a resource is not found
function resourceNotFound($type, $code)
{
	$notFound = '<section>';
	$notFound .= '<div class="alert alert-danger" role="alert"><h3 class="alert-heading">Error</h3><p>';
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





// This function generates the default landing page
function printDefaultLandingPage($programDefinitions, $urlDisplayType = 'pretty', $urlPrefix = '/', $urlScript = 'index.php', $accreditationDisplayScript = '')
{
	echo '<head>' . PHP_EOL;
	echo '	<meta charset="utf-8">' . PHP_EOL;
	echo '	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">' . PHP_EOL;
	echo '	<meta name="description" content="CECS Professional Skills Mapping">' . PHP_EOL;
	echo '	<meta name="author" content="Rado Faletič">' . PHP_EOL;
	echo '	<meta name="keywords" content="CECS,EA,Engineers Australia,engineering,mapping">' . PHP_EOL;
	echo '	<meta name="format-detection" content="telephone=no">' . PHP_EOL;
	echo customCSS('	', PHP_EOL);
	echo '	<title>Professional Skills Mapping :: CECS :: ANU</title>' . PHP_EOL;
	echo '</head>' . PHP_EOL . PHP_EOL;
	
	echo '<body class="container">' . PHP_EOL;
	echo '	<h1 class="display-1 text-center"><a class="text-reset" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript) . '">CECS Professional Skills Mapping</a></h1>' . PHP_EOL;
	echo '	<div class="alert alert-info fst-italic" role="alert">Note: information provided here is indicative only. For full and current information view the official pages on P&amp;C.</div>' . PHP_EOL;
	
	echo '	<section id="programs">' . PHP_EOL;
	echo '		<h2>Mapped degree programs &amp; majors</h2>' . PHP_EOL;
	
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
				$programs[$program->code] = $program;
				if ( $program->majors )
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
	
	echo '		<div class="container">' . PHP_EOL;
	if ( $programs ) {
		echo '			<table class="table table-sm table-hover">' . PHP_EOL;
		echo '				<thead class="bg-light sticky-top">' . PHP_EOL;
		echo '					<tr><th class="small col-2">code</th><th>name</th><th class="text-center col-1">P&amp;C</th></tr>' . PHP_EOL;
		echo '				</thead>' . PHP_EOL;
		echo '				<tbody>' . PHP_EOL;
		foreach ($programs as $program)
		{
			echo '					<tr class="table-secondary"><td class="small"></td><td class="h3">';
			echo $program->name;
			echo '</td><td class="text-center position-relative">';
			echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($program->code) . '">' . biBoxArrowUpRight() . '</a>';
			echo '</td></tr>' . PHP_EOL;
			echo '					<tr><td class="small position-relative">';
			echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code)) . '">' . $program->code . '</a>';
			echo '</td><td class="position-relative">';
			echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array('program', $program->code)) . '">';
			if ( $program->majors )
			{
				echo '<span class="text-decoration-underline">engineering core</span>: ';
			}
			echo htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . '</a>';
			echo '</td><td class="text-center position-relative">';
			echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($program->code) . '">' . biBoxArrowUpRight() . '</a>';
			echo '</td></tr>' . PHP_EOL;
			if ( $program->majors )
			{
				foreach ($program->majors as $majorCode)
				{
					foreach ($majors as $major)
					{
						if ( $major->code == $majorCode )
						{
							echo '					<tr><td class="small position-relative">';
							echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . '">' . $major->code . '</a>';
							echo '</td><td class="position-relative">';
							echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("major", $major->code)) . '"><span class="text-decoration-underline">major</span>: ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . '</a>';
							echo '</td><td class="text-center position-relative">';
							echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($major->code) . '">' . biBoxArrowUpRight() . '</a>';
							echo '</td></tr>' . PHP_EOL;
						}
					}
				}
			}
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
	} else {
		echo '		<div class="alert alert-danger" role="alert"><h3 class="alert-heading">Error</h3><p>Could not load programs and majors.</p></div>' . PHP_EOL;
	}
	echo '		</div>' . PHP_EOL;
	echo '	</section>' . PHP_EOL;
	
	echo '	<section id="courses">' . PHP_EOL;
	echo '		<h2>Mapped courses</h2>' . PHP_EOL;
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
		echo '		<div class="container" id="ENGN">' . PHP_EOL;
		echo '			<table class="table table-sm table-hover caption-top">' . PHP_EOL;
		echo '				<caption class="h3">Engineering</caption>' . PHP_EOL;
		echo '				<thead class="bg-light sticky-top">' . PHP_EOL;
		echo '					<tr><th class="small col-2">code</th><th>name</th><th class="text-center col-1">P&amp;C</th></tr>' . PHP_EOL;
		echo '				</thead>' . PHP_EOL;
		echo '				<tbody>' . PHP_EOL;
		foreach ($engnCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '					<tr><td class="small position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td class="position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link fst-italic" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center position-relative">';
			echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . biBoxArrowUpRight() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
		echo '		</div>' . PHP_EOL;
	}
	if ( $compCourses )
	{
		echo '		<div class="container" id="COMP">' . PHP_EOL;
		echo '			<table class="table table-sm table-hover caption-top">' . PHP_EOL;
		echo '				<caption class="h3">Computing</caption>' . PHP_EOL;
		echo '				<thead class="bg-light sticky-top">' . PHP_EOL;
		echo '					<tr><th class="small col-2">code</th><th>name</th><th class="text-center col-1">P&amp;C</th></tr>' . PHP_EOL;
		echo '				</thead>' . PHP_EOL;
		echo '				<tbody>' . PHP_EOL;
		foreach ($compCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '					<tr><td class="small position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td class="position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link fst-italic" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center position-relative">';
			echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . biBoxArrowUpRight() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
		echo '		</div>' . PHP_EOL;
	}
	if ( !$engnCourses && !$compCourses )
	{
		echo '		<div class="container"><div class="alert alert-danger" role="alert"><h3 class="alert-danger">Error</h3><p>Could not find any ENGN or COMP course mappings.</p></div></div>' . PHP_EOL;
	}
	if ( $otherCourses )
	{
		echo '		<div class="container" id="other">' . PHP_EOL;
		echo '			<table class="table table-sm table-hover caption-top">' . PHP_EOL;
		echo '				<caption class="h3">Other</caption>' . PHP_EOL;
		echo '				<thead class="bg-light sticky-top">' . PHP_EOL;
		echo '					<tr><th class="small col-2">code</th><th>name</th><th class="text-center col-1">P&amp;C</th></tr>' . PHP_EOL;
		echo '				</thead>' . PHP_EOL;
		echo '				<tbody>' . PHP_EOL;
		foreach ($otherCourses as $code => $courseFile)
		{
			$course = getCourse($code, $courses, 'basic');
			echo '					<tr><td class="small position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . $code . '</a>';
			}
			else
			{
				echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . $code . '</a>';
			}
			echo '</td><td class="position-relative">';
			if ( $course->name )
			{
				echo '<a class="stretched-link fst-italic" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("course", $code)) . '">' . htmlspecialchars($course->name, ENT_QUOTES|ENT_HTML5) . '</a>';
			}
			echo '</td><td class="text-center position-relative">';
			echo '<a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . biBoxArrowUpRight() . '</a>';
			echo '</td></tr>' . PHP_EOL;
		}
		echo '				</tbody>' . PHP_EOL;
		echo '			</table>' . PHP_EOL;
		echo '		</div>' . PHP_EOL;
	}
	echo '	</section>' . PHP_EOL;
	
	if ( $accreditationDisplayScript )
	{
		require_once('./functions-program.php');
		echo '	<section id="fdd">' . PHP_EOL;
		echo '		<h2>Mapped non-engineering degree programs (for <abbr title="Flexible Double Degree">FDD</abbr>s)</h2>' . PHP_EOL;
		echo '		<div class="alert alert-warning">Note: not all of these programs are allowable FDD combinations with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations, or consult with CECS Student Services.</div>' . PHP_EOL;
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
			
			echo '			<div class="container">' . PHP_EOL;
			echo '				<table class="table table-sm table-hover caption-top">' . PHP_EOL;
			echo '					<thead class="bg-light sticky-top">' . PHP_EOL;
			echo '						<tr><th class="small col-2">code</th><th>name</th><th class="text-center col-1">eng. years</th><th class="text-center col-1">P&amp;C</th></tr>' . PHP_EOL;
			echo '					</thead>' . PHP_EOL;
			echo '					<tbody>' . PHP_EOL;
			foreach ($fddPrograms as $code => $name)
			{
				$fdd = getFDD($code, 'full');
				echo '						<tr>';
				echo '<td class="small position-relative"><a class="stretched-link" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . '">' . $code . '</a></td>';
				echo '<td class="position-relative"><a class="stretched-link fst-italic" href="' . createLink($urlDisplayType, $urlPrefix, $urlScript, array("fdd", $code)) . '">' . htmlspecialchars($name, ENT_QUOTES|ENT_HTML5) . '</a></td>';
				echo '<td class="text-center">' . number_format($fdd->units / 48, 1) . '</td>';
				echo '<td class="text-center position-relative"><a class="stretched-link" href="' . generateLinkToProgramsAndCourses($code) . '">' . biBoxArrowUpRight() . '</a></td>';
				echo '</tr>' . PHP_EOL;
			}
			echo '					</tbody>' . PHP_EOL;
			echo '				</table>' . PHP_EOL;
			echo '			</div>' . PHP_EOL;
		}
		else
		{
			echo '		<div class="container"><div class="alert alert-warning" role="alert"><h3 class="alert-warning">Notice</h3><p>Could not find any degree mappings.</p></div></div>' . PHP_EOL;
		}
		echo '	</section>' . PHP_EOL;
	}
	
	echo '	' . htmlPageFooter() . PHP_EOL;
	echo '</body>' . PHP_EOL;
}
