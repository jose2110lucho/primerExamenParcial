@section('title', 'Home')
<x-app-layout>
    <div class="page">
        <div class="page-wrapper">
            <div class="container-xl">
                <!-- Page title -->
                <div class="page-header d-print-none">
                    <div class="row g-2 align-items-center">
                        <div class="col">
                            <!-- Page pre-title -->
                            <div class="page-pretitle">
                                Inicio
                            </div>
                            <h2 class="page-title">
                                UML Diagramador
                            </h2>
                        </div>
                        <!-- Page title actions -->
                        <div class="col-12 col-md-auto ms-auto d-print-none">
                            <div class="btn-list">

                                <a href="{{route('proyectos.index')}}" class="btn btn-white">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-layout-2" width="44" height="44"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="#597e8d" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <rect x="4" y="4" width="6" height="5"
                                            rx="2" />
                                        <rect x="4" y="13" width="6" height="7"
                                            rx="2" />
                                        <rect x="14" y="4" width="6" height="7"
                                            rx="2" />
                                        <rect x="14" y="15" width="6" height="5"
                                            rx="2" />
                                    </svg>
                                    Proyectos
                                </a>

                                <a href="{{route('profile.index')}}" class="btn btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-settings" width="44" height="44"
                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Mi cuenta
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="page-body">
                <div class="container-xl">
                    <div class="row row-cards mb-2">

                        

                        <div {{-- class="col-md-4 col-lg-6" --}}>
                            {{-- <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Proyectos Favoritos</h3>
                                </div>
                                <div class="card-body">
                                    @if (count($proyectos) > 0)
                                        <div class="row row-cards">
                                            @foreach ($proyectos as $proyecto)
                                                <div class="col-12">
                                                    <div class="card card-sm">
                                                        <div class="card-body">
                                                            <div class="row align-items-center">
                                                                <div class="col-2">
                                                                    @if ($proyecto->url)
                                                                        <img src="{{ asset('storage/' . $proyecto->url) }}"
                                                                            alt="Food Deliver UI dashboards"
                                                                            class="rounded">
                                                                    @else
                                                                        <img src="{{ asset('/assets/img/image-default.jpg') }}"
                                                                            alt="Food Deliver UI dashboards"
                                                                            class="rounded height-min">
                                                                    @endif
                                                                </div>

                                                                <div class="col">
                                                                    <div class="font-weight-medium">
                                                                        <a href="{{route('diagramas.index', $proyecto->id)}}" title="ver diagramas">
                                                                            {{ $proyecto->nombre }}
                                                                        </a>
                                                                    </div>
                                                                    <div class="text-muted">
                                                                        {{ $proyecto->calificacion }}
                                                                    </div>
                                                                </div>

                                                                <div class="col-auto">
                                                                    <div class="btn-action">
                                                                        <button class="switch-icon switch-icon-fade"
                                                                            data-bs-toggle="switch-icon" title="Favorito"
                                                                            onclick="favorito({{ $proyecto->id }})">
                                                                            @if ($proyecto->favorito == 1)
                                                                                <span class="switch-icon-a text-red mt-1">
                                                                                    
                                                                                </span>
                                                                                <span class="switch-icon-b text-muted mt-1">
                                                                                    <i
                                                                                        class="fa-regular fa-heart text-pink"></i>
                                                                                </span>
                                                                            @else
                                                                                <span class="switch-icon-a text-red mt-1">
                                                                                    <i
                                                                                        class="fa-regular fa-heart text-pink"></i>
                                                                                </span>
                                                                                <span class="switch-icon-b text-muted mt-1">
                                                                                    <i class="fa-solid fa-heart text-pink"></i>
                                                                                </span>
                                                                            @endif
                                                                        </button>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="card mt-1">
                                            <div class="card-body pb-0">
                                                <div class="pagination">
                                                    {{ $proyectos->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <h6>No tienes Proyectos Favoritos</h6>
                                    @endif
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card card-md">
                            <div class="card-stamp card-stamp-lg">
                                <div class="card-stamp-icon bg-primary">
                                    <!-- Download SVG icon from http://tabler-icons.io/i/ghost -->
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M5 11a7 7 0 0 1 14 0v7a1.78 1.78 0 0 1 -3.1 1.4a1.65 1.65 0 0 0 -2.6 0a1.65 1.65 0 0 1 -2.6 0a1.65 1.65 0 0 0 -2.6 0a1.78 1.78 0 0 1 -3.1 -1.4v-7" />
                                        <line x1="10" y1="10" x2="10.01" y2="10" />
                                        <line x1="14" y1="10" x2="14.01" y2="10" />
                                        <path d="M10 14a3.5 3.5 0 0 0 4 0" />
                                    </svg>
                                </div>
                            </div>
                            {{-- <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-10 mb-2">
                                        <h3 class="h1">Diagramas de núcleo</h3>
                                        <div class="markdown text-muted">
                                            La visualización de esta jerarquía de abstracciones se realiza mediante la
                                            creación de una colección de diagramas de <b>Contexto</b>, <b>Contenedor</b>
                                            , <b>Componente</b> y (opcionalmente) <b>Código</b> (por ejemplo, clase
                                            UML). Aquí es donde el modelo C4 recibe su nombre.
                                        </div>

                                    </div>
                                    <hr>
                                    <div class="col-12 row mb-2">
                                        <div class="col-3 row">
                                            <div><img src="{{ asset('/assets/img/image-level-1.png') }}"
                                                    alt=""></div>
                                            <div><img src="{{ asset('/assets/img/image-level-1-sub.png') }}"
                                                    alt=""></div>
                                        </div>
                                        <div class="col-8">
                                            <div class="ms-2">
                                                <h3 class="h1">Nivel 1: diagrama de contexto del sistemaEnlace</h3>
                                                <span class="text-muted">
                                                    <p class="mb-1">Un diagrama de contexto del sistema es un buen
                                                        punto
                                                        de partida para
                                                        diagramar y documentar un sistema de software, lo que le permite
                                                        dar
                                                        un paso
                                                        atrás y ver el panorama general. Dibuje un diagrama que muestre
                                                        su
                                                        sistema
                                                        como un cuadro en el centro, rodeado por sus usuarios y los
                                                        otros
                                                        sistemas
                                                        con los que interactúa.</p>
                                                    <p>Los detalles no son importantes aquí, ya que esta es su vista
                                                        alejada
                                                        que
                                                        muestra una imagen grande del panorama del sistema. El enfoque
                                                        debe
                                                        estar en
                                                        las personas (actores, roles, personajes, etc.) y los sistemas
                                                        de
                                                        software
                                                        en lugar de las tecnologías, los protocolos y otros detalles de
                                                        bajo
                                                        nivel.
                                                        Es el tipo de diagrama que podría mostrar a personas no
                                                        técnicas.
                                                    </p>
                                                    <span style="font-size: 10px">
                                                        <p class="mb-1"><b>Alcance : </b>Un solo
                                                            sistema de software.
                                                        </p>
                                                        <p class="mb-1"><b>Elementos primarios :
                                                            </b>El sistema de
                                                            software en el alcance.</p>
                                                        <p class="mb-1"><b>Elementos de soporte :
                                                            </b>personas (por
                                                            ejemplo, usuarios, actores, roles o
                                                            personas) y sistemas de software (dependencias externas) que
                                                            están
                                                            directamente conectados al sistema de software en el
                                                            alcance.
                                                            Por lo
                                                            general, estos otros sistemas de software se encuentran
                                                            fuera
                                                            del alcance o
                                                            los límites de su propio sistema de software, y usted no
                                                            tiene
                                                            responsabilidad ni propiedad sobre ellos.
                                                        </p>
                                                        <p class="mb-1"><b>Público objetivo :
                                                            </b>todos, tanto
                                                            técnicos
                                                            como no técnicos, dentro y fuera
                                                            del equipo de desarrollo de software.</p>
                                                        <p class="mb-1"><b>Recomendado para la
                                                                mayoría de los equipos
                                                                :
                                                            </b><span class="text-success">Sí.</span></p>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-12 row mb-2">
                                        <div class="col-3 row">
                                            <div><img src="{{ asset('/assets/img/image-level-2.png') }}"
                                                    alt=""></div>
                                            <div><img src="{{ asset('/assets/img/image-level-2-sub.png') }}"
                                                    alt=""></div>
                                        </div>
                                        <div class="col-8">
                                            <div class="markdown ms-2">
                                                <h3 class="h1">Nivel 2: Diagrama de contenedorEnlace</h3>
                                                <span class="text-muted">
                                                    <p class="mb-1">Una vez que comprenda cómo encaja su sistema en
                                                        el
                                                        entorno de TI general, el siguiente paso realmente útil es
                                                        acercarse
                                                        al límite del sistema con un diagrama de contenedor. Un
                                                        "contenedor"
                                                        es algo así como una aplicación web del lado del servidor, una
                                                        aplicación de una sola página, una aplicación de escritorio, una
                                                        aplicación móvil, un esquema de base de datos, un sistema de
                                                        archivos, etc. Básicamente, un contenedor es una unidad
                                                        ejecutable/implementable por separado (por ejemplo, un espacio
                                                        de
                                                        proceso separado ) que ejecuta código o almacena datos.
                                                    </p>
                                                    <p class="mb-1">El diagrama de contenedores muestra la forma de
                                                        alto
                                                        nivel de la arquitectura del software y cómo se distribuyen las
                                                        responsabilidades a través de ella. También muestra las
                                                        principales
                                                        opciones tecnológicas y cómo los contenedores se comunican entre
                                                        sí.
                                                        Es un diagrama centrado en la tecnología simple y de alto nivel
                                                        que
                                                        es útil para los desarrolladores de software y el personal de
                                                        soporte/operaciones por igual.
                                                    </p>
                                                    <span style="font-size: 10px">
                                                        <p class="mb-1"><b>Alcance : </b>Un solo sistema de software.
                                                        </p>

                                                        <p class="mb-1"><b>Elementos primarios : </b>Contenedores
                                                            dentro
                                                            del sistema de software en
                                                            el alcance.</p>
                                                        <p class="mb-1"><b>Elementos de apoyo : </b>Personas y
                                                            sistemas
                                                            de software conectados
                                                            directamente a los contenedores.</p>

                                                        <p class="mb-1"><b>Público objetivo : </b>personas técnicas
                                                            dentro y fuera del equipo de
                                                            desarrollo de software; incluyendo arquitectos de software,
                                                            desarrolladores y personal de operaciones/soporte.</p>

                                                        <p class="mb-1"><b>Recomendado para la mayoría de los equipos
                                                                :
                                                            </b><span class="text-success">Sí.</span></p>

                                                        <p class="mb-1"><b>Notas : </b>este diagrama no dice nada
                                                            sobre
                                                            escenarios de
                                                            implementación, agrupación, replicación, conmutación por
                                                            error,
                                                            etc.</p>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-12 row mb-2">
                                        <div class="col-3 row">
                                            <div><img src="{{ asset('/assets/img/image-level-3.png') }}"
                                                    alt=""></div>
                                            <div><img src="{{ asset('/assets/img/image-level-3-sub.png') }}"
                                                    alt=""></div>
                                        </div>
                                        <div class="col-8">
                                            <div class="markdown ms-2">
                                                <h3 class="h1">Nivel 3: diagrama de componentesEnlace</h3>
                                                <span class="text-muted">
                                                    <p class="mb-1">A continuación, puede acercar y descomponer aún
                                                        más
                                                        cada contenedor para identificar los principales bloques de
                                                        construcción estructurales y sus interacciones.
                                                    </p>
                                                    <p class="mb-1">El diagrama de componentes muestra cómo un
                                                        contenedor
                                                        se compone de una serie de "componentes", qué es cada uno de
                                                        esos
                                                        componentes, sus responsabilidades y los detalles de
                                                        tecnología/implementación.
                                                    </p>
                                                    <span style="font-size: 10px">
                                                        <p class="mb-1"><b>Alcance : </b>Un solo contenedor.</p>

                                                        <p class="mb-1"><b>Elementos primarios : </b>Componentes
                                                            dentro del contenedor en el
                                                            alcance.</p>
                                                        <p class="mb-1"><b>Elementos de soporte : </b>Contenedores
                                                            (dentro del alcance del sistema
                                                            de software) más personas y sistemas de software conectados
                                                            directamente a los componentes.</p>

                                                        <p class="mb-1"><b>Público objetivo : </b>Arquitectos y
                                                            desarrolladores de software.</p>

                                                        <p class="mb-1"><b>Recomendado para la mayoría de los equipos
                                                                :
                                                            </b><span class="text-danger">no, solo cree diagramas
                                                                de componentes si cree que agregan valor y considere
                                                                automatizar su
                                                                creación para una documentación de larga
                                                                duración.</span>
                                                        </p>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="col-12 row mb-2">
                                        <div class="col-3 row">
                                            <div><img src="{{ asset('/assets/img/image-level-4.png') }}"
                                                    alt=""></div>

                                        </div>
                                        <div class="col-8">
                                            <div class="markdown ms-2">
                                                <h3 class="h1">Nivel 4: CódigoEnlace</h3>
                                                <span class="text-muted">
                                                    <p class="mb-1">Finalmente, puede ampliar cada componente para
                                                        mostrar cómo se implementa como código; utilizando diagramas de
                                                        clases UML, diagramas entidad relación o similares.
                                                    </p>
                                                    <p class="mb-1">Este es un nivel de detalle opcional y, a menudo,
                                                        está disponible a pedido desde herramientas como IDE.
                                                        Idealmente,
                                                        este diagrama se generaría automáticamente utilizando
                                                        herramientas
                                                        (por ejemplo, una herramienta de modelado IDE o UML), y debería
                                                        considerar mostrar solo aquellos atributos y métodos que le
                                                        permitan
                                                        contar la historia que desea contar. Este nivel de detalle no se
                                                        recomienda para nada más que para los componentes más
                                                        importantes o
                                                        complejos.
                                                    </p>
                                                    <span style="font-size: 10px">
                                                        <p class="mb-1"><b>Alcance : </b>Un solo componente.</p>

                                                        <p class="mb-1"><b>Elementos primarios : </b>elementos de
                                                            código
                                                            (por ejemplo, clases,
                                                            interfaces, objetos, funciones, tablas de bases de datos,
                                                            etc.)
                                                            dentro del componente en el ámbito.</p>

                                                        <p class="mb-1"><b>Público objetivo : </b>Arquitectos y
                                                            desarrolladores de software.</p>

                                                        <p class="mb-1"><b>Recomendado para la mayoría de los equipos
                                                                :
                                                            </b><span class="text-danger">no, para la
                                                                documentación de larga duración, la mayoría de los IDE
                                                                pueden
                                                                generar este nivel de detalle a pedido.</span></p>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
    @push('scripts')
    <script>
        function favorito(proyecto_id) {
                $.ajax({
                    type: "POST",
                    url: "{{ url('proyectos/favorito') }}",
                    data: {
                        id: proyecto_id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'JSON',
                    success: function() {
                    },
                });
            };
    </script>
    @endpush
</x-app-layout>
