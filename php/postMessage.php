<?php

if (isset($_POST['user_id']) && isset($_POST['message']) && !ctype_space($_POST['message'])) {
    session_start();

    require_once 'DB.php';
    
    $id = $_POST['user_id'];
    // Clean html tags in message
    $message = htmlentities($_POST['message']);
    
    // Verify match between incoming ID and session ID
    if ($_SESSION['ID'] == $id) {
        // Prepare and bind
        $statement = $conn->prepare("INSERT INTO messages (message, user_id) VALUES (?, ?)");
        $statement->bind_param("si", $message, $id);
    
        if ($statement->execute()) {        
            echo "Message Sent Successfully!";
        } else {
            echo "Error: " . $statement->error;
        }
        $statement->close();
    } /*else {
        echo "USER ID MISMATCH sending message!";
    }*/
    
    
    $conn->close();
}

?>