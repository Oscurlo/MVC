$(function () {
    try {
        const Calendar = FullCalendar.Calendar
        const $calendarEl = $("#calendar").get(0)
        const $modal = $("#modal-calendar")
        const Config = CONFIG()
        const URL_BACKEND = `${Config.BASE_SERVER}/app/Views/AdminMode/script/Calendar/back.php`

        const handleEventClick = (info) => {
            const eventId = info.event.id
            $modal.find("[data-mode]").data("mode", "showEvent").data("id", eventId)
            $modal.modal("show")
        }

        const handleEventDrop = (info) => {
            const newStartDate = info.event.start.toISOString()
            const newEndDate = info.event.end.toISOString()

            const eventId = info.event.id

            $.ajax(`${URL_BACKEND}?action=modifyEvent&id=${eventId}`, {
                type: "POST",
                dataType: "JSON",
                data: {
                    "data[start]": newStartDate,
                    "data[end]": newEndDate
                },
                success: (response) => {
                    const title = response.status ? "Se Actualizó La Fecha del evento." : "Ha ocurrido un error al intentar actualizar el evento."
                    const icon = response.status ? "success" : "error"
                    alerts({ title: title, icon: icon })
                }
            })
        }

        const handleDateClick = (info) => {
            $modal.find("[data-mode]").data("mode", "formAddEvent")
            $modal.modal("show")
        }

        const handleEventDidMount = (info) => {
            $(info.el).popover({
                title: info.event.title,
                content: info.event.extendedProps.description,
                placement: "top",
                trigger: "hover",
                container: "body",
            })
        }

        const handleModalShown = async function () {
            const $modal = $(this)
            const $modalBody = $modal.find(".modal-body")
            const $modalTitle = $modal.find(".modal-title")

            const id = $modalBody.data("id")
            const mode = $modalBody.data("mode")

            try {
                const response = await $.ajax(`${URL_BACKEND}?action=${mode}`, {
                    type: "POST",
                    dataType: "HTML",
                    data: { id: id },
                })

                const $html = $(response)
                const tagName = $html.prop("tagName")

                if (tagName === "FORM") {
                    if (mode === "formAddEvent") $modalTitle.text("Agregar Evento")
                    else $modalTitle.text("Actualizar Evento")
                    $html.data("id", id)
                } else if (tagName === "DL") $modalTitle.text($html.data("title"))

                $modalBody.html($html)
            } catch (error) {
                console.error(error)
                alerts({ title: "Ha ocurrido un error al cargar el contenido.", icon: "error" })
            }
        }

        const handleFormSubmit = function (e) {
            e.preventDefault()
            const $form = $(this)
            const id = $form.data("id")
            const mode = $form.data("mode")
            $modal.modal("hide")

            $.ajax(`${URL_BACKEND}?action=${mode}&id=${id}`, {
                type: "POST",
                dataType: "JSON",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                success: (response) => {
                    const title = response.status ? (mode == "modifyEvent" ? "Se Actualizó evento." : "Se agrego un nuevo evento") : "Ha ocurrido un error al intentar actualizar el evento."
                    const icon = response.status ? "success" : "error"
                    alerts({ title: title, icon: icon })

                    if (response.status) refreshCalendarEvents() // Agregar paréntesis aquí
                }

            })
        }

        const handleEventUpdate = function () {
            const $btn = $(this)
            const id = $btn.data("event-update")
            $modal.find("[data-mode]").data("mode", "formModifyEvent").data("id", id)
            setTimeout(() => {
                $modal.modal("show")
            }, 1000)
        }

        const handleInputAllDay = function (params) {
            const $input = $(this)
            console.log($input);
        }

        const refreshCalendarEvents = () => calendar.refetchEvents()

        const calendar = new Calendar($calendarEl, {
            headerToolbar: {
                left: "title",
                center: "dayGridMonth,timeGridWeek,timeGridDay",
                right: "prev,next today",
            },
            themeSystem: "bootstrap",
            events: `${URL_BACKEND}?action=getEvents`,
            editable: true,
            droppable: true,
            timeZone: Config.TIMEZONE,
            locale: Config.LANGUAGE,
            eventClick: handleEventClick,
            eventDrop: handleEventDrop,
            dateClick: handleDateClick,
            eventDidMount: handleEventDidMount
        })

        calendar.render()

        // Eventos
        $modal.on("shown.bs.modal", handleModalShown)
        $modal.on("submit", "form", handleFormSubmit)
        $modal.on("click", "[data-event-update]", handleEventUpdate)
        $modal.on("change", `[name="data[allDay]"]`, handleInputAllDay)

    } catch (error) {
        console.error(error)
        alerts({ title: "Ha ocurrido un error inesperado en el calendario :/", icon: "error" })
    }
})
