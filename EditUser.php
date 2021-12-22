<html>
    <head>
        <title>Edit a user's details</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
        <ul>
            <li><a href="index.php">Return to index page</a></li>
        </ul>
        <br>
        <br>
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>">
        Old First Name: <input type="text" name="postoldfirstname">
        Old Last Name: <input type="text" name="postoldlastname">
        <br/>
        <br/>
        New First Name: <input type="text" name="postnewfirstname">
        New Last name: <input type="text" name="postnewlastname">
        <br/>
        <br/>
        <input onclick="POST" type="submit" value="Change User's Name">
        </form>
        <?php
            if ($_SERVER["REQUEST_METHOD"] = "POST")
            {
                $oldfirstname = $_POST['postoldfirstname'];
                $oldlastname = $_POST['postoldlastname'];
                $newfirstname = $_POST['postnewfirstname'];
                $newlastname = $_POST['postnewlastname'];
                if(!(empty($oldfirstname) || empty($oldlastname) || empty($newfirstname) || empty($newlastname)))//if all fields are filled
                {
                    $server = 'sql.rde.hull.ac.uk';//connect to database
                    $connectionInfo = array( "Database"=>"rde_636948");//your ID
                    $link = sqlsrv_connect($server,$connectionInfo);

                    $query="Select FirstName,LastName from Students where FirstName='$oldfirstname' and LastName='$oldlastname'";//gets the old student name if they exist
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $oldfirstnamecheck=$row["FirstName"];
                    $oldlastnamecheck=$row["LastName"];

                    $query="Select FirstName,LastName from Students where FirstName='$newfirstname' and LastName='$newlastname'";//gets the new student name if they exist
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $newfirstnamecheck=$row["FirstName"];
                    $newlastnamecheck=$row["LastName"];
                    if(empty($oldfirstnamecheck) && empty($oldlastnamecheck))//if the old name does not exist do not update do not update the database
                    {
                        echo "That student does not exist in the database!";
                    }
                    elseif(!(empty($newfirstnamecheck) && empty($newlastnamecheck)))//if the new name already exists do not update the database
                    {
                        echo "The new name is already taken!";
                    }
                    else//otherwise run the update query
                    {
                        $query="update Students set Firstname=?, LastName=? where FirstName=? and LastName=?";
                        $params=array($newfirstname,$newlastname,$oldfirstname,$oldlastname);
                        $result=sqlsrv_query($link,$query,$params);
                        if($result)
                        {
                            echo $oldfirstname." ".$oldlastname." is now called ".$newfirstname." ".$newlastname;
                        }
                    }
                    sqlsrv_free_stmt($query);
                    sqlsrv_close($link);
                }
                elseif(empty($oldfirstname) && empty($oldlastname) && empty($newfirstname) && empty($newlastname))
                {
                    //do nothing as the data probably hasn't been entered yet
                }
                else//if only some fields are filled
                {
                    echo "All fields must be filled for an output to occur";
                }
            }
        ?>
    </body>
</html>