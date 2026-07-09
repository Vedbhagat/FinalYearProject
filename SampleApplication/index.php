<?php
$server= "Localhost";
$username = "root";
$password = "";
$dbname = "samplee";

error_reporting(0); 


$conn = new mysqli($server, $username, $password, $dbname);

if($conn->connect_error){   
    die("connection failed" .$conn->connect_error );
}

if($_SERVER["REQUEST_METHOD"]== "POST"){
    {
        $prodId = $_POST['prodId'];
        $prodName = $_POST['prodName'];
        $prodPrice = $_POST['prodPrice'];
    }
        
    
    $conn->query("
    CREATE TABLE IF NOT EXISTS products(
        prodId int primary key,
        prodName Varchar(10),
        prodPrice int);
    ");

    if(isset($_POST['add'])){
        $sql = "SELECT * FROM products WHERE prodId = '$prodId'";
        $result = $conn->query($sql);
        if($result -> num_rows > 0){
            // echo "Updating";
            $sql = "UPDATE products SET prodName = '$prodName', prodPrice = '$prodPrice' WHERE prodID = '$prodId'";
            $result = $conn->query($sql);
            header("Location: index.html");
            exit;
        }else{
            $sql = "INSERT INTO products VALUES ('$prodId', '$prodName', '$prodPrice')";
            if($conn->query($sql)===true){
                echo "Product Added";
                header("Location: index.html");
                exit;
            }
            else{
                echo "Process Failed". $sql . "<br>" . $conn->error;
                header("Location: index.html");
                exit;
            }
        };
    }
    elseif(isset($_POST['getOne'])){
        $sql = "SELECT * FROM products WHERE prodId = '$prodId'";
        $result = $conn->query($sql);
        if($result-> num_rows >0){
            while($row = $result->fetch_assoc()){
                echo  "<br>" . "   id: ".$row["prodId"]. "   Name: ".$row["prodName"]. "   Price: ".$row["prodPrice"]. "<br>";
            }
        }
        else{
            echo "NO DATA EXIST";
        }
    }
    elseif(isset($_POST['Delete'])){
        $sql = "DELETE FROM products WHERE prodId = '$prodId'";
        $conn->query($sql);
        header("Location: index.html");
        exit;        
    }
    elseif(isset($_POST['getAll'])){
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        if($result-> num_rows >0){
            while($row = $result->fetch_assoc()){
                echo "   id: ".$row["prodId"]. "   Name: ".$row["prodName"]. "  Price: ".$row["prodPrice"]. "<br>";

            }
        }
        else{
            echo "NO DATA EXIST";
        }
    }

}
