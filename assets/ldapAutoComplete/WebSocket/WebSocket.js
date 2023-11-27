const conn = new WebSocket('ws://localhost:8080');

conn.onmessage = function (e) {
    const data = JSON.parse(e.data);

    if (data.type === 'userList') {
        updateUsersList(data.users);
    } else if (data.type === 'userChat') {
        $('#chat').append('<p>' + data.message + '</p>');
    }
};

function sendMessage() {
    const toUser = $('#userList').val();
    const message = $('#message').val();

    const data = {
        to: toUser,
        message: message
    };

    conn.send(JSON.stringify(data));
    $('#message').val('');
}

function updateUsersList(users) {
    $('#userList').empty();

    users.forEach(function (userId) {
        $('#userList').append('<option value="' + userId + '">' + userId + '</option>');
    });
}