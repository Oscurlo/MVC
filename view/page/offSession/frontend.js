// 2023-10-22 23:49:36

$(document).ready(async () => {
    const BASE_SERVER = CONFIG("BASE_SERVER")

    bsCustomFileInput.init();

    const $form = $(`#login, #register`)
    $form.on(`submit`, function (e) {
        const $this = $(this)
        const action = $this.attr(`id`)

        e.preventDefault()
        $.ajax(`view/page/offSession/backend.php?action=${action}`, {
            type: "POST",
            dataType: "JSON",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                const actionU = action.toUpperCase()

                if (actionU.toUpperCase() == "LOGIN" && response.status === true) window.location.href = BASE_SERVER
                else if (actionU.toUpperCase() == "REGISTER" && response.status === true) alerts({ title: "Registro exitoso", icon: "success" })
                else alerts({ title: "Ha ocurrido algo inesperado :c", icon: "info" })
            }
        })
    })

    const $checkPass = $(`#checkPass`)
    $checkPass.on(`input`, function () {
        const $pass1 = $(`[name="data[password]"]`)
        const $pass2 = $(this)
        const val1 = $pass1.val().toUpperCase()
        const val2 = $pass2.val().toUpperCase()

        if (!val1.startsWith(val2)) $pass2.addClass(`is-invalid`)
        else $pass2.removeClass(`is-invalid`)
    })
})