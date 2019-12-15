$(document).ready(function() {    
    // Open Create User Modal
    $("#createUserModal").modal("show");    

    // Focus User Name Input on page load
    setTimeout(focusInput, 500);
    function focusInput() {
        $("#userNameInput").focus();
    }

    // Start with visible users for big devices
    if ($(window).width() > 768) {
        $("#users-online").show();
    }

    // Toggle users online Div on click inside online users header
    $("#online-header").click(function() {
        $("#users-online").toggle();
    })

    loadEmojis();

    // Load emojis in dropup
    function loadEmojis() {  
        let emojis = "";        
        for (let i = 128512; i <= 128591; i++) {
            emojis += `<div class="emoji">&#${i};</div>`;            
        }        
        $("#emojisDropup").html(emojis);
    }

    // Prevent Emoji dropup from closing by clicking inside if window width > 768
    $("#emojisDropup").click(function(e){
        if ($(window).width() > 768) {
            e.stopPropagation();
        }        
    })

    // Emoji on click insert in message box     
    $(".emoji").on("click", function() {        
        let message = $("#message").val();
        message += $(this).html();
        $("#message").val(message);
        $("#message").focus();
    })   
    
    // Click event on meme in meme modal
    $(".memeModal").click(function() {
        $(".selectedMemeModal").removeClass("selectedMemeModal");        
        $(this).addClass("selectedMemeModal");        
    })

    // Click on meme button
    $("#buttonMeme").click(function() {
        // Check if the class 'selectedMemeModal' is assigned to a meme, if so procceed to submit meme
        if ($(".selectedMemeModal")[0]) {
            $("#message").val($(".selectedMemeModal").attr("data-meme"));
            $("#messageForm").submit();
            $(".selectedMemeModal").removeClass("selectedMemeModal");
            $("#modalMemez").modal("hide");
        } else {
            alert("No meme selected!");
        }        
    })
    
    /* Pendientes */    
    // Crear Login y campo de contraseña para usuarios, encriptar con PHP    
    // Permitir la visualizacion de mensajes anteriores     

    // On submit Create User Form
    $("#createUserForm").on("submit", function(e) {        
        e.preventDefault();
        // Check for empty or spaces only in user name input     
        if (!$.trim($("#userNameInput").val())) {
            alert("User name cannot be an empty string!");            
            return;
        }
        let user = $("#userNameInput").val();        
        // Check for max string count in User Name
        if (user.length > 36) {
            alert("You surpassed the maximum user name allowed length!");
            return;
        }
        // Encode User Name
        let encodedUserName = $('<div />').text(user).html();
        // Create user in DB, then put user name and id in html
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: 'php/createUser.php',
            data: {userName: encodedUserName},
            error: function(xhr, status, error) {                
                console.log("Trouble Creating User!");                
            },
            success: function(result) {
                console.log(result);                
                $("#createUserModal").modal("hide");
                $("#user").html(result.user);
                $("#hiddenUserID").val(result.id);
                loadMessages(forceScroll = true);                
                setInterval(loadMessages, 5000);
                // Focus on message textarea for big devices
                if ($(window).width() > 768) {
                    $("#message").focus();
                }               
            }
        })
    })
    
    // Check for online Users 
    function checkOnlineUsers() {
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: 'php/getOnlineUsers.php',
            data: {user_id: $("#hiddenUserID").val()},
            success: function(result) {
                console.log(result);
                // Check for online users
                let string = "";
                for (let i = 0; i < result.length; i++) {                    
                    string += `<div class="online-user">
                        ${result[i].name}
                    </div>`;
                }
                $("#users-online").html(string);
            }
        })
    }    

    // Check if Must Scroll Function (if Scrollbar is placed at the bottom or 10% above)
    function mustScroll() {        
        // Get scrollable space of Chat element
        let chatScrollHeight = $("#chat").get(0).scrollHeight;
        // Get height of Chat element
        let chatHeight = parseInt($("#chat").height());
        // Sum padding top and bottom of chat element and place the result in a variable
        let chatPadding = parseInt($("#chat").css('padding-top')) + parseInt($("#chat").css('padding-bottom'));        
        // Get position of Scrollbar
        let scrollPosition = $("#chat").scrollTop();
        // Calculate distance to scroll
        let scrollDistance = chatScrollHeight - chatHeight - chatPadding;
        // Define if Chat must scroll
        let mustScroll = false;
        // Must scroll if scrollbar is at the bottom or 10% above the bottom
        if (scrollPosition > scrollDistance * 0.9) {
            mustScroll = true;            
        }
        return {scrollDistance: scrollDistance, mustScroll: mustScroll};
    }

    // Load messages Counter needed for notification sound play
    var loadMessagesCounter = 0;
    // Audio to be played if required conditions are met
    var notification = new Audio('resources/deduction.mp3');
    // Timestamp variables to compare
    var lastMessageTime = "";     
    // RegExp pattern to check for meme, example 'meme_1.jpg'
    var memePattern = /^meme_[0-9].jpg$/;

    // Load messages inside Chat element
    function loadMessages(forceScroll = false) {        
        loadMessagesCounter++;        
        $.ajax({
            dataType: 'json',
            type: 'POST',
            url: 'php/getMessages.php',
            data: {user_id: $("#hiddenUserID").val()}, 
            error: function(xhr, status, error) {
                console.log("No Messages found!");
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            success: function(result) { 
                console.log("Success!");             
                console.log(result);
		        result = result.reverse();
                let msgs = '';
                for (let i = 0; i < result.length; i++) {                    
                    // Check for current user id in message, if so, align element to the right
                    if (result[i].user_id == $("#hiddenUserID").val()) {
                        msgs += `<div class="message current-user">`;                        
                    } else {
                        // This will store the Timestamp of last message in first messages load
                        if (loadMessagesCounter == 1) {
                            lastMessageTime = result[i].created_at;
                        }
                        // Check if this is the last message and it's not the first messages load
                        if (i == result.length - 1 && loadMessagesCounter > 1) {                            
                            // Play sound if Timestamp of current last message is different than the last saved Timestamp in JS
                            if (lastMessageTime != result[i].created_at) {                                
                                notification.play();
                            }
                            lastMessageTime = result[i].created_at;
                        }
                        msgs += `<div class="message">`;                                              
                    }                   
                    msgs += `<span class="msg-header"><b>${result[i].name}</b> at ${result[i].time}</span><br>`;
                    // Check if message is a Meme
                    if (memePattern.test(result[i].message)) {
                        msgs += `<img src="resources/memez/${result[i].message}" class="meme">`
                    } else {
                        msgs += result[i].message;
                    }                    
                    msgs += `</div>`;                    
                }
                // Messages preparing done
                checkOnlineUsers();
                $("#chat").html(msgs);                 
                let scrollObject = mustScroll(); 
                // Scroll to bottom of Chat if Scrollbar is placed at the bottom or 10% above, or if forceScroll
                if (scrollObject.mustScroll == true || forceScroll == true) {
                    $("#chat").animate({ scrollTop: scrollObject.scrollDistance}, 500);                 
                }                
            }
        })
    }

    // Message Textarea on Key Press
    $("#message").keypress(function(e) {
        // Submit if Enter Key is pressed
        if (e.which == 13 && !e.shiftKey) {
            $(this).closest("form").submit();
        }   
    })   
    
    // Message Form Submit
    $("#messageForm").on("submit", function(e) {        
        e.preventDefault();   
        // Check for empty or spaces only in textarea     
        if (!$.trim($("#message").val())) {
            alert("Please write a message..");            
            return;
        }
        let message = $("#message").val();
        // Encode the message
        let encodedMsg = $('<div />').text(message).html();
        let id = $("#hiddenUserID").val();        
        $.ajax({            
            type: 'POST',
            url: 'php/postMessage.php',
            data: {user_id: id, message: encodedMsg},
            success: function(result) {
                console.log(result);     
                loadMessages(forceScroll = true);                
                $("#message").val("");
            }
        })
    })
})

