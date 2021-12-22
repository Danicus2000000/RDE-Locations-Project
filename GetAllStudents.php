<html>
    <head>
        <title>List of all students most recent locations</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
        <ul>
            <li><a href="index.php">Return to index page</a></li>
        </ul>
        <br>
        <br>
        <h2 id="HeadingUnderline"><u>Student's Most Recent Locations</u></h1>
        <?php
            $server = 'sql.rde.hull.ac.uk';//connect to database
            $connectionInfo = array( "Database"=>"rde_636948");//your ID
            $link = sqlsrv_connect($server,$connectionInfo);

            //get max student ID in the database
            $query="Select max(id) as maxID from Students";
            $result=sqlsrv_query($link,$query);
            $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            $maxStudentID=$row["maxID"];
            if(empty($maxStudentID))
            {
                echo "There are no students in the database!";
            }
            else
            {
                $studentDates=array();
                for($i=1; $i<=$maxStudentID;$i++)
                {
                    $query="Select max(TimeOfVisit) as maxDate from StudentAssociations where StudentID='$i'";//get the visit time from the student id
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $newestDate=$row["maxDate"];
                    $trueDate=date_format($newestDate,"Y-m-d H:i:s");

                    $query="Select LocationID from StudentAssociations where StudentID='$i' and TimeOfVisit='$trueDate'";//get the location id of where the student went
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $locationId=$row["LocationID"];

                    $query="Select LocationName from Locations where id='$locationId'";//use the location id to get the location name
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $location=$row["LocationName"];

                    $query="Select FirstName,LastName from Students where id='$i'";//get the name of the student from their id
                    $result=sqlsrv_query($link,$query);
                    $row=sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
                    $firstname=$row["FirstName"];
                    $lastname=$row["LastName"];

                    $data=array($firstname,$lastname,$location,$trueDate);//stores this as an array to be outputted after all students have been checked
                    $studentDates[]=$data;
                }
                foreach($studentDates as $eachStudent)//loops through all students outputting most recent location
                {
                    if(empty($eachStudent[2]))//if the location is empty
                    {
                        echo $eachStudent[0]." ".$eachStudent[1]." has not been to any locations yet!";
                    }
                    else
                    {
                        echo $eachStudent[0]." ".$eachStudent[1]." was most recently at ".$eachStudent[2]." at time ".$eachStudent[3]."<br/>";
                    }
                }
            }
            sqlsrv_free_stmt($query);
            sqlsrv_close($link);
        ?>
    </body>
</html>