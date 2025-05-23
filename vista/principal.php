<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/principal.css">
    <link  href="../public/css/remixicon.css" rel="stylesheet">
    <title>Principal</title>
</head>
<body>
    <section class="header"> 
        <div class="logo">
        
        <h2>Soporte <span>Técnico</span></h2>
        </div>
        <div class="search--notification--profile">
        <div class="search">
            <input type="text" placeholder="search Scdule..">
            <button><i class="ri-search-2-line"></i></button>
        </div>
        <div class="notification--profile">
            <img src="../resources/image/lo.png" class="imagenlogo">

        </div>
    </div>
    </section>
    <section class="main">
    <div class="sidebar">
        <ul class="sidebar--items">
            <li>
                <a href="#" id="active--link">
                    <span class="icon icon-1"> <i class="ri-layout-grid-line"></i></span>
                    <span class="sidebar--items"> Panel Principal</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon icon-2"> <i class="ri-calendar-2-line"></i></span>
                    <span class="sidebar--items"> Cronograma</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon icon-3"> <i class="ri-user-2-line"></i></span>
                    <span class="sidebar--items" style="white-space:nowrap;"> Tecnicos </span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon icon-4"> <i class="ri-user-line"></i></span>
                    <span class="sidebar--items"> Panel Principal</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon icon-5"> <i class="ri-line-chart-line"></i></span>
                    <span class="sidebar--items"> Actividad</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon icon-6"> <i class="ri-customer-service-line"></i></span>
                    <span class="sidebar--items"> Soporte</span>
                </a>
            </li>
        </ul>
        <ul class="sidebar--bottom-items">
            <li>
                <a href="#">
                    <span class="icon icon-7"> <i class="ri-settings-3-line"></i></span>
                    <span class="sidebar--items"> Ajustes</span>
                </a>
            </li>
            <li>
                <a href="../php/cerrar_sesion.php">
                    <span class="icon icon-8"> <i class="ri-logout-box-r-line"></i></span>
                    <span class="sidebar--items"> Cerrar sesion</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="main--content">
        <div class="overview">
            <div class="title">
                <h2 class="section--title">Descripcion general</h2>
                <select name="date" id="date" class="dropdown">
                    <option value="today"> Hoy</option>
                    <option value="lastweek"> Semana pasada</option>
                    <option value="lastmonth"> Mes pasado</option>
                    <option value="lastyear"> Año pasado</option>
                    <option value="alltime"> Todo el tiempo</option>
                </select>
            </div>
            <div class="cards">
                <div class="card card-1">
                    <div class="card--data">
                        <div class="card--content">
                            <h5 class="card--title"> Total de Tecnicos</h5>
                            <h1>20</h1>
                            <i class="ri-user-2-line card--icon--lg"></i>
                        </div>
                        <div class="card--stats">
                            <span> <i class="ri-bar-chart-fill card--icon stat--icon"></i>65%</span>
                            <span> <i class="ri-arrow-up-s-fill card--icon up-.arrow"></i>10</span>
                            <span> <i class="ri-arrow-down-fill card--icon down--arrow"></i>2</span>
                        </div>
                    </div>
                </div>
                    <div class="card card-2">
                        <div class="card--data">
                            <div class="card--content">
                                <h5 class="card--title"> Total de Reportes</h5>
                                <h1>20</h1>
                                <i class="ri-user-2-line card--icon--lg"></i>
                            </div>
                            <div class="card--stats">
                                <span> <i class="ri-bar-chart-fill card--icon stat--icon"></i>82%</span>
                                <span> <i class="ri-arrow-up-s-fill card--icon up-.arrow"></i>9</span>
                                <span> <i class="ri-arrow-down-fill card--icon down--arrow"></i>5</span>
                            </div>
                        </div>
                    </div>
                        <div class="card card-3">
                            <div class="card--data">
                                <div class="card--content">
                                    <h5 class="card--title"> Cronograma</h5>
                                    <h1>120</h1>
                                    <i class="ri-calendar-2-line card--icon--lg"></i>
                                </div>
                                <div class="card--stats">
                                    <span> <i class="ri-bar-chart-fill card--icon stat--icon"></i>65%</span>
                                    <span> <i class="ri-arrow-up-s-fill card--icon up-.arrow"></i>10</span>
                                    <span> <i class="ri-arrow-down-fill card--icon down--arrow"></i>2</span>
                                </div>
                            </div>
                        </div>
                            <div class="card card-4">
                                <div class="card--data">
                                    <div class="card--content">
                                        <h5 class="card--title"> Incidencia solvente</h5>
                                        <h1>90</h1>
                                        <i class="ri-hotel-bed-line card--icon--lg"></i>
                                    </div>
                                    <div class="card--stats">
                                        <span> <i class="ri-bar-chart-fill card--icon stat--icon"></i>65%</span>
                                        <span> <i class="ri-arrow-up-s-fill card--icon up-.arrow"></i>10</span>
                                        <span> <i class="ri-arrow-down-fill card--icon down--arrow"></i>2</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tecnic">
                            <div class="title">
                            <h2 class="section--title">Tecnicos</h2>
                            <div class="tecnic--right--btns">
                                <select name="date" id="date" class="dropdown tecnic--filter">
                                    <option>Disponibilidad</option>
                                    <option value="free">Libre</option>
                                    <option value="scheduled"> Ocupado</option>
                                </select>

                                <button class="add"> <i class="ri-add-line"></i>Agregar Tecnico</button>
                            </div>
                        </div>
                        <div class="tecnic--cards">
                            <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="scheduled"> Ocupado</p>
                            </a>

                            <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="free"> Libre</p>
                            </a>
                            <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="scheduled"> Ocupado</p>
                            </a>
                            <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="free"> Libre</p>
                            </a> <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="scheduled"> Ocupado</p>
                            </a> <a href="#" class="tecnic--card">
                                <div class="img--box--cover">
                                    <div class="img--box">
                                    <img src="../resources/image/usuario.png" class="imagentec" alt="">
                                </div>
                            </div>
                            <p class="free"> Libre</p>
                            </a>   
                        </a> <a href="#" class="tecnic--card">
                            <div class="img--box--cover">
                                <div class="img--box">
                                <img src="../resources/image/usuario.png" class="imagentec" alt="">
                            </div>
                        </div>
                        <p class="scheduled"> Ocupado</p>
                        </a> 
                 </div>
            </div>
            <div class="new--report">
                <div class="title">
                    <h2 class="section--title"> Solicitudes</h2>
                    <button class="add"> <i class="ri-add-line"></i> <a href="reporte.php">Nuevo Reporte</a></button>
                </div>
                <div class="table">
                <table>
                    <thead>
                        <tr>
                            <th>NOMBRE</th>
                            <th>FECHA</th>
                            <th>PISO</th>
                            <th>EXTENSION</th>
                            <th>STATUS</th>
                            <th>CONFIGURACION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SIMON RIVAS</td>
                            <td>20/04/2025</td>
                            <td>11</td>
                            <td>666</td>
                            <td class="pending">Pendiente</td>
                            <td> <span> <i class="ri-edit-line edit"></i> <i class="delete-bin-line delete"></i></span></td>
                        </tr>
                        <tr>
                            <td>JAIME RIVAS</td>
                            <td>20/04/2025</td>
                            <td>11</td>
                            <td>666</td>
                            <td class="confirmed">Confirmado</td>
                            <td> <span> <i class="ri-edit-line edit"></i> <i class="delete-bin-line delete"></i></span></td>
                        </tr>
                        <tr>
                            <td>JHOVANNY RIVAS </td>
                            <td>20/04/2025</td>
                            <td>11</td>
                            <td>666</td>
                            <td class="rejected">Redirigido</td>
                            <td> <span> <i class="ri-edit-line edit"></i> <i class="delete-bin-line delete"></i></span></td>
                        </tr>
                    </tbody>
                </table>
             </div>
            </div>
         </div>
     </section>
     <script src="./public/js/js/principal.js"></script>
 </body>

 </html>