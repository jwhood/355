<?php
session_start(); // needed to be able to use the $_SESSION variable

// if admin was not logged in, then redirect user to page index.php
if (!array_key_exists("admin", $_SESSION) || $_SESSION["admin"] != "fuzzy")
	header("Location: index.php");

// if we're not coming from page admin_bodyparts.php then redirect to page index.php
if (!array_key_exists("hdnBodyPartId", $_POST) || $_POST["hdnBodyPartId"] == "")
	header("Location: index.php");
?>

<!DOCTYPE html>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Add/Edit/Delete Symptoms</title>

		<style>
			body {
				background: gainsboro; /* Fills the page */
				max-width: 1280px;
				margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<script type="text/javascript">

			// insert a new symptom
			function frmSymptoms_Insert()
			{
				// same as frmBodyPart_Insert() in file admin_bodyparts.php
				var symptomNewName = prompt("Enter a name for the new symptom:", "");
				if (symptomNewName !== null && symptomNewName !== "")
				{
					document.getElementById("hdnAction").value = "insert";
					document.getElementById("hdnSymptomNewName").value = symptomNewName;
					var symptomNewQuestion = prompt("Enter a question for the new symptom:", "");
					if (symptomNewQuestion !== null && symptomNewQuestion !== "")
						document.getElementById("hdnSymptomNewQuestion").value = symptomNewQuestion;
					document.getElementById("frmSymptoms").submit();
				}
			}

			// edit the name of an already existing symptom
			function frmSymptoms_Edit(symptomId, symptomName, symptomQuestion)
			{
				// same pattern again
				var symptomNewName = prompt("Enter the symptom's new name", symptomName);
				if (symptomNewName !== null && symptomNewName !== "")
				{
					document.getElementById("hdnAction").value = "edit";
					document.getElementById("hdnSymptomId").value = symptomId;
					document.getElementById("hdnSymptomName").value = symptomName;
					document.getElementById("hdnSymptomNewName").value = symptomNewName;
					var symptomNewQuestion = prompt("Enter the symptom's new question:", symptomQuestion);
					if (symptomNewQuestion !== null && symptomNewQuestion !== "")
						document.getElementById("hdnSymptomNewQuestion").value = symptomNewQuestion;
					document.getElementById("frmSymptoms").submit();
				}
			}

			// delete an already existing symptom
			function frmSymptoms_Delete(symptomId, symptomName)
			{
				// same pattern again
				if (confirm("Are you sure you want the symptom '" + symptomName + "' deleted? All rules associated to this symptom will be deleted as well."))
				{
					document.getElementById("hdnAction").value = "delete";
					document.getElementById("hdnSymptomId").value = symptomId;
					document.getElementById("hdnSymptomName").value = symptomName;
					document.getElementById("frmSymptoms").submit();
				}
			}
		</script>

		<center>

			<br/>
			<table style="background-color:white; border: 20px solid white">
				<tr>
					<td><a href='index.php'>Home</a></td>
					<td align='right'></td>
					<td align='right'></td>
				</tr>
				<tr>
					<td colspan='3'><hr/></td>
				</tr>
				<tr>
					<td valign='top'>
						<center><h1>Disease diagnosis system</h1></center>
						<br/>

						<?php
						// retrieve values submitted by the previous php page
						$bodyPartId = $_POST["hdnBodyPartId"];
						$bodyPartName = $_POST["hdnBodyPartName"];

						echo "<h2>Symptoms for body part: " . $bodyPartName . "</h2>";
						echo "Hover with the mouse over each symptom for more details.";
						echo "<br/><br/><br/>";

						// if the previous php page made a change to the symptoms, now read it from the $_POST structure and do its appropriate action (insert/edit/delete)
						if (array_key_exists("hdnAction", $_POST) && $_POST["hdnAction"] != "symptoms")
						{
							// retrieve values from the previous page submission
							$symptomId = array_key_exists("hdnSymptomId", $_POST) ? $_POST["hdnSymptomId"] : "";
							$symptomName = array_key_exists("hdnSymptomName", $_POST) ? $_POST["hdnSymptomName"] : "";
							$symptomNewName = array_key_exists("hdnSymptomNewName", $_POST) ? $_POST["hdnSymptomNewName"] : "";
							$symptomNewQuestion = array_key_exists("hdnSymptomNewQuestion", $_POST) ? $_POST["hdnSymptomNewQuestion"] : "";

							// insert the new symptom to the database
							if ($_POST["hdnAction"] == "insert")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to select symptoms
										$query = sprintf("SELECT * FROM symptoms WHERE BodyPartId=%s AND Name='%s'", $bodyPartId, $symptomNewName);
										
										// run the query
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("<font color='red'>Error! Symptom '%s' already exists in the database. No new record was inserted.</font><br>", $symptomNewName);
										else
										{
											// find the maximum Id field value in the table 'symptoms'
											$query = sprintf("SELECT MAX(Id) AS Max FROM symptoms WHERE BodyPartId=%s", $bodyPartId);
											$result = mysql_query($query, $connection);
											if ($result)
											{
												$row = mysql_fetch_assoc($result);
												$maxId = $row["Max"];
											}
											else
												$maxId = 0;
											
											// now the new symptom will have Id = maxId+1
											$symptomNewId = strval(1 + intval($maxId));

											// create query to insert new symptom
											$query = sprintf("INSERT INTO symptoms (BodyPartId, Id, Name, Question) VALUES (%s, %s, '%s', '%s')", $bodyPartId, $symptomNewId, $symptomNewName, $symptomNewQuestion);

											// execute query
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>New symptom '" . $symptomNewName . "' inserted into the database</font><br>";
											else
												echo "<font color='red'>Could not insert new symptom '" . $symptomNewName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// edit an existing symptom in the database
							else if ($_POST["hdnAction"] == "edit")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// first make sure that the symptom that will be inserted doesn't already exist
										$query = sprintf("SELECT * FROM symptoms WHERE BodyPartId=%s AND Name='%s' AND Question='%s'", $bodyPartId, $symptomNewName, $symptomNewQuestion);
										$records = mysql_query($query, $connection);

										if (mysql_num_rows($records) > 0)
											echo sprintf("<font color='red'>Error! Symptom '%s' already exists in the database. '%s' was not renamed to '%s'.</font><br>", $symptomNewName, $symptomName, $symptomNewName);
										else
										{
											// create query to update the symptom name
											$query = sprintf("UPDATE symptoms SET Name='%s', Question='%s' WHERE BodyPartId=%s AND Id=%s", $symptomNewName, $symptomNewQuestion, $bodyPartId, $symptomId);

											// execute the query
											$retval = mysql_query($query, $connection);
											if ($retval)
												echo "<font color='green'>Symptom '" . $symptomName . "' renamed to '" . $symptomNewName . "'</font><br>";
											else
												echo "<font color='red'>Could not rename symptom '" . $symptomName . "': " . mysql_error() . "</font>";
										}
									}
									mysql_close($connection);
								}
							}
							// delete an existing symptom in the database
							elseif ($_POST["hdnAction"] == "delete")
							{
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create query to delete the symptom
										$query = sprintf("DELETE FROM symptoms WHERE BodyPartId=%s AND Id=%s", $bodyPartId, $symptomId);

										// execute the query
										$retval = mysql_query($query, $connection);
										if ($retval)
											echo "<font color='green'>Symptom '" . $symptomName . "' deleted from database</font><br>";
										else
											echo "<font color='red'>Could not delete symptom '" . $symptomName . "': " . mysql_error() . "</font>";
									}
									mysql_close($connection);
								}
							}
							$_POST["hdnAction"] = "";
						}
						?>

						<br/>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id = "frmSymptoms" action = "admin_symptoms.php" method = "post">
							<table border = "1">
								<th>Symptom Name</th>
								<th>Edit</th>
								<th>Delete</th>
								<?php
								// connect to MySQL
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select the database 'fuzzy'
									if (mysql_select_db("fuzzy", $connection))
									{
										// create a query to select the symptoms
										$query = sprintf("SELECT * FROM symptoms WHERE BodyPartId=%d ORDER BY Name", $bodyPartId);
										$records = mysql_query($query, $connection);
										
										// if the recordset is not empty
										if ($records && mysql_num_rows($records) > 0)
										{
											// fetch each symptom as a row
											while ($row = mysql_fetch_array($records))
											{
												// extract fields from row
												$symptomId = $row["Id"];
												$symptomName = $row["Name"];
												$symptomQuestion = $row["Question"];
												
												// print them as a HTML row
												echo "<tr>";
												echo "<td title='" . $symptomQuestion . "'>" . $symptomName . "</td>";
												echo "<td><button type='button' onclick='frmSymptoms_Edit(" . $symptomId . ", \"" . $symptomName . "\", \"" . $symptomQuestion . "\")'>Edit</button></td>";
												echo "<td><button type='button' onclick='frmSymptoms_Delete(" . $symptomId . ", \"" . $symptomName . "\")'>Delete</button></td>";
												echo "</tr>";
											}
										}
									}
									mysql_close($connection);
								}
								?>
								<tr><td colspan='3'><center><button type='button' onclick='frmSymptoms_Insert()'>Insert New Symptom</button></center></td></tr>
							</table>
							<br/><br/>
							<input type='hidden' id='hdnAction' name='hdnAction' value=''>

							<?php
							// we use these hidden fields to record values with javascript so that they will be available when the next page is loaded
							// when the next page is loaded we access them using the $_POST structure
							echo "<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value='" . $bodyPartId . "'>";
							echo "<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value='" . $bodyPartName . "'>";

							echo "<input type = 'hidden' id = 'hdnSymptomId' name = 'hdnSymptomId' value = ''>";
							echo "<input type = 'hidden' id = 'hdnSymptomName' name = 'hdnSymptomName' value = ''>";
							echo "<input type = 'hidden' id = 'hdnSymptomNewName' name = 'hdnSymptomNewName' value = ''>";
							echo "<input type = 'hidden' id = 'hdnSymptomQuestion' name = 'hdnSymptomQuestion' value = ''>";
							echo "<input type = 'hidden' id = 'hdnSymptomNewQuestion' name = 'hdnSymptomNewQuestion' value = ''>";
							?>
						</form>
					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
			</table>
		</center>
	</body>
</html>
