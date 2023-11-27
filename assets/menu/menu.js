var Config = CONFIG()
$(document).on(`click`, `#btnDisconnect`, async function () {
    const request = await fetch(`${Config.BASE_SERVER}/assets/menu/menu.php?action=disconnect`)

    if (request.status === 200) window.location.href = Config.BASE_SERVER
})

// Intento de controlar todas las redirecciones
$(document).on(`click`, `[href][href!=""][href!="#"][href*="${Config.BASE_SERVER}"]`, function (e) {
    e.preventDefault()

    const $this = $(this)

    if ($this.hasClass("nav-link")) {
        const $active = $(`.nav-link.active`)
        $this.addClass("active")
        $active.removeClass("active")
    }

    title = $this.text()
    url = $this.attr("href")
    route(title, url)
})

const $CodeMirror = $(`#CodeMirrorException, #CodeMirrorError`).each(function () {
    const $this = $(this)
    if ($this.length) {
        const line = parseInt($this.data('line')) - 1
        const message = $this.data('message')

        const editor = CodeMirror.fromTextArea($this.get(0), {
            mode: "application/x-httpd-php",
            theme: "ayu-dark",
            value: $this.val(),
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: true,
            lineNumbers: true
        })

        editor.addLineClass(line, "text-decoration", "CodeMirror-error-line")

        $this.siblings().popover({
            trigger: 'hover',
            content: message
        })
    }
})

window.addEventListener('popstate', async function (e) {
    const currentState = e.state
    if (currentState) await route(currentState.title, currentState.url)
})

const route = async (title, url = "/index") => {
    const Config = CONFIG()

    const request = await fetch(`${Config.BASE_SERVER}/assets/menu/menu.php?action=checkSession`)
    const checkSession = await request.json()

    if (checkSession.status === true) {
        if (url && !["#", ""].includes(url)) {
            const $preloader = $(`.preloader`)
            const headTitle = $(`head title`)

            url = url.replace(Config.BASE_SERVER, "") || "/index"

            history.pushState({
                title: title,
                url: url
            }, title, Config.BASE_SERVER + url)

            headTitle.html(title)

            $.ajax(`${Config.BASE_SERVER}/assets/menu/menu.php?action=view`, {
                type: "POST",
                dataType: "HTML",
                data: { view: url },
                beforeSend: () => {
                    $preloader.removeAttr(`style`).find(`img`).removeAttr(`style`)
                },
                success: (response) => {
                    const $router = $(`[data-router]`)
                    $router.replaceWith(response)
                },
                complete: () => {
                    const loadJS = $(`LOAD-SCRIPT`)
                    if (loadJS.length) {
                        JSON.parse(loadJS.text()).forEach((e) => {
                            $.getScript(e)
                        })
                        loadJS.remove()
                    }
                    setTimeout(() => {
                        $preloader.css({ height: 0 }).find(`img`).css({ display: "none" })
                    }, 1000)
                }
            })
        }
    } else window.location.href = Config.BASE_SERVER
}