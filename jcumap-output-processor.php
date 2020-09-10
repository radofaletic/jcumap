<?php
/*
	jcumap-output-processor.php

	by © 2020, Dr Rado Faletič (rado.faletic@anu.edu.au)
*/

function biBoxArrowUpRight()
{
	return '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-box-arrow-up-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"/><path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"/></svg>';
}

function getDefinition($code)
{
	$jsonFile = "./definitions/" . $code . ".json";
	$json = false;
	if ( file_exists($jsonFile) )
	{
		$rawJsonData = file_get_contents($jsonFile);
		$json = json_decode($rawJsonData);
	}
	return $json;
}

function getCourse($code, $detail = 'basic')
{
	$data = new stdClass();
	$data->code = $code;
	$data->name = "";
	$data->description = "";
	$data->units = 0;
	$data->learningOutcomes = array();
	$data->data = false;
	$data->result = false;
	
	$dataFile = "./jcumap-output-files/" . $code . "_Data.xml";
	$mappingFile = "./jcumap-output-files/" . $code . "_Result.xml";
	
	// get basic information from $code_Data.xml
	if ( file_exists($dataFile) )
	{
		$rawXmlData = simplexml_load_file($dataFile);
		if ( $rawXmlData )
		{
			if ( strtoupper(trim($rawXmlData->SubjectInfo->SubjectCode)) == $data->code )
			{
				$data->data = $rawXmlData;
				$data->name = trim($rawXmlData->SubjectInfo->SubjectName);
				$data->description = trim($rawXmlData->SubjectInfo->SubjectDescription);
				$data->units = trim($rawXmlData->SubjectInfo->CreditPoints);
				if ( isset($rawXmlData->SLOCount) && $rawXmlData->SLOCount > 0 && isset($rawXmlData->SLOsAndMapping) )
				{
					foreach ($rawXmlData->SLOsAndMapping->children() as $mapping)
					{
						$data->learningOutcomes[] = htmlspecialchars(trim($mapping->SubjectLearningOutcome), ENT_QUOTES|ENT_HTML5);
					}
				}
			}
		}
	}
	
	return $data;
}

function getAllCourseCodes()
{
	$courses = array();
	$directory = opendir("./jcumap-output-files");
	while ( false !== ( $filename = readdir($directory) ) )
	{
		if ( 12 <= strlen($filename) && ctype_upper( substr($filename, 0, 4) ) && ctype_digit( substr($filename, 4, 4) ) && substr($filename, -4) == ".xml" )
		{
			$courseCode = substr($filename, 0, 8);
			$courses[$courseCode] = substr($filename, 0, 8);
		}
    }
    return $courses;
}

function generateLinkToProgramsAndCourses($code)
{
	$urlPrefix = "https://programsandcourses.anu.edu.au";
	$programPrefix = "/program";
	$majorPrefix = "/major";
	$coursePrefix = "/course";
	
	// check if code is a course, major, or program
	$code = trim($code);
	$url = $urlPrefix;
	if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_digit( substr($code, -4) ) )
	{
		$url .= $coursePrefix . "/" . $code;
	}
	else if ( strlen($code) == 8 && ctype_upper( substr($code, 0, 4) ) && ctype_upper( substr($code, -3) ) && substr($code, 4, 1) == "-" )
	{
		$url .= $majorPrefix . "/" . $code;
	}
	else if ( strlen($code) == 5 && ctype_upper($code) )
	{
		$url .= $programPrefix . "/" . $code;
	}
	return $url;
}

function generateHTMLfromJCUMap($inputFile, $outputType = "public")
{
	
	
}
