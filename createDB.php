<html>
    <head>
        <title>Create Database</title>
        <link rel="stylesheet" type="text/css" href="main.css"/>
    </head>
    <body>
    <ul>
        <li><a href="index.php">Return to index page</a></li>
    </ul>
    <br>
    <br>
    <?php
                $server = 'sql.rde.hull.ac.uk';
                $connectionInfo = array( "Database"=>"rde_636948");//your ID
                $link = sqlsrv_connect($server,$connectionInfo);
                if( ($errors = sqlsrv_errors() ) != null) 
                {
                    foreach( $errors as $error ) 
                    {
                        echo "SQLSTATE:".$error[ 'SQLSTATE']."<br />";
                        echo "code: ".$error[ 'code']."<br />";
                        echo "message: ".$error[ 'message']."<br />";
                    }
                }
                $null_params=array();//ensures function can still be used when no parameters are required
                //drop studentAssociations
                $query='drop table studentAssociations';
                $resultpositive='studentAssociations successfully dropped';
                echo doQuerey($query,$resultpositive,$link,$null_params);

                //drop locations
                $query='drop table Locations';
                $resultpositive='Locations successfully dropped';
                echo doQuerey($query,$resultpositive,$link,$null_params);

                //drop students
                $query='drop table Students';
                $resultpositive='Students successfully dropped';
                echo doQuerey($query,$resultpositive,$link,$null_params);

                //create students
                $query='create table Students';
                $query .= ' (id int PRIMARY KEY IDENTITY(1,1), FirstName varchar(100) NOT NULL, LastName varchar(100) NOT NULL)';
                $resultpositive='Students successfully created';
                echo doQuerey($query,$resultpositive,$link,$null_params);

                //create locations
                $query='create table Locations';
                $query.=' (id int PRIMARY KEY IDENTITY(1,1), LocationName varchar(100) NOT NULL)';
                $resultpositive='Locations successfully created';
                echo doQuerey($query,$resultpositive,$link,$null_params);

                //create locations
                $query='create table studentAssociations';
                $query.=' (id int PRIMARY KEY IDENTITY(1,1),TimeOfVisit DATETIME NOT NULL, StudentID int NOT NULL,LocationID int NOT NULL, FOREIGN KEY (StudentID) REFERENCES Students(id), FOREIGN KEY (LocationID) REFERENCES Locations(id))';
                $resultpositive='studentAssociations successfully created';
                echo doQuerey($query,$resultpositive,$link,$null_params);
                
                //add example Students
                $insert_query="INSERT INTO Students (FirstName, LastName) VALUES (?,?)";
                $params_set=array(array("Daniel","Bulman"), array("Joe","Jokes"), array("Penny","Wise"), array("Lara","Croft"),array("Donkey","Kong"),array("Jeremy","Jan"),array("Con","Ron"),array("Gary","Powers"),array("Austin","Powers"),array("Joeseph","Joestar"),array("Johnathan","Joestar"),array("Dr","Evil"),array("Lucy","Lankaster"),array("Mary","Moonshine"),array("Mark","Rustov"));
                foreach($params_set as $params)
                {
                    $resultpositive="Student ".$params[0]." ".$params[1]." added";
                    echo doQuerey($insert_query,$resultpositive,$link,$params);
                }

                //add example Locations
                $insert_query="INSERT INTO Locations (LocationName) VALUES (?)";
                $params_set=array(array("Hull University"), array("Old Grey Mare"), array("Evil Incorperated"), array("Sanctuary"),array("Asylum"));
                foreach($params_set as $params)
                {
                    $resultpositive="Location ".$params[0]." added";
                    echo doQuerey($insert_query,$resultpositive,$link,$params);
                }
                date_default_timezone_set('GMT');
                $today=date('Y-m-d H:i:s');
                //add example Student Place Associations
                $insert_query="INSERT INTO studentAssociations (StudentID,LocationID,TimeOfVisit) VALUES (?,?,?)";
                $params_set=array(array(1,2,"2021-12-01 09:00:00"),array(1,1,"2021-12-05 10:00:00"),array(2,1,"2021-12-08 08:00:00"),array(3,3,"2021-12-07 07:30"),array(4,4,"2021-12-06 12:00:00"),array(5,1,"2021-12-13 13:00:00"),array(5,5,"2021-12-14 15:00:00"),array(6,3,"2021-12-10 16:40:00"),array(6,2,"2021-12-06 17:45:00"),array(6,1,"2021-12-02 09:40:00"),array(7,1,"2021-12-03 11:45:00"),array(7,1,"2021-12-04 10:15:00"),array(8,5,"2021-12-08 08:30:00"),array(9,4,"2021-12-09 16:15:00"),array(10,4,"2021-12-15 12:00:00"),array(11,1,"2021-12-13 13:30:00"),array(12,2,"2021-12-16 09:45:00"),array(12,2,"2021-12-16 11:00:00"),array(13,4,"2021-12-16 14:20:00"),array(14,5,"2021-12-04 15:30:00"),array(14,1,"2021-12-08 16:30:00"),array(15,1,"2021-12-10 18:30:00"),array(15,2,"2021-12-10 10:00:00"),array(15,5,"2021-12-12 11:20:00"),array(1,2,$today));
                foreach($params_set as $params)
                {
                    $resultpositive="Student ID ".$params[0]." was at location ID ".$params[1]." on the ".$params[2];
                    echo doQuerey($insert_query,$resultpositive,$link,$params);
                }

                //close queries
                sqlsrv_free_stmt($query);
                sqlsrv_close($link);

                function doQuerey($createQuery, $QuereySuccessMessage,$linkcontext,$insertparams)//function takes in query and context and attempts to run it on the database
                {
                    $result=sqlsrv_query($linkcontext,$createQuery,$insertparams);
                    if (!$result)
                    {
                        if( ($errors = sqlsrv_errors() ) != null) 
                        {
                            $errorall="";
                            foreach( $errors as $error) 
                            {
                                $errorall.= "SQLSTATE: ".$error[ 'SQLSTATE']."<br />"."code: ".$error[ 'code']."<br /> "."message: ".$error[ 'message']."<br />";
                            }
                            return $errorall;
                        }
                    } 
                    else
                    {
                        return "$QuereySuccessMessage <br/>";
                    }
                }
    ?>
    </body>
</html>