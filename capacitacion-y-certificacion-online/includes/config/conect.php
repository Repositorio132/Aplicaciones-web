<?php 
    function connectDB():mysqli{
     $db = mysqli_connect("localhost", "root", "", "CursosDBN");
    

        if($db){
            echo"Connected";
        }else{
            echo "NOT Connected";
        }
        return $db;
    }
