<?php

class Database{
    public $conn;  
    function __construct(){
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cms";

    $this->conn = new mysqli($servername, $username, $password, $dbname);

    if($this->conn->connect_error){
        die("Failed: " . $this->conn->connect_error);
    }   
    }
}
?>