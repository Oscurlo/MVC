// 2023-10-27 14:31:22

$(document).ready(async () => {
    $.widget.bridge('uibutton', $.ui.button)

    $(`[data-toggle="popover"]`).popover({
        trigger: 'hover'
    })
})