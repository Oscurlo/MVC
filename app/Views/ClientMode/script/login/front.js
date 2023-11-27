$(document).ready(async () => {
    var Config = CONFIG()

    const $form = $(`#login`)
    $form.on(`submit`, function (e) {
        e.preventDefault()

        const $this = $(this)
        const action = $this.attr(`id`)

        $.ajax(`${Config.BASE_SERVER}/app/Views/ClientMode/script/login/back.php?action=${action}`, {
            type: "POST",
            dataType: "JSON",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                if (response.status === true) window.location.href = Config.BASE_SERVER
                else alerts({ title: "Usuario o contraseña incorrectos ", icon: "info" })
            }
        })

    })
})