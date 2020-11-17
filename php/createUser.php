<?php

// Proceed if required variables are set
if (isset($_POST['userName'])) {
    session_start();

    require_once 'DB.php';
    
    // Clean html tags in user name input
    $user = htmlentities($_POST['userName']);
    
    // Prepare and bind
    $statement = $conn->prepare("INSERT INTO users (name, status) VALUES (?, 'online')");
    $statement->bind_param("s", $user);    

    if ($statement->execute()) {    
        // When a new user is created a Trigger in the DB inserts their user_id inside chat_rooms_has_users table in room # 1, General Room, so we start the chat inside that room
        $_SESSION['room'] = 1;
        $data['room'] = $_SESSION['room'];
        $id_query = "SELECT LAST_INSERT_ID()";
        $result = $conn->query($id_query);
        if ($row = $result->fetch_array()) {
            $data['id'] = $row['LAST_INSERT_ID()'];        
        }
        // Create ID session variable for validation in all php files
        $_SESSION['ID'] = $data['id'];        
        // Send back user id and room id in Array
        $statement->close();
        echo json_encode($data);
    } else {
        echo "Error: " . $statement->error;
    }    
    
    $conn->close();
}

?>