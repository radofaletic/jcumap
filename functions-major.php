<?php
/*
	functions-major.php

	by © 2020 Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

require_once('./functions-jcumap.php');
require_once('./functions-program.php');





// This function creates major details and generates $name and $shortname that can be displayed in HTML
function createMajorDetails($code, $codeProgram, &$major, &$program, &$name, &$shortname)
{
	$major = getDefinition($code);
	if ( $major && isset($major->name) )
	{
		$name = htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ' major';
		$shortname = $name;
		if ( $codeProgram )
		{
			$program = getProgram($codeProgram, 'full');
			if ( $program && $program->name )
			{
				$programDefinition = getDefinition($codeProgram);
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
					$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5). '  (major in ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
					$shortname = htmlspecialchars($programDefinition->program, ENT_QUOTES|ENT_HTML5). ' (' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
				}
			}
			else
			{
				$program = getDefinition($codeProgram);
				if ( $program && isset($program->name) )
				{
					if ( !isset($program->coursesForAggregating) )
					{
						$program->coursesForAggregating = $program->courses;
					}
					$name = htmlspecialchars($program->name, ENT_QUOTES|ENT_HTML5) . '  (major in ' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
					$shortname = htmlspecialchars($program->program, ENT_QUOTES|ENT_HTML5) . ' (' . htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ')';
				}
			}
		}
		if ( !$name )
		{
			$name = htmlspecialchars($major->name, ENT_QUOTES|ENT_HTML5) . ' major';
			$shortname = $name;
		}
	}
}
