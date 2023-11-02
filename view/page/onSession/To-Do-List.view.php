<?php

use Controller\ToDoList;

$ToDoList = new ToDoList();

$breadcrumb = str_replace("index", "Dashboard", substr($this::getURI(), 1));

$dataCategorias = $ToDoList->getCategories();
$dataToDoList = $ToDoList->getToDoList();

?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">To-Do List</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">
                        <?= $breadcrumb ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ion ion-clipboard mr-1"></i>
                            To Do List
                        </h3>
                        <!-- Si me animo hago la paginación -->
                        <!-- <div class="card-tools">
                            <ul class="pagination pagination-sm">
                                <li class="page-item">
                                    <a href="#" class="page-link">&laquo;</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">1</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">2</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">3</a>
                                </li>
                                <li class="page-item">
                                    <a href="#" class="page-link">&raquo;</a>
                                </li>
                            </ul>
                        </div> -->
                    </div>
                    <div class="card-body">
                        <ul class="todo-list" data-widget="todo-list">
                            <?php foreach ($dataToDoList as $i => $data) :
                                $id = $data["id"];
                                $descripcion = $data["descripcion"];
                                $color1 = $data["color"];
                                $color2 = $data["colorCategory"];
                                $categoria = $data["categoria"];

                                $tiempoTrasncurrido = $ToDoList::timeElapsed($data["fechaRegistro"]);

                                if (!is_numeric($categoria)) {
                                    $color = $color1;
                                    $nombreCategoria = "N/A";
                                } else {
                                    $color = $color2;
                                    $nombreCategoria = $data["nameCategory"];
                                }

                                echo trim(<<<HTML
                                    <li data-position="{$i}">
                                        <span class="handle">
                                            <i class="fas fa-ellipsis-v"></i>
                                            <i class="fas fa-ellipsis-v"></i>
                                        </span>
                                        <div class="icheck-primary d-inline ml-2">
                                            <input type="checkbox" value="{$id}" name="todo{$id}" id="todoCheck{$id}">
                                            <label for="todoCheck{$id}"></label>
                                        </div>
                                        <span class="text">{$descripcion}</span>
                                        <small
                                                class="badge"
                                                style="color: #fff; background-color: {$color};"
                                                data-toggle="popover"
                                                title="Categoria"
                                                data-content="{$nombreCategoria}"
                                        >
                                            <i class="far fa-clock"></i> {$tiempoTrasncurrido}
                                        </small>
                                        <div class="tools">
                                            <i class="fas fa-edit"></i>
                                            <i class="fas fa-trash-o"></i>
                                        </div>
                                    </li>
                                HTML);
                            endforeach ?>
                        </ul>
                    </div>
                    <div class="card-footer clearfix">
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modal-todolist">
                            <i class="fas fa-plus"></i> Add item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade modal-primary" id="modal-todolist">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Tareas</h3>
            </div>
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="descripcion">Descripción</label>
                        <textarea name="data[descripcion]" id="descripcion" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoria" class="badget-content">
                            Categorias
                            <button class="btn badget-icon" data-dismiss="modal" data-toggle="modal" data-target="#modal-categories">
                                <i class="fa fa-plus"></i>
                            </button>
                        </label>
                        <select name="data[categoria]" id="categoria" class="form-control">
                            <option value="color libre" selected>Selección de color</option>
                            <?php foreach ($dataCategorias as $data) :
                                echo trim(<<<HTML
                                    <option value="{$data['id']}" style="color: {$data['color']}; font-weight: bold;">
                                        {$data['nombre']}
                                    </option>
                                HTML);
                            endforeach ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="color" name="data[color]" id="color" class="form-control">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade modal-primary" id="modal-categories">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Categorias</h3>
            </div>
            <form>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre">Nombre de la categoria</label>
                        <input type="text" name="data[nombre]" id="nombre" class="form-control" required>
                    </div>
                    <div class="form-group d-nonee">
                        <label for="color">Color</label>
                        <input type="color" name="data[color]" id="color" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-info" data-dismiss="modal" data-toggle="modal" data-target="#modal-todolist">Volver</button>
                    <button type="submit" class="btn btn-primary">Enviar</button>
                </div>
            </form>
        </div>
    </div>
</div>