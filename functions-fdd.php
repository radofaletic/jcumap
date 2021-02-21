<?php
/*
	functions-fdd.php

	by © 2020–2021 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-course.php');




// This function creates FDD details and generates $name and $shortname that can be displayed in HTML
function createFDDDetails($code, &$fdd, &$name, &$shortname)
{
	$fdd = getFDD($code, 'full');
	if ( $fdd && $fdd->name )
	{
		$name = '<span>' . $fdd->name . '</span>';
		$shortname = $fdd->name;
	}
}





// This function is a specialised alias of getCourse(...)
function getFDD($code, $details = 'basic')
{
	return getCourse($code, array(), $details, 'fdd');
}





// This function prints HTML for a single course
function displayFDDPage($name, $fdd, $urlPrefix)
{
	echo PHP_EOL . '<!-- begin FDD information -->' . PHP_EOL . PHP_EOL;
	echo '<section class="margintop padtop">' . PHP_EOL;
	echo '	<h2>' . $name . '</h2>' . PHP_EOL;
	echo '	<p class="msg-warn" role="alert">Note: information provided here is indicative only. This program may not be an allowable FDD combination with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations and for full and current information about this degree, or consult with CECS Student Services.</p>' . PHP_EOL;
	echo '	<table class="fullwidth tbl-row-bdr anu-long-area">' . PHP_EOL;
	echo '		<tbody>' . PHP_EOL;
	echo '			<tr><th><i>JCUMap</i> files: </th><td><ul class="noindent">';
	if ( file_exists('programs/' . $fdd->fileName . '.xml') )
	{
		echo '<li><a href="' . $urlPrefix . 'programs/' . $fdd->fileName . '.xml" download>' . $fdd->fileName . '.xml</a> ← <small>(download this file to revise the degree mapping)</small></li>';
	}
	if ( file_exists('programs/' . $fdd->fileName . 'MappingResult.xml') )
	{
		echo '<li><a href="' . $urlPrefix . 'programs/' . $fdd->fileName . 'MappingResult.xml" download>' . $fdd->fileName . 'MappingResult.xml</a></li>';
	}
	echo '</ul></td></tr>' . PHP_EOL;
	if ( isset($fdd->description) && $fdd->description )
	{
		echo '			<tr><th>description: </th><td class="small">' . str_replace("\n", '<br> ', str_replace("\r\n", '<br> ', trim($fdd->description))) . '</td></tr>' . PHP_EOL;
	}
	echo '			<tr><th>P&amp;C: </th><td><a href="' . generateLinkToProgramsAndCourses($fdd->code) . '">' . generateLinkToProgramsAndCourses($fdd->code) . '</a></td></tr>' . PHP_EOL;
	if ( isset($fdd->learningOutcomes) && $fdd->learningOutcomes )
	{
		echo '			<tr><th>learning outcomes: </th><td class="small"><ol class="noindent">';
		foreach ($fdd->learningOutcomes as $learningOutcome)
		{
			if ( $learningOutcome )
			{
				echo '<li>' . $learningOutcome . '</li>';
			}
		}
		echo '</ol></td></tr>' . PHP_EOL;
	}
	if ( isset($fdd->units) )
	{
		$unitContribution = number_format($fdd->units / 48, 1); // 48 units in one year
		echo '			<tr><th>engineering contribution: </th><td>' . number_format($fdd->units, 1) . ' units (' . $unitContribution . ' ';
		if ( $unitContribution == 1 || $unitContribution == 1.00 )
		{
			echo 'year';
		}
		else
		{
			echo 'years';
		}
		echo ')</td></tr>' . PHP_EOL;
	}
	echo '		</tbody>' . PHP_EOL;
	echo '	</table>' . PHP_EOL;
	
	// table of all learning outcomes mappings (to EA competencies and to assessment items)
	echo '	<section class="margintop padtop">' . PHP_EOL;
	echo '		<h3>Mapped learning outcomes</h3>' . PHP_EOL;
	echo '		<table class="fullwidth tbl-cell-bdr anu-long-area small">' . PHP_EOL;
	echo '			<colgroup>' . PHP_EOL;
	echo '				<col span="1">' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '					<col span="' . $competency->sublevels . '">' . PHP_EOL;
		}
	}
	echo '			</colgroup>' . PHP_EOL;
	echo '			<thead class="anu-sticky-header">' . PHP_EOL;
	echo '				<tr><th colspan="1" rowspan="2" class="text-center">learning outcome</th>';
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '<th colspan="' . $competency->sublevels . '" class="text-center">' . $competency->label . ' ' . $competency->text . '</th>';
		}
	}
	echo '</tr>' . PHP_EOL;
	echo '					<tr>';
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 2 )
		{
			echo '<th class="text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</th>';
		}
	}
	echo '</tr>' . PHP_EOL;
	echo '			</thead>' . PHP_EOL;
	echo '			<tbody>' . PHP_EOL;
	foreach ($fdd->learningOutcomes as $learningOutcomeN => $learningOutcome)
	{
		if ( $learningOutcome )
		{
			echo '				<tr><td><ol class="noindent" start="' . ( $learningOutcomeN + 1 ) . '"><li>' . $learningOutcome . '</li></ol></td>';
			foreach ($fdd->competencies as $competencyKey => $competency)
			{
				if ( $competency->level == 2 )
				{
					if ( isset($fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0 )
					{
						echo '<td class="text-center bg-college50">✓</td>';
					}
					else
					{
						echo '<td class="text-center"></td>';
					}
				}
			}
			echo '</tr>' . PHP_EOL;
		}
	}
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	echo '	</section>' . PHP_EOL;
	
	// display chart of development level learning against each of the competencies
	echo '	<section class="margintop padtop">' . PHP_EOL;
	echo '		<h3>Course contribution towards the ' . $fdd->competencyName . '</h3>' . PHP_EOL;
	echo '		<p>This table maps how the unit credits of this course contribute towards achievement of the ' . $fdd->competencyName . '.</p>' . PHP_EOL;
	$maxUnits = 1;
	foreach ($fdd->mappingData as $competencyLabel => $DLs)
	{
		$maxUnits = max($maxUnits, ceil(array_sum($DLs)));
	}
	echo '		<table class="fullwidth tbl-cell-bdr noborder anu-long-area small">' . PHP_EOL;
	echo '			<thead class="anu-sticky-header"><tr><th></th><th class="text-left bdr-left-solid"><img style="width: 1em; height: 1em;" src="//style.anu.edu.au/_anu/images/icons/web/first.png" alt="0.0" /> 0.0</th><th class="text-right bdr-right-solid">' . $maxUnits . '.0 <img style="width: 1em; height: 1em;" src="//style.anu.edu.au/_anu/images/icons/web/last.png" alt="' . $maxUnits . '.0" /></th></tr></thead>' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '				<tr><td colspan="3"><strong>' . $competency->label . ' ' . $competency->text . '</strong></td></tr>' . PHP_EOL;
		}
		else if ( $competency->level == 2 )
		{
			echo '				<tr><td style="width: 10%; " class="text-center" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</td><td colspan="2">' . PHP_EOL;
			echo '						<div class="fullwdith">';
			$mappingSum = array_sum($fdd->mappingData[$competency->label]);
			if ( $mappingSum > 0.0)
			{
				$mappingPercentage = 100 * $mappingSum / $maxUnits;
				echo '<div class="bg-college50" role="progressbar" style="display: inline; display: inline-block; width: ' . $mappingPercentage . '%" aria-valuenow="' . $mappingSum . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($mappingSum, 2) . '">&nbsp;</div>';
			}
			echo '</div>' . PHP_EOL;
			echo '					</td></tr>' . PHP_EOL;
		}
	}
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	echo '	</section>' . PHP_EOL;
	
	// list all EA competencies, and indicate which are addressed in this course
	echo '	<section class="margintop padtop">' . PHP_EOL;
	echo '		<h3>' . $fdd->competencyName . ' — summary</h3>' . PHP_EOL;
	echo '		<table class="fullwidth tbl-row-bdr noborder anu-long-area">' . PHP_EOL;
	echo '			<tbody>' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		switch ($competency->level)
		{
			case 1:
				echo '				<tr>';
				echo '<th colspan="4">' . $competency->label . ' ' . $competency->text . '</th>';
				echo '</tr>' . PHP_EOL;
				break;
			case 2:
				echo '				<tr class="small">';
				if ( $competency->competencyLevel > 0 )
				{
					echo '<td class="text-center">✓</td>';
				}
				else
				{
					echo '<td></td>';
				}
				echo '<td>' . $competency->label . '</td><td colspan="2">' . $competency->text . '</td>';
				echo '</tr>' . PHP_EOL;
				break;
			case 3:
				echo '				<tr class="small"><td class="small"></td>';
				if ( $competency->competencyLevel > 0 )
				{
					echo '<td class="small text-center">✓</td>';
				}
				else
				{
					echo '<td class="small"></td>';
				}
				echo '<td class="small">' . $competency->label . '</td><td class="small">' . $competency->text . '</td>';
				echo '</tr>' . PHP_EOL;
				break;
		}
	}
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	echo '	</section>' . PHP_EOL;
	echo '</section>' . PHP_EOL;
	echo PHP_EOL . '<!-- end FDD information -->' . PHP_EOL . PHP_EOL;
}
