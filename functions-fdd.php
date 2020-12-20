<?php
/*
	functions-fdd.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-course.php');




// This function creates FDD details and generates $name and $shortname that can be displayed in HTML
function createFDDDetails($code, &$fdd, &$name, &$shortname)
{
	$fdd = getFDD($code, 'full');
	if ( $fdd && $fdd->name )
	{
		$name = '<span class="fst-italic">' . $fdd->name . '</span>';
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
	echo '<main>' . PHP_EOL;
	echo '	<section>' . PHP_EOL;
	echo '		<h2>' . $name . '</h2>' . PHP_EOL;
	echo '		<div class="alert alert-warning fst-italic" role="alert">Note: information provided here is indicative only. This program may not be an allowable FDD combination with the ANU engineering degrees. See the specific engineering degree rules on P&amp;C for allowable combinations and for full and current information about this degree, or consult with CECS Student Services.</div>' . PHP_EOL;
	echo '		<table class="table">' . PHP_EOL;
	echo '			<tbody>' . PHP_EOL;
	echo '				<tr><th><i>JCUMap</i> files: </th><td><ul class="m-0">';
	if ( file_exists('programs/' . $fdd->fileName . '.xml') )
	{
		echo '<li><a href="' . $urlPrefix . 'programs/' . $fdd->fileName . '.xml" download>' . $fdd->fileName . '.xml</a></li>';
	}
	if ( file_exists('programs/' . $fdd->fileName . 'MappingResult.xml') )
	{
		echo '<li><a href="' . $urlPrefix . 'programs/' . $fdd->fileName . 'MappingResult.xml" download>' . $fdd->fileName . 'MappingResult.xml</a></li>';
	}
	echo '</ul></td></tr>' . PHP_EOL;
	if ( isset($fdd->description) && $fdd->description )
	{
		echo '				<tr><th>description: </th><td class="small">' . str_replace("\n", '<br> ', str_replace("\r\n", '<br> ', trim($fdd->description))) . '</td></tr>' . PHP_EOL;
	}
	echo '				<tr><th>P&amp;C: </th><td class="position-relative"><a class="stretched-link" href="' . generateLinkToProgramsAndCourses($fdd->code) . '">' . generateLinkToProgramsAndCourses($fdd->code) . '</a></td></tr>' . PHP_EOL;
	if ( isset($fdd->learningOutcomes) && $fdd->learningOutcomes )
	{
		echo '				<tr><th>learning outcomes: </th><td class="small"><ol>';
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
		echo '				<tr><th>engineering contribution: </th><td>' . number_format($fdd->units, 1) . ' units (' . $unitContribution . ' ';
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
	echo '			</tbody>' . PHP_EOL;
	echo '		</table>' . PHP_EOL;
	
	// table of all learning outcomes mappings (to EA competencies and to assessment items)
	echo '		<section>' . PHP_EOL;
	echo '			<h3>Mapped learning outcomes</h3>' . PHP_EOL;
	echo '			<table class="table table-sm table-bordered table-hover small">' . PHP_EOL;
	echo '				<colgroup>' . PHP_EOL;
	echo '					<col span="1">' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '					<col span="' . $competency->sublevels . '">' . PHP_EOL;
		}
	}
	echo '				</colgroup>' . PHP_EOL;
	echo '				<thead class="bg-light">' . PHP_EOL;
	echo '					<tr><th colspan="1" rowspan="2" class="text-center align-middle">learning outcome</th>';
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
			echo '<th class="text-center align-middle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</th>';
		}
	}
	echo '</tr>' . PHP_EOL;
	echo '				</thead>' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	foreach ($fdd->learningOutcomes as $learningOutcomeN => $learningOutcome)
	{
		if ( $learningOutcome )
		{
			echo '					<tr><td><ol class="m-0" start="' . ( $learningOutcomeN + 1 ) . '"><li>' . $learningOutcome . '</li></ol></td>';
			foreach ($fdd->competencies as $competencyKey => $competency)
			{
				if ( $competency->level == 2 )
				{
					echo '<td class="text-center text-success align-middle">';
					if ( isset($fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey]) && $fdd->learningOutcomesMapping[$learningOutcomeN][$competencyKey] > 0 )
					{
						echo '✓';
					}
					echo '</td>';
				}
			}
			echo '</tr>' . PHP_EOL;
		}
	}
	echo '				</tbody>' . PHP_EOL;
	echo '			</table>' . PHP_EOL;
	echo '		</section>' . PHP_EOL;
	
	// display chart of development level learning against each of the competencies
	echo '		<section>' . PHP_EOL;
	echo '			<h3>Course contribution towards the ' . $fdd->competencyName . '</h3>' . PHP_EOL;
	echo '			<p>This table maps how the unit credits of this course contribute towards achievement of the ' . $fdd->competencyName . '.</p>' . PHP_EOL;
	$maxUnits = 1;
	foreach ($fdd->mappingData as $competencyLabel => $DLs)
	{
		$maxUnits = max($maxUnits, ceil(array_sum($DLs)));
	}
	echo '			<table class="table table-sm table-hover small">' . PHP_EOL;
	echo '				<thead class="bg-light sticky-top"><tr><th class="col-1"></th><th class="text-start border-start">0.0</th>';
	echo '<th class="text-end border-end">' . $maxUnits . '.0</th>';
	echo '</tr></thead>' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		if ( $competency->level == 1 )
		{
			echo '					<tr class="table-secondary"><td colspan="3" class="fw-bold">' . $competency->label . ' ' . $competency->text . '</td></tr>' . PHP_EOL;
		}
		else if ( $competency->level == 2 )
		{
			echo '					<tr class="border-bottom"><td class="text-center align-middle border-end col-1" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-html="true" title="' . $competency->text . '">' . $competency->label . '</td><td colspan="2" class="align-middle">' . PHP_EOL;
			echo '						<div class="progress bg-transparent">';
			$mappingSum = array_sum($fdd->mappingData[$competency->label]);
			if ( $mappingSum > 0.0)
			{
				$mappingPercentage = 100 * $mappingSum / $maxUnits;
				echo '<div class="progress-bar bg-secondary" role="progressbar" style="width: ' . $mappingPercentage . '%" aria-valuenow="' . $mappingSum . '" aria-valuemin="0" aria-valuemax="' . $maxUnits . '" data-bs-toggle="tooltip" data-bs-placement="right" title="' . number_format($mappingSum, 2) . '"></div>';
			}
			echo '</div>' . PHP_EOL;
			echo '					</td></tr>' . PHP_EOL;
		}
	}
	echo '				</tbody>' . PHP_EOL;
	echo '			</table>' . PHP_EOL;
	echo '		</section>' . PHP_EOL;
	
	// list all EA competencies, and indicate which are addressed in this course
	echo '		<section>' . PHP_EOL;
	echo '			<h3>' . $fdd->competencyName . ' — summary</h3>' . PHP_EOL;
	echo '			<table class="table table-sm table-hover">' . PHP_EOL;
	echo '				<tbody>' . PHP_EOL;
	foreach ($fdd->competencies as $competencyKey => $competency)
	{
		switch ($competency->level)
		{
			case 1:
				echo '					<tr class="table-secondary">';
				echo '<th colspan="4">' . $competency->label . ' ' . $competency->text . '</th>';
				echo '</tr>' . PHP_EOL;
				break;
			case 2:
				echo '					<tr class="small">';
				if ( $competency->competencyLevel > 0 )
				{
					echo '<td class="text-center text-success align-top">✓</td>';
				}
				else
				{
					echo '<td></td>';
				}
				echo '<td>' . $competency->label . '</td><td colspan="2">' . $competency->text . '</td>';
				echo '</tr>' . PHP_EOL;
				break;
			case 3:
				echo '					<tr class="small"><td class="small"></td>';
				if ( $competency->competencyLevel > 0 )
				{
					echo '<td class="small text-center text-success align-top">✓</td>';
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
	echo '				</tbody>' . PHP_EOL;
	echo '			</table>' . PHP_EOL;
	echo '		</section>' . PHP_EOL;
	echo '	</section>' . PHP_EOL;
	echo '</main>' . PHP_EOL;
	echo PHP_EOL . '<!-- end FDD information -->' . PHP_EOL . PHP_EOL;
}
