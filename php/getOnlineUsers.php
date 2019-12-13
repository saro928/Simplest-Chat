<?php

if (isset($_POST['user_id'])) {
    session_start();

    require_once 'DB.php';
    
    // Verify match between incoming ID and session ID
    if ($_POST['user_id'] == $_SESSION['ID']) {
        // Get online users query
        $sql = "SELECT id, name FROM users WHERE status = 'online' ORDER BY name";
        $result = $conn->query($sql);
        // Store online users in Assoc Array    
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }    
        echo json_encode($users);
    } /*else {
        echo json_encode("USER ID MISMATCH getting online users!");
    }*/    
    
    $conn->close();
}

?>