<?php
/*
	functions-program.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-course.php');




// This function produces an array of all program codes available on this site.
// 	The format of the array is [program_code] => [program_code]
function listAllPrograms()
{
	$programs = array();
	$directory = opendir('./programs');
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 7 <= strlen($filename) && ctype_upper( substr($filename, 0, -4) ) && substr($filename, -4) == '.xml' && substr($filename, -17) != 'MappingResult.xml' )
		{
			$program = substr($filename, 0, -4);
			$programs[$program] = $program;
		}
    }
    ksort($programs);
    return $programs;
}





// This function creates major details and generates $name and $shortname that can be displayed in HTML
function createProgramDetails($code, &$program, &$name, &$shortname)
{
	$program = getProgram($code, 'full');
	if ( $program && $program->name )
	{
		$programDefinition = getDefinition($code);
		if ( $programDefinition && isset($programDefinition->name) )
		{
			$program->courses = $programDefinition->courses;
			$program->coursesForAggregating = array();
			if ( isset($programDefinition->coursesForAggregating) )
			{
				$program->coursesForAggregating = $programDefinition->coursesForAggregating;
			}
			else
			{
				$program->coursesForAggregating = $programDefinition->courses;
			}
			$program->majors = $programDefinition->majors;
			$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5);
			$shortname = htmlspecialchars($programDefinition->program, ENT_QUOTES|ENT_HTML5);
			if ( isset($program->majors) && $program->majors )
			{
				$name .= ' core';
				$shortname .= ' (core)';
			}
		}
	}
	else
	{
		$program = getDefinition($code);
		if ( $program && isset($program->name) )
		{
			if ( !isset($program->coursesForAggregating) )
			{
				$program->coursesForAggregating = $program->courses;
			}
			$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5);
			$shortname = htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5);
			if ( isset($program->majors) && $program->majors )
			{
				$name .= ' core';
				$shortname .= ' (core)';
			}
		}
	}
}





// This function is a specialised alias of getCourse(...)
function getProgram($code, $details = 'basic')
{
	return getCourse($code, array(), $details, 'program');
}
