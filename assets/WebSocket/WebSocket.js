/**
 * http://socketo.me/
 * 
 * Intento de chat usando websocket
 * 
 * No he trabajado con uno,
 * pero sé de su existencia,
 * entonces parte del código está en la documentación que leí y el resto del código,
 * bueno con conocimientos medios de JavaScript y conocimientos medio altos con jQuery hice el resto del código
 */


// ------------------------------------------------------------------------------------------------------------
// Conexión y eventos del WebSocket
// ------------------------------------------------------------------------------------------------------------
var config = CONFIG()
var conn = new WebSocket(`ws://${config.WEBSOCKET.HOST}:${config.WEBSOCKET.PORT}`)

conn.onopen = async (e) => {
    await showChat()

    const userInfo = {}
    userInfo.id = JSON.parse(sessionStorage.getItem("userInfo"))["id"]
    userInfo.type = "userInfo"
    conn.send(JSON.stringify(userInfo))
}, conn.onerror = async (e) => {
    console.error("Error al conectar con el WebSocket:", e)

    enableWebSocket = await swalFire({
        title: "¿El WebSocket se encuentra inactivo, quieres habilitarlo?",
        showDenyButton: true,
        denyButtonText: "Cancelar",
        showCancelButton: false,
        preConfirm: (confirm) => {
            return confirm
        }
    }, ["input"])

    if (enableWebSocket) {
        const request = await fetch(`${config.BASE_SERVER}/bin/start-server.php`)
        if (request.status === 200) window.location.reload()
    }
}, conn.onmessage = async (e) => {
    const data = JSON.parse(e.data)

    if (data.type === "userChat") receiveMessage(data)
    else if (data.type === "userList") showList(data.users)
}

// ------------------------------------------------------------------------------------------------------------
// Eventos
// ------------------------------------------------------------------------------------------------------------
$(`body`).on(`submit`, `[data-id="myFirstChat"] form`, function (e) {
    e.preventDefault()
    const formData = new FormData(this)

    sendMessage(formData)
})

$(`body`).on(`click`, `li[data-system][data-socket] a`, function (e) {
    e.preventDefault()

    const $a = $(this)
    const $li = $a.parent()

    const userInfo = JSON.parse(sessionStorage.getItem("userInfo"))

    const $chat = $(`[data-id="myFirstChat"]`)

    const $to = $chat.find(`form [name="to"]`)
    const $message = $chat.find(`form [name="data[message]"]`)
    const $sender_id = $chat.find(`form [name="data[sender_id]"]`)
    const $receiver_id = $chat.find(`form [name="data[receiver_id]"]`)
    const $btnSubmit = $chat.find(`form button[type="submit"]`)

    const $btnCollapse = $chat.find(`button[data-card-widget="collapse"]`)
    const $btnToggle = $chat.find(`button[data-widget="chat-pane-toggle"]`)
    const $btnRemove = $chat.find(`button[data-card-widget="remove"]`)

    $to.val($li.data(`socket`))
    $sender_id.val(userInfo.id)
    $receiver_id.val($li.data(`system`))

    $message.removeAttr(`disabled`)
    $btnSubmit.removeAttr(`disabled`)

    const sender_id = $sender_id.val();
    const receiver_id = $receiver_id.val();

    $btnToggle.click()

    showMessage(sender_id, receiver_id)
})

// ------------------------------------------------------------------------------------------------------------
// Funciones
// ------------------------------------------------------------------------------------------------------------
const sendMessage = (data) => {
    if (typeof data === "object" && data instanceof FormData) {
        const $chat = $(`[data-id="myFirstChat"]`)
        const $message = $chat.find(`form [name="data[message]"]`)
        const $direct_chat_messages = $chat.find(`.direct-chat-messages`)

        const to = data.get(`to`)
        const sender_id = data.get(`data[sender_id]`)
        const receiver_id = data.get(`data[receiver_id]`)
        const message = data.get(`data[message]`)

        const userInfo = JSON.parse(sessionStorage.getItem("userInfo"))

        const sendM = {
            type: "userChat",
            to: to,
            fromUser: sender_id,
            toUser: receiver_id,
            userInfo: userInfo,
            message: message
        }

        $.ajax(`${config.BASE_SERVER}/assets/WebSocket/WebSocket.php?action=sendMessage`, {
            type: "POST",
            dataType: "JSON",
            data: data,
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                if (response.status === true) try {

                    $message.val(``)
                    $direct_chat_messages.append(`
                    <div class="direct-chat-msg left">
                        <div class="direct-chat-infos clearfix">
                            <span class="direct-chat-name float-left">${userInfo.name}</span>
                            <span class="direct-chat-timestamp float-right">Now</span>
                        </div>
                        <img class="direct-chat-img" src="${userInfo.icon}" alt="message user image" style="height: 40px">
                        <div class="direct-chat-text">
                            ${message}
                        </div>
                    </div>
                    `);

                    conn.send(JSON.stringify(sendM))
                } catch (error) {
                    console.error("Error al enviar el mensaje:", error)
                }
            }
        })
    }
}, receiveMessage = (data) => {
    const $chat = $(`[data-id="myFirstChat"]`)
    const $direct_chat_messages = $chat.find(`.direct-chat-messages`)

    const $sender_id = $chat.find(`form [name="data[sender_id]"]`)
    const sender_id = $sender_id.val()
    const $receiver_id = $chat.find(`form [name="data[receiver_id]"]`)
    const receiver_id = $receiver_id.val()

    if (sender_id == data.toUser && receiver_id == data.fromUser) $direct_chat_messages.append(`
    <div class="direct-chat-msg right">
        <div class="direct-chat-infos clearfix">
            <span class="direct-chat-name float-right">${data.userInfo.name}</span>
            <span class="direct-chat-timestamp float-left">Now</span>
        </div>
        <img class="direct-chat-img" src="${data.userInfo.icon}" alt="message user image" style="height: 40px">
        <div class="direct-chat-text">
            ${data.message}
        </div>
    </div>
    `);

}, showChat = async () => {
    const $chat = $(`[data-id="myFirstChat"]`)

    if ($chat.length == 0) $.ajax(`${config.BASE_SERVER}/assets/WebSocket/WebSocket.php?action=showChat`, {
        dataType: "HTML",
        async: false,
        success: (response) => {
            const $chatContent = $(response)
            $(`[data-router]`).parent().append($chatContent)
        }
    })
    else $chat.show("slow")
}, showList = (users) => {
    if (typeof users === "object") {
        const $chat = $(`[data-id="myFirstChat"]`)

        $.ajax(`${config.BASE_SERVER}/assets/WebSocket/WebSocket.php?action=showList`, {
            type: "POST",
            data: {
                users: users
            },
            dataType: "HTML",
            success: (response) => {
                const $list = $(response)
                $chat.find(`.direct-chat-contacts`).html($list)
            }
        })
    }
}, showMessage = (from, to) => {
    if (from && to) {
        const $chat = $(`[data-id="myFirstChat"]`)
        const $direct_chat_messages = $chat.find(`.direct-chat-messages`)

        $.ajax(`${config.BASE_SERVER}/assets/WebSocket/WebSocket.php?action=showMessage`, {
            type: "POST",
            data: {
                from: from,
                to: to
            },
            dataType: "HTML",
            success: (response) => {
                if (response !== "") {
                    const $message = $(response)
                    $direct_chat_messages.html($message)
                } else $direct_chat_messages.html(``)
            }
        })
    }
}