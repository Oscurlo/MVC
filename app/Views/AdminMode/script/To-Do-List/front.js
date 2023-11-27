$(document).ready(async () => {
    const Config = CONFIG()
    const URL_BACKEND = `${Config.BASE_SERVER}/app/Views/AdminMode/script/To-Do-List/back.php`

    $('.todo-list').sortable({
        placeholder: 'sort-highlight',
        handle: '.handle',
        forcePlaceholderSize: true,
        zIndex: 999999
    })

    $(`#categoria`).on(`change`, function () {
        const $this = $(this)
        const $inputColor = $(`#color`)

        const val = Number($this.val())
        const isNaN = Number.isNaN(val)

        $this.attr(`style`, $this.find(":selected").attr(`style`))

        if (!isNaN) $inputColor.parent().hide(`slow`)
        else $inputColor.parent().show(`slow`)
    })

    $(`#modal-todolist form`).on(`submit`, function (e) {
        const $ToDoList = $(`.todo-list[data-widget="todo-list"]`)

        e.preventDefault()
        $.ajax(`${URL_BACKEND}?action=newToDoList`, {
            type: "POST",
            dataType: "JSON",
            data: new FormData(this),
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                if (response.status && response.status === true) {
                    alerts({ title: "Nueva tarea agregada", icon: "success" })
                    $ToDoList.append(``)
                }
            }
        })
    })

    $(`#modal-categories form`).on(`submit`, function (e) {
        const $categoria = $(`#categoria`)
        const formData = new FormData(this)

        e.preventDefault()
        $.ajax(`${URL_BACKEND}?action=newCategory`, {
            type: "POST",
            dataType: "JSON",
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: (response) => {
                const nombre = formData.get("data[nombre]")
                const color = formData.get("data[color]")

                if (response.status && response.status === true) {
                    alerts({ title: "Categoria agregada", icon: "success" })
                    $categoria.append(elementCreator("option", {
                        value: response.lastInsertId,
                        style: `color: ${color} font-weight: bold`,
                        text: nombre
                    }))
                }
            }
        })
    })
})