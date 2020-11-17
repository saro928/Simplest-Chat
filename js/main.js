$(document).ready(function () {
  // Open Create User Modal
  $("#createUserModal").modal("show");

  // Focus User Name Input on page load
  setTimeout(focusInput, 500);
  function focusInput() {
    $("#userNameInput").focus();
  }

  // Start with visible Users and Rooms for big devices
  if ($(window).width() > 768) {
    $("#users-online").show();
    $("#rooms").show();
  }

  // Toggle users online Visibility on click inside online users header
  $("#online-header").click(function () {
    $("#users-online").toggle();
  });

  // Toggle Rooms Visibility on click inside rooms header
  $("#rooms-header").click(function () {
    $("#rooms").toggle();
  });

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
  $("#emojisDropup").click(function (e) {
    if ($(window).width() > 768) {
      e.stopPropagation();
    }
  });

  // Emoji on click insert in message box
  $(".emoji").on("click", function () {
    let message = $("#message").val();
    message += $(this).html();
    $("#message").val(message);
    $("#message").focus();
  });

  // Click event on meme in meme modal
  $(".memeModal").click(function () {
    $(".selectedMemeModal").removeClass("selectedMemeModal");
    $(this).addClass("selectedMemeModal");
  });

  // Click on meme button
  $("#buttonMeme").click(function () {
    // Check if the class 'selectedMemeModal' is assigned to a meme, if so procceed to submit meme
    if ($(".selectedMemeModal")[0]) {
      $("#message").val($(".selectedMemeModal").attr("data-meme"));
      $("#messageForm").submit();
      $(".selectedMemeModal").removeClass("selectedMemeModal");
      $("#modalMemez").modal("hide");
    } else {
      alert("No meme selected!");
    }
  });

  // Click on Online User element then add or remove class Dinamically
  $(document).on("click", ".online-user", function () {
    if ($(this).hasClass("selectedOnlineUser")) {
      $(this).removeClass("selectedOnlineUser");
    } else {
      $(this).addClass("selectedOnlineUser");
    }
    // Show create-room button if at least one element has the selectedOnlineUser class
    if ($(".selectedOnlineUser")[0]) {
      $("#create-room").show();
    } else {
      $("#create-room").hide();
    }
  });

  // Click on create-room button
  $("#create-room").click(function () {
    let user = $("#hiddenUserID").val();
    let room = $("#hiddenRoomID").val();
    let users = [];
    $(".selectedOnlineUser").each(function () {
      users.push($(this).attr("data-id"));
    });
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/createRoom.php",
      data: { users: users, user_id: user, room_id: room },
      success: function (result) {
        console.log("Created Room!");
        $(".selectedOnlineUser").removeClass("selectedOnlineUser");
        $("#create-room").hide();
        checkRooms();
        changeRoom(result.room);
      },
    });
  });

  // Click on Room element
  $(document).on("click", ".room", function () {
    changeRoom($(this).attr("data-room"));
  });

  // Change Room, by clicking on a Room or by creating a new Room
  function changeRoom(room) {
    let user = $("#hiddenUserID").val();
    let current_room = $("#hiddenRoomID").val();
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/changeRoom.php",
      data: { user_id: user, current_room: current_room, target_room: room },
      success: function (result) {
        console.log("Changed Room!");
        console.log(result);
        // Set new room value in page
        $("#hiddenRoomID").val(result.room);
        // Load messages, online users, and Rooms I belong to
        loadMessages((forceScroll = true));
      },
    });
  }

  /* Pendientes */
  // Revisar que suena la alerta sola al cambiar de sala
  // Crear Login y campo de contraseña para usuarios, encriptar con PHP
  // Permitir la visualizacion de mensajes anteriores

  // On submit Create User Form
  $("#createUserForm").on("submit", function (e) {
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
    let encodedUserName = $("<div />").text(user).html();
    // Create user in DB, then put user name and id in html
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/createUser.php",
      data: { userName: encodedUserName },
      error: function (xhr, status, error) {
        console.log("Trouble Creating User!");
      },
      success: function (result) {
        console.log(result);
        $("#createUserModal").modal("hide");
        $("#hiddenUserID").val(result.id);
        $("#hiddenRoomID").val(result.room);
        loadMessages((forceScroll = true));
        setInterval(loadMessages, 5000);
        // Focus on message textarea for big devices
        if ($(window).width() > 768) {
          $("#message").focus();
        }
      },
    });
  });

  // Check for online Users
  function checkOnlineUsers() {
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/getOnlineUsers.php",
      data: {
        user_id: $("#hiddenUserID").val(),
        room_id: $("#hiddenRoomID").val(),
      },
      error: function (xhr, status, error) {
        console.log("ONLINE USERS ERROR!");
        console.log(xhr);
        console.log(status);
        console.log(error);
      },
      success: function (result) {
        console.log("ONLINE USERS");
        console.log(result);
        // Children Array of users-online DIV
        let childrenArray = $("#users-online").children();
        // If users-online DIV has no children then insert ALL fetched online users, on page load
        if (childrenArray.length == 0) {
          for (let i = 0; i < result.users.length; i++) {
            // Check if user is current user, if so apply css
            if (result.users[i].id == $("#hiddenUserID").val()) {
              $("#users-online").append(
                `<div id="online-me" data-id="${result.users[i].id}">
                                ${result.users[i].name}
                                </div>`
              );
            } else {
              $("#users-online").append(
                `<div class="online-user" data-id="${result.users[i].id}">
                                ${result.users[i].name}
                                </div>`
              );
            }
          }
        } else {
          // Get array of id's of online users to compare
          fetchedIds = Object.values(result.ids);
          // Create Array of children ids in page
          let childrenIds = [];
          // Check if every div in page is present in result(online-users) array and Delete if it is not
          for (let i = 0; i < childrenArray.length; i++) {
            let id = parseInt($(childrenArray[i]).attr("data-id"));
            childrenIds.push(id);
            if (!fetchedIds.includes(id)) {
              $(childrenArray[i]).remove();
            }
          }
          // Check every online user id in result array and Add user to the page when necesary
          for (let i = 0; i < fetchedIds.length; i++) {
            if (!childrenIds.includes(fetchedIds[i])) {
              $("#users-online").append(
                `<div class="online-user" data-id="${result.users[i].id}">
                                ${result.users[i].name}
                                </div>`
              );
            }
          }
        }
      },
    });
  }

  //Check for rooms where the current user belongs
  function checkRooms() {
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/getRooms.php",
      data: { user_id: $("#hiddenUserID").val() },
      error: function (xhr, status, error) {
        console.log("GET ROOMS ERROR!");
        console.log(xhr);
        console.log(status);
        console.log(error);
      },
      success: function (result) {
        console.log("Got rooms!");
        console.log(result);
        let string = "";
        for (let i = 0; i < result.length; i++) {
          // Check for current Room in chat
          if (result[i].room_id == $("#hiddenRoomID").val()) {
            string += `<div id="selected-room" data-room="${result[i].room_id}">`;
          } else {
            string += `<div class="room" data-room="${result[i].room_id}">`;
          }
          // Room id 1 is General chat room
          if (result[i].room_id == 1) {
            string += "General Room";
          } else {
            string += `Room ${result[i].room_id}`;
          }
          string += `</div>`;
        }
        $("#rooms").html(string);
      },
    });
  }

  // Check if Must Scroll Function (if Scrollbar is placed at the bottom or 10% above)
  function mustScroll() {
    // Get scrollable space of Chat element
    let chatScrollHeight = $("#chat").get(0).scrollHeight;
    // Get height of Chat element
    let chatHeight = parseInt($("#chat").height());
    // Sum padding top and bottom of chat element and place the result in a variable
    let chatPadding =
      parseInt($("#chat").css("padding-top")) +
      parseInt($("#chat").css("padding-bottom"));
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
    return { scrollDistance: scrollDistance, mustScroll: mustScroll };
  }

  // Load messages Counter needed for notification sound play
  var loadMessagesCounter = 0;
  // Audio to be played if required conditions are met
  var notification = new Audio("resources/deduction.mp3");
  // Timestamp variables to compare
  var lastMessageTime = "";
  // RegExp pattern to check for meme, example 'meme_1.jpg'
  var memePattern = /^meme_[0-9].jpg$/;

  // Load messages inside Chat element
  function loadMessages(forceScroll = false) {
    loadMessagesCounter++;
    $.ajax({
      dataType: "json",
      type: "POST",
      url: "/api/controllers/getMessages.php",
      data: {
        user_id: $("#hiddenUserID").val(),
        room_id: $("#hiddenRoomID").val(),
      },
      error: function (xhr, status, error) {
        console.log("No Messages found!");
        console.log(xhr);
        console.log(status);
        console.log(error);
      },
      success: function (result) {
        console.log("MESSAGES!");
        console.log(result);
        // Check if there is at least 1 message and it is formatted the way we expect
        if (
          result[0].message &&
          result[0].time &&
          result[0].created_at &&
          result[0].name
        ) {
          result.reverse();
          let msgs = "";
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
              msgs += `<img src="resources/memez/${result[i].message}" class="meme">`;
            } else {
              msgs += result[i].message;
            }
            msgs += `</div>`;
          }
          // Messages preparing done
          checkOnlineUsers();
          checkRooms();
          $("#chat").html(msgs);
          let scrollObject = mustScroll();
          // Scroll to bottom of Chat if Scrollbar is placed at the bottom or 10% above, or if forceScroll
          if (scrollObject.mustScroll == true || forceScroll == true) {
            $("#chat").animate({ scrollTop: scrollObject.scrollDistance }, 500);
          }
        } else {
          // Zero messages in room
          checkOnlineUsers();
          checkRooms();
          $("#chat").html("");
        }
      },
    });
  }

  // Message Textarea on Key Press
  $("#message").keypress(function (e) {
    // Submit if Enter Key is pressed
    if (e.which == 13 && !e.shiftKey) {
      $(this).closest("form").submit();
    }
  });

  // Message Form Submit
  $("#messageForm").on("submit", function (e) {
    e.preventDefault();
    // Check for empty or spaces only in textarea
    if (!$.trim($("#message").val())) {
      alert("Please write a message..");
      return;
    }
    let message = $("#message").val();
    // Encode the message
    let encodedMsg = $("<div />").text(message).html();
    let id = $("#hiddenUserID").val();
    let room = $("#hiddenRoomID").val();
    $.ajax({
      type: "POST",
      url: "/api/controllers/postMessage.php",
      data: { user_id: id, message: encodedMsg, room_id: room },
      success: function (result) {
        console.log(result);
        loadMessages((forceScroll = true));
        $("#message").val("");
      },
    });
  });
});
