const API_URL =
    "../messapi/api/public/restaurants";


const contenedor =
    document.getElementById("restaurantes");


const mensaje =
    document.getElementById("mensaje");


const buscador =
    document.getElementById("buscador");


let restaurantes = [];



async function cargarRestaurantes() {

    try {

        const respuesta =
            await fetch(API_URL);


        if (!respuesta.ok) {

            throw new Error(
                "No se pudo obtener la información"
            );

        }


        restaurantes =
            await respuesta.json();


        mensaje.innerHTML = "";


        mostrarRestaurantes(restaurantes);


    } catch (error) {

        console.error(error);


        mensaje.innerHTML = `
            <p>
                Error al conectar con la API.
            </p>
        `;

    }

}



function mostrarRestaurantes(lista) {

    contenedor.innerHTML = "";


    if (lista.length === 0) {

        contenedor.innerHTML = `
            <p>
                No se encontraron restaurantes.
            </p>
        `;

        return;
    }



    lista.forEach(restaurante => {

        const tarjeta =
            document.createElement("article");


        tarjeta.classList.add("restaurante");



        const mesasDisponibles =
            restaurante.tables.filter(
                mesa =>
                    mesa.status === "disponible"
                    ||
                    mesa.status === "available"
            );



        const lugaresDisponibles =
            mesasDisponibles.reduce(
                (total, mesa) =>
                    total + mesa.chairs,
                0
            );



        tarjeta.innerHTML = `

            <h2>
                ${restaurante.name}
            </h2>


            <p class="direccion">
                📍 ${restaurante.address}
            </p>


            <p class="descripcion">
                ${restaurante.description ?? ""}
            </p>


            <p class="estado-restaurante">
                ${
                    restaurante.is_open
                        ? "🟢 Abierto"
                        : "🔴 Cerrado"
                }
            </p>


            <div class="resumen">

                <p>
                    Mesas disponibles:
                    <strong>
                        ${mesasDisponibles.length}
                    </strong>
                </p>

                <p>
                    Lugares disponibles:
                    <strong>
                        ${lugaresDisponibles}
                    </strong>
                </p>

                <p>
                    Mesas totales:
                    <strong>
                        ${restaurante.total_tables}
                    </strong>
                </p>

            </div>


            <div class="mesas">

                ${
                    restaurante.tables.length > 0

                    ? restaurante.tables
                        .map(crearMesa)
                        .join("")

                    : `
                        <p class="sin-mesas">
                            Este restaurante no tiene mesas cargadas.
                        </p>
                    `
                }

            </div>

        `;


        contenedor.appendChild(tarjeta);

    });

}



function crearMesa(mesa) {

    let claseEstado =
        mesa.status.toLowerCase();


    let textoEstado =
        mesa.status;


    if (claseEstado === "available") {

        claseEstado = "disponible";

        textoEstado = "Disponible";

    }


    if (claseEstado === "occupied") {

        claseEstado = "ocupada";

        textoEstado = "Ocupada";

    }


    if (claseEstado === "reserved") {

        claseEstado = "reservada";

        textoEstado = "Reservada";

    }


    return `

        <div class="mesa ${claseEstado}">

            <h3>
                Mesa ${mesa.table_number}
            </h3>


            <p>
                👥 ${mesa.chairs} sillas
            </p>


            <p>
                Estado:
                <strong>
                    ${textoEstado}
                </strong>
            </p>


            ${
                mesa.details

                ? `
                    <p>
                        ${mesa.details}
                    </p>
                `

                : ""
            }

        </div>

    `;

}



buscador.addEventListener(
    "input",
    function () {

        const texto =
            buscador.value
                .toLowerCase()
                .trim();


        const filtrados =
            restaurantes.filter(
                restaurante =>

                    restaurante.name
                        .toLowerCase()
                        .includes(texto)
            );


        mostrarRestaurantes(filtrados);

    }
);



cargarRestaurantes();