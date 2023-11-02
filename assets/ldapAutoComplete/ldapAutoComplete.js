/**
 * Esta función es uno de los mayores pajasos mentales que he tenido con js ༼ つ ◕_◕ ༽つ 
 * El caso, pensando en como hacer que se viera bien un autocompletado, pensé en el buscador de google y tome la idea para hacer esta función
 * Ejemplo de uso...
const autoComplete = [
    {
        element: `#mail`, // Indicador del elemento
        event: `input`, // Evento con el que va a reaccionar la función
        search: `mail` // Campo del directorio activo con el que va a completar
    }
]
const config = { // No es obligatorio
    limit: 1, // Limite de resultados
    url: "backend.php" // De momento me parece útil que también pueda cambiar la url ya sea que quiera obtener los datos de otro lado
}
ldapAutoComplete(autoComplete, config)
*/
const ldapAutoComplete = (array, config = {}) => {
    const defaultConfig = {
        limit: 1,
        url: `${CONFIG("BASE_SERVER")}/assets/ldapAutoComplete/ldapAutoComplete.php`,
        dataForLDAP: true
    }

    const newConfig = $.extend(defaultConfig, typeof config === `object` ? config : {})

    if (typeof array === `object`) array.forEach(autoComplete => {

        const $find = $(autoComplete[`element`] ?? false)

        if ($find.length && !$find.data("ldapautocomplete")) {

            $find.attr("data-ldapautocomplete", true)

            const tagName = $find.prop("tagName")

            if (tagName === "INPUT") {
                const $container = $(elementCreator("div", {
                    class: "position-relative"
                }))

                const $input1 = $find.clone().appendTo($container)
                const $input2 = $find.clone().appendTo($container)

                $input2.css({
                    "background-color": "transparent",
                    position: "absolute",
                    top: 0,
                    left: 0,
                    "z-index": 1,
                    "pointer-events": "none"
                }).removeAttr("name").removeAttr("required")

                $find.replaceWith($container)

                $input1.on(`${autoComplete["event"] ?? "input"}`, () => {
                    const val1 = $input1.val()
                    const val2 = $input2.val()
                    if (!val2.toUpperCase().startsWith(val1.toUpperCase())) $.ajax(newConfig.url, {
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            limit: 1,
                            filter: [autoComplete["search"] ?? false],
                            search: `${val1}*`
                        },
                        success: (response) => {
                            if (response.length && !response.error && autoComplete.search && val1 != val2)
                                if (response[0][autoComplete.search] ?? false) {
                                    const search = (
                                        newConfig.dataForLDAP == true
                                            ? (response[0][autoComplete.search][0] ?? false) // datos de ldap
                                            : response[0][autoComplete.search] // datos de arreglo
                                    )
                                    $input2.val(search ? val1 + search.substr(val1.length) : "")
                                } else $input2.val("")
                            else $input2.val("")
                        }
                    })
                    else $input2.val(val2 ? val1 + val2.substr(val1.length) : "")
                }).on("keypress keydown", (e) => {
                    const val1 = $input1.val()
                    const val2 = $input2.val()
                    if (e.which === 9 && val2 != "" && val1 != val2) { // Evento de la tecla "Tab ↹"
                        e.preventDefault()
                        $input1.val(val2)
                    }
                }).on("blur", () => {
                    $input2.val("")
                })
            } else console.log(`dio mio, pero eto que eh: ${tagName}`)
        }
    })
}