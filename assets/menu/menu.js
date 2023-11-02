$(`.nav-link`).on(`click`, function (e) {
    e.preventDefault()

    const $this = $(this)
    const $active = $(`.nav-link.active`)

    // style
    $this.addClass("active")
    $active.removeClass("active")
    // view
    title = $this.text()
    url = $this.attr("href")
    route(title, url)
})

window.addEventListener('popstate', async function (e) {
    const currentState = e.state;
    if (currentState) await route(currentState.title, currentState.url);
});

const route = async (title, url) => {
    const BASE_SERVER = CONFIG("BASE_SERVER")

    const request = await fetch(`${BASE_SERVER}/assets/menu/menu.php?action=checkSession`)
    const checkSession = await request.json()

    if (checkSession.status === true) {
        if (url && !["#", ""].includes(url)) {
            const $preloader = $(`.preloader`)
            const headTitle = $(`head title`)

            url = (
                url.startsWith(BASE_SERVER)
                    ? url.replace(BASE_SERVER, "")
                    : (
                        url.startsWith(`/`)
                            ? `${url}`
                            : `/${url}`
                    )
            )

            history.pushState({
                title: title,
                url: url
            }, title, BASE_SERVER + url)

            headTitle.html(title)

            $.ajax(`${BASE_SERVER}/assets/menu/menu.php?action=view`, {
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
    } else window.location.href = BASE_SERVER
}