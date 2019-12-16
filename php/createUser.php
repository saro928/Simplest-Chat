<?php

if (isset($_POST['userName'])) {
    session_start();

    require_once 'DB.php';
    
    // Clean html tags in user name input
    $user = htmlentities($_POST['userName']);
    
    // Prepare and bind
    $statement = $conn->prepare("INSERT INTO users (name, status) VALUES (?, 'online')");
    $statement->bind_param("s", $user);    

    if ($statement->execute()) {    
        $id_query = "SELECT LAST_INSERT_ID();";
        $result = $conn->query($id_query);
        if ($row = $result->fetch_array()) {
            $data['id'] = $row['LAST_INSERT_ID()'];        
        }
        // Create ID session variable for validation in all php files
        $_SESSION['ID'] = $data['id'];
        $data['user'] = $user;
        // Send back user name and user id in Array
        echo json_encode($data);
    } else {
        echo "Error: " . $statement->error;
    }
    
    $statement->close();
    $conn->close();
}

?>