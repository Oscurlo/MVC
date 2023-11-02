<?php

$NAME = explode(" ", $this->config::COMPANY["NAME"]);
$NAME = implode(" ", array_map(function ($k, $v) {
    return empty($k) ? "<b>{$v}</b>" : $v;
}, array_keys($NAME), array_values($NAME)));

$HOME_PAGE = $this->config::COMPANY["HOME_PAGE"];
?>

<body class="hold-transition login-page" style="--image: url('<?= $this->config::BASE_SERVER . "/img/ben-griffiths-Bj6ENZDMSDY-unsplash.jpg" ?>')">
    <div class="login-box">
        <div class="card card-outline card-primary glass">
            <div class="card-header text-center">
                <a target="_blank" href="<?= $HOME_PAGE ?>" class="h1"><?= $NAME ?></a>
            </div>
            <div class="card-body">
                <form id="login">
                    <div class="input-group mb-3">
                        <input type="email" name="data[user]" class="form-control" placeholder="Email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="data[pass]" class="form-control" placeholder="Password" autocomplete="on">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                        </div>
                    </div>
                </form>
                <p class="mb-0">
                    <a href="<?= $this->config::BASE_SERVER . "/register" ?>" class="text-center">Registrarme</a>
                </p>
            </div>
        </div>
    </div>
</body>