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
