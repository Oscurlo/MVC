<?php

$NAME = explode(" ", $this->config::COMPANY["NAME"]);
$NAME = implode(" ", array_map(function ($k, $v) {
    return empty($k) ? "<b>{$v}</b>" : $v;
}, array_keys($NAME), array_values($NAME)));

$HOME_PAGE = $this->config::COMPANY["HOME_PAGE"];
?>

<body class="hold-transition register-page" style="--image: url('<?= $this->config::BASE_SERVER . "/img/ben-griffiths-Bj6ENZDMSDY-unsplash.jpg" ?>')">
    <div class="register-box">
        <div class="card card-outline card-primary glass">
            <div class="card-header text-center">
                <a target="_blank" href="<?= $HOME_PAGE ?>" class="h1"><?= $NAME ?></a>
            </div>
            <div class="card-body">
                <form id="register">
                    <div class="input-group mb-3">
                        <input name="data[name]" type="text" class="form-control" placeholder="Nombre Completo" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="data[email]" type="email" class="form-control" placeholder="Correo" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input name="data[password]" type="password" class="form-control" placeholder="Contraseña" required autocomplete="on">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="Confirmar Contraseña" id="checkPass" autocomplete="on">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" name="file[files]" id="exampleInputFile" class="custom-file-input">
                                <label class="custom-file-label" for="exampleInputFile">Cargar logo</label>
                            </div>
                            <div class="input-group-append">
                                <span class="input-group-text">Subir</span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                    </div>
                </form>
                <a href="<?= $this->config::BASE_SERVER . "/login" ?>" class="text-center">Iniciar sessión</a>
            </div>
        </div>
    </div>
</body>