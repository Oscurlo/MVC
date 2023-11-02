<?php

/**
 * Todo este código lo hice leyendo la documentación oficial de datatable
 * https://datatables.net/manual/server-side
 */

namespace Model;

use Exception;

class Datatable extends ProcessData
{
    /**
     * Establecí una herencia hacia la clase de "ProcessData" con el objetivo de tener el constructor y destructor que permite cambiar o establecer la conexión a la base de datos
     * 
     * @param array $request Datos enviados por el datatable
     * @param string|array $table Pensando un poco en que esta clase la hice para ser usada con joins
     * deje que se pudiera enviar un string o un arreglo,
     * siendo el string solo el nombre de la tabla o el arreglo para hacer joins.
     * Con la explicación creo que solo me entiendo yo, así que mejor vean algún ejemplo
     * @param array $columns La forma de estructurar los datos lo hice siguiendo el código de datatable
     * para que no fuera más confuso,
     * así especial que agregue fue la posibilidad de agregar un alias y poder usarlos con el nombre de las columnas "alias.nombre"
     * @param $config Más que una configuración es como un extra,
     * creo que me hizo falta algo más de imaginación para este campo,
     * pero esto fue lo que agregue de momento
     * 1. $config["condition]: Condición para la consulta normal "1 = 1"
     * 2. $config["columns]: Ya que el código solo acede a las columnas que se envíen y no a las de toda la tabla,
     * agregue este campo para que agreguen las columnas que quieran usar según yo es más delicado,
     * así porque pueden generar errores sin querer
     */
    public function serverSide(
        array $request,
        string|array $table,
        array $columns,
        array $config = []
    ): array {
        try {
            # table
            $originalTable = $table;
            $table = self::table($table);

            # codition
            $condition = $config["condition"] ?? "1 = 1";

            # columns
            $showColumn = self::columns($columns, $config);

            # filter
            $filter = self::filter($columns, $request);

            # order
            $order = self::order($columns, $request, $originalTable);

            # limit
            $limit = self::limit($request); # De momento no está funcional del todo, ya que si le envió una conexión diferente al constructor fallaría "No lo he probado, pero lo intuyo"

            # query
            $query = trim(<<<SQL
                SELECT {$showColumn} FROM {$table} WHERE {$condition} AND ({$filter}) {$order} {$limit}
            SQL);

            $result = $this->conn->executeQuery($query);
            $newData = [];

            # formatter
            foreach ($result as $i => $data) foreach ($columns as $key => $value) {
                $db = explode(".", $value["db"]);
                $string = (isset($value["as"]) ? ($data[$value["as"]] ?? false) : ($data[$db[1] ?? $db[0]] ?? false));

                if ($string) if ($value["formatter"] ?? false) $newData[$i][] = self::formatter(
                    $string,
                    $value["formatter"],
                    [
                        $string,  // valor de la base de datos
                        $data,   // datos de la fila
                        $key    // identificador
                    ]
                );
                else $newData[$i][] = $string;
                else $newData[$i][] = $value["failed"] ?? trim(<<<HTML
                    <b class="text-danger">NULL</b>
                HTML);
            }

            # total query result
            $queryTotal = trim(<<<SQL
                SELECT count(*) total FROM {$table} WHERE {$condition}
            SQL);
            $recordsTotal = $this->conn->executeQuery($queryTotal)[0]["total"] ?? 0;

            # total filtered result of the query
            $queryFiltered = trim(<<<SQL
                SELECT count(*) total FROM {$table} WHERE {$condition} AND ({$filter})
            SQL);
            $recordsFiltered = $this->conn->executeQuery($queryFiltered)[0]["total"] ?? 0;

            return [
                "draw" => $request["draw"],
                "recordsTotal" => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data" => $newData
                // "query" => $query
            ];
        } catch (Exception $th) {
            # No sé si sea buena idea enviar el error así ya que esto lo va a mostrar datatable en una alerta, luego lo corrijo :c
            return [
                "error" => $th->getMessage()
            ];
        }
    }

    private function table($t): String
    {
        $type = strtoupper(gettype($t));

        if ($type === "ARRAY") return implode(" ", $t);
        else return $t;
    }

    private function columns($col, $con): String
    {
        return $con["columns"] ?? implode(", ", array_map(function ($columns) {
            $alias = $columns["as"] ?? false;
            return $columns["db"] . ($alias ? " as {$alias}" : "");
        }, $col));
    }

    private function filter($col, $req): String
    {
        return implode(" OR ", array_map(function ($columns) use ($req) {
            $search = "%{$req["search"]["value"]}%";
            return "{$columns["db"]} LIKE '{$search}'";
        }, $col));
    }

    private function order($col, $req, $ot): String
    {
        $type = strtoupper(gettype($ot));

        if ($type === "STRING") $ot = explode(" ", trim($ot))[0];

        $column = $col[$req["order"][0]["column"]]["db"];
        $order = $req["order"][0]["dir"];

        return [
            "ARRAY" => "ORDER BY {$ot[0]}.{$column} {$order}",
            "STRING" => "ORDER BY {$ot}.{$column} {$order}"
        ][$type] ?? "ORDER BY {$column} {$order}";
    }

    private function limit($req): String
    {
        return [
            "MYSQL" => "LIMIT {$req["start"]}, {$req["length"]}",
            "SQLITE" => "LIMIT {$req["start"]} OFFSET {$req["length"]}", # No he trabajado mucho con sqlite no se si esta bien :(
            "SQLSRV" => "OFFSET {$req["start"]} ROWS FETCH NEXT {$req["length"]} ROWS ONLY"
        ][$this->conn->getGestor()] ?? "";
    }

    /**
     * @param String $oldString Valor de la base de datos
     * @param Mixed $newString Nuevo valor al que lo quieran convertir
     * @param Array $data Datos de la columna que este recorriendo
     * @return String retorna el nuevo valor
     */
    private function formatter(String $oldString, Mixed $newString, array $data = []): String
    {
        $type = strtoupper(gettype($newString));

        if ($type === "STRING") return str_replace("@this", $oldString, $newString); # Formateo simple de una cadena
        elseif ($type === "OBJECT") return $newString(...$data); # Formateo de una función
        else return $oldString;
    }
}
