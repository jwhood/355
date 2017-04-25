
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Symptoms</title>

		<style>
			body {
				background: gainsboro; /* Fills the page */
				max-width: 1280px;
				margin: 0 auto;
			}
		</style>
	</head>

	<body>

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
						<h2>Symptoms</h2>
						Please answer all symptoms below (hover with the mouse above each symptom for more details).
						<br/><br/><br/>

						<?php
						// retrieve values submitted by the previous php page
						$bodyPartId = $_POST["hdnBodyPartId"];
						$bodyPartName = $_POST["hdnBodyPartName"];
						?>

						<!-- all fields are in an HTML form so that their values will be submitted for processing by PHP when the next page loads -->
						<form id="frmBodyPart" action="diagnosis.php" method="post"> 
							<table border="1">
								<?php
								echo "<tr><th colspan='2'>Symptoms for: " . $bodyPartName . "</th></tr>";
								echo "<tr><th>Question</th><th>Answer</th></tr>";

								// connect to the MySQL server
								if ($connection = mysql_connect("localhost", "root", ""))
								{
									// select database 'fuzzy' as default for this connection
									if (mysql_select_db("fuzzy", $connection))
									{
										// create the query to retrieve symptoms for the given body part
										$query = sprintf("SELECT * FROM symptoms WHERE BodyPartId=%s ORDER BY Name", $bodyPartId);

										// retrieve all symptoms from the MySQL table
										$records = mysql_query($query, $connection);
										
										// if a nonempty recordset was returned
										if ($records && mysql_num_rows($records) > 0)
										{
											// for each symptom print a row in the HTML table
											while ($row = mysql_fetch_array($records))
											{
												// extract the value from the recordset
												$symptomId = $row["Id"];
												$symptomName = $row["Name"];
												$symptomQuestion = $row["Question"];
												
												if (is_null($symptomQuestion))
												{
													$symptomQuestion = $symptomName;
												}
												
												// create HTML Form elements
												echo "<tr><td title='" . $symptomQuestion . "'>" . $symptomName . "</td>";
												echo "<td><select name='symptom" . $symptomId . "'>";
												echo "<option value='1' selected='selected'>Very Low</option>";
												echo "<option value='2'>Low</option>";
												echo "<option value='3'>Moderate</option>";
												echo "<option value='4'>High</option>";
												echo "<option value='5'>Very High</option>";
												echo "</select></td></tr>\n";
											}
										}
									}
									// close the MySQL connection
									mysql_close($connection);
								}
								?>
								<tr><td colspan='2'><center><input type="submit" value="Diagnose" /></center></td></tr>
							</table>
							<br/><br/>
							<?php
							// we use these hidden fields to record values with javascript so that they will be available when the next page is loaded
							// when the next page is loaded we access them using the $_POST structure
							echo "<input type='hidden' id='hdnBodyPartId' name='hdnBodyPartId' value='" . $bodyPartId . "'>";
							echo "<input type='hidden' id='hdnBodyPartName' name='hdnBodyPartName' value='" . $bodyPartName . "'>";
							?>
						</form>
				
					
					</td>
					<td colspan='2'><img src='MusculoSkeletal.gif'/></td>
				</tr>
				
			</table>
		
		</center>
	</body>
</html>
