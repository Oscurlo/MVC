
$(document).ready(async () => {
    var Config = CONFIG()

    const $form = $(`#register`)
    $form.on(`submit`, function (e) {
        e.preventDefault()

        const $this = $(this)
        const action = $this.attr(`id`)

        $.ajax(`app/Views/ClientMode/script/register/back.php?action=${action}`, {
            type: "POST",
            dataType: "JSON",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                const actionU = action.toUpperCase()

                if (response.status === true) alerts({ title: "Registro exitoso", icon: "success" })
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