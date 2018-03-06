<?php

// Project 4 (PHP)
// Author: Ben Holzhauer
// Date: 11/20/2017
// Class: CS316-001
// Purpose: Run webpage FanXelk, where users can access past sports scores/stats

// Implementations:
// A) The program performs basic reporting on JSON objects properly
// B) The program performs searches and highlights the included search term
// C) The program correctly reads files and converts them to JSON objects
// D) The program presents an HTML form with dynamically-populated fields and options
// E) The program is secure and returns appropriate error messages to the user
// F) The program handles missing/incorrect JSON fields and elements

// The HTML form and PHP code are both included in this PHP file, as it seemed more practical and personally easier to do


// Function to get valid JSON data from given file
function getJSONData($JSONFilename)
{
	 // Check if file exists
        if (!file_exists($JSONFilename))
        {
                echo "<br>JSON file does not exist.";
		return 0;
        }
        else
        {
                // Open file
                $file = file_get_contents($JSONFilename);

                // Decode JSON data into data array
                $data = json_decode($file, true);

                // Check if file is invalid (improper JSON)
                if (json_last_error() != JSON_ERROR_NONE)
                {
                        echo "<br>JSON file is invalid.";
			return 0;
                }
		// Otherwise, return JSON data
		else
		{
			return $data;
		}
        }
}

// Function to show results, given JSON file and search term
function showResults($filename, $searchterm)
{
	// Get results JSON data from file
	$resultsData = getJSONData($filename);

	// Make sure results can be displayed
	if ($resultsData != NULL)
	{
		// Display comments from file
		foreach ($resultsData['comments'] as $comment)
		{
			echo "<div class='comment'>" , $comment , "</div>";
		}

		// Initialize win and lose counts
		$wins = 0;
		$losses = 0;

		// Display table headers
		echo "<hr><table>";
		echo "<tr>";
		foreach ($resultsData['games'][0] as $tableHeader => $tableItem)
		{
			echo "<th>" , $tableHeader , "</th>";
		}
		echo "</tr>";

		// Display stats for each game
		foreach ($resultsData['games'] as $game)
		{
			echo "<tr>";
			foreach ($game as $key => $value)
			{
				echo "<td>";
				// Make search term stats bold
				if ($searchterm == $key)
				{
					echo "<b>";
				}

				// Display stats
				echo $value;

				// Finishing making stats bold
				if ($searchterm == $key)
				{
					echo "</b>";
				}
				echo "</td>";

				// Iterate win or lose count, given stats
				if ($key == "WinorLose")
				{
					if ($value == "W")
					{
						$wins += 1;
					}
					else if ($value == "L")
					{
						$losses += 1;
					}
				}
			}
			echo "</tr>";
		}
		echo "</table><hr>";

		// Determine winning chance percentage from counts
		$chance = ( +$wins / (+$losses + +$wins) ) * 100;

		// Display wins, losses, and winning chance
		echo "<table><tr><th>Wins</th><th>Losses</th><th>Winning Chance</th></tr>";
		echo "<tr><td>" , $wins , "</td><td>" ,  $losses , "</td><td>" , $chance , "%</td></tr>";
		echo "</table>";
	}
}

// Function to show webpage for user input
function showWebpage()
{
	// Get initial JSON data from sports file for webpage
	$sportsData = getJSONData("Sports.json");

	// Make sure sports file exists
	if ($sportsData != NULL)
	{
		// Initialize arrays of sport titles, result years, result files, and search terms
		$titles = [];
		$resultYears = [];
		$resultFiles = [];
		$searchTerms = [];

		// Get sport titles, result years, and search terms for user selection
		foreach ($sportsData['sport'] as $sport)
                {
			// Get sport titles
                        array_push($titles, $sport['title']);

			// Get result years and JSON files
			foreach ($sport['results'] as $resultYear => $resultFile)
			{
				array_push($resultYears, $resultYear);
				array_push($resultFiles, $resultFile);
			}
		
			// Get search terms
			foreach ($sport['searchterms'] as $term)
			{
				// Only get unique terms
				if (!in_array($term, $searchTerms))
				{
					array_push($searchTerms, $term);
				}
			}
                }

		// Begin displaying webpage
		echo "
		<!DOCTYPE html>
		<html>
        	<head>
                <title>FanXelk</title> ";

		// Define webpage styling
		echo "
		<style type='text/css'>
		body { font-family: Verdana; background-color: #EEF3F6; color: #1C3051; }
		h1 { text-decoration: underline; }
		b { color: #1493FF; }
		div { display: inline-block; padding-left: 10px; padding-right: 10px; }
		.comment { font-style: italic; padding-left: 15px; padding-right: 15px; }
		#wide { border-width: 2px; border-color: #1C3051; }
		table { text-align: center; }
		th, tr, td { padding: 5px; }
		</style> ";

		// Display webpage body
		echo "
        	</head>
        	<body>
		<center>
		<h1>FanXelk</h1>
                <form method='POST' action=''> ";

		// Allow user to select sport title
		echo "<div>Sport Title:<br>";
		echo "<select name='title'>";
		foreach ($titles as $title)
		{
			echo "<option value='" , $title , "'>" , $title , "</option>";
		}
		echo "</select></div>";

		// Allow user to select result year
		echo "<div>Results Year:<br>";
                echo "<select name='results'>";
                foreach ($resultYears as $year)
                {
                        echo "<option value='" , $year , "'>" , $year , "</option>";
                }
                echo "</select></div>";

		// Allow user to select search term
		echo "<div>Search Term:<br>";
                echo "<select name='searchterms'>";
                foreach ($searchTerms as $search)
                {
                        echo "<option value='" , $search , "'>" , $search , "</option>";
                }
                echo "</select></div><br>";
		
		// Enable submission
		echo "<br><input type='submit' value='See Results'/></form>";
		echo "<br><hr id='wide'></hr>";

		// Wait for user to submit form data
		$submitted = $_POST;
		if ($submitted)
		{
			// Get JSON filename
			$resultsIndex = array_search($_POST['results'], $resultYears);
			$resultsFilename = $resultFiles[$resultsIndex];

			// Get search term
			$resultsSearchterm = $_POST['searchterms'];

			// Initialize flag for existing results as false
			$noResults = true;

			// Make sure results year exists for sport title
			foreach ($sportsData['sport'] as $sportItem)
			{
				// Find sport in JSON file given title
				if ($sportItem['title'] == $_POST['title'])
				{
					// Make sure sport has results
					if ($sportItem['results'] != NULL)
					{
						foreach ($sportItem['results'] as $results)
						{
							// Check if results year is valid for sport title
							if ($resultsFilename == $results)
							{
								// Show results, given filename and search term
								showResults($resultsFilename, $resultsSearchterm);
								$noResults = false;
							}
						}
					}
				}
			}

			// Display error if no results exists
			if ($noResults)
			{
				echo "<br>No such results could be found.";
			}
		}

		// Finish displaying webpage
                echo "
		</center>
                </body>
                </html> ";
	}
}

// Show webpage for user
showWebpage();

?>
