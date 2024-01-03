<?php

/**
 * This class handles server-side processing for DataTables.
 * It extends the ProcessData class to utilize its constructor and destructor for managing database connections.
 *
 */

namespace Config;

use Exception;

class Datatable extends ProcessData
{
    /**
     * Retrieves data for DataTables based on the provided request and configuration.
     *
     * @param array       $request Data sent by DataTables
     * @param string|array $table   Table name or array for joins
     * @param array       $columns Columns configuration
     * @param array       $config  Additional settings
     *
     * @return array Associative array containing DataTables data
     */
    public function serverSide(array $request, string|array $table, array $columns, array $config = []): array
    {
        try {
            # Original table name (for potential joins)
            $originalTable = $table;

            # Format the table name or join array
            $table = self::formatTable($table);

            # Condition for the query
            $condition = $config["condition"] ?? "1 = 1";

            # Columns to be selected in the query
            $showColumn = self::formatColumns($columns, $config);

            # Filtering based on user input
            $filter = self::applyFilter($columns, $request);

            # Sorting order of the results
            $order = self::applyOrder($columns, $request, $originalTable);

            # Limit the number of results
            $limit = self::applyLimit($request);

            # Construct the SQL query
            $query = trim(<<<SQL
                SELECT {$showColumn} FROM {$table} WHERE {$condition} AND ({$filter}) {$order} {$limit}
            SQL);

            # Execute the query
            $result = $this->conn->executeQuery($query);
            $newData = [];

            # Format the results for DataTables
            foreach ($result as $i => $data) foreach ($columns as $key => $value) {
                # Extract the column value based on alias or database name
                $db = explode(".", $value["db"]);
                $string = (isset($value["as"]) ? ($data[$value["as"]] ?? false) : ($data[$db[1] ?? $db[0]] ?? false));

                # Apply formatter function if provided
                if ($string) $newData[$i][] = $value["formatter"] ? self::applyFormatter($string, $value["formatter"], [$string, $data, $key]) : $string;

                # Handle NULL values with a default message
                else $newData[$i][] = $value["failed"] ?? '<b class="text-danger">NULL</b>';
            }


            # Total number of records without filtering
            $queryTotal = trim(<<<SQL
                SELECT count(*) total FROM {$table} WHERE {$condition}
            SQL);
            $recordsTotal = $this->conn->executeQuery($queryTotal)[0]["total"] ?? 0;

            # Total number of records after filtering
            $queryFiltered = trim(<<<SQL
                SELECT count(*) total FROM {$table} WHERE {$condition} AND ({$filter})
            SQL);
            $recordsFiltered = $this->conn->executeQuery($queryFiltered)[0]["total"] ?? 0;

            # Return the DataTables data
            return [
                "draw"            => $request["draw"],
                "recordsTotal"    => $recordsTotal,
                "recordsFiltered" => $recordsFiltered,
                "data"            => $newData,
            ];
        } catch (Exception $th) {
            # Log the error or handle it more gracefully
            return [
                "error" => $th->getMessage(),
            ];
        }
    }

    private function formatTable($t): String
    {
        $type = strtoupper(gettype($t));

        if ($type === "ARRAY") {
            return implode(" ", $t);
        } else {
            return $t;
        }
    }

    private function formatColumns($col, $con): String
    {
        return $con["columns"] ?? implode(", ", array_map(function ($columns) {
            $alias = $columns["as"] ?? false;
            return $columns["db"] . ($alias ? " as {$alias}" : "");
        }, $col));
    }

    private function applyFilter($col, $req): String
    {
        return implode(" OR ", array_map(function ($columns) use ($req) {
            $search = "%{$req["search"]["value"]}%";
            return "{$columns["db"]} LIKE '{$search}'";
        }, $col));
    }

    private function applyOrder($col, $req, $ot): String
    {
        // $type = strtoupper(gettype($ot));

        $column = $col[$req["order"][0]["column"]]["db"];
        $order = $req["order"][0]["dir"];

        // return [
        //     "ARRAY" => "ORDER BY {$ot[0]}.{$column} {$order}",
        //     "STRING" => "ORDER BY {$ot}.{$column} {$order}"
        // ][$type] ?? "ORDER BY {$column} {$order}";

        return "ORDER BY {$column} {$order}";
    }

    private function applyLimit($req): String
    {
        return [
            "MYSQL" => "LIMIT {$req["start"]}, {$req["length"]}",
            "SQLITE" => "LIMIT {$req["start"]} OFFSET {$req["length"]}", # I haven't worked much with SQLite, not sure if this is correct :(
            "SQLSRV" => "OFFSET {$req["start"]} ROWS FETCH NEXT {$req["length"]} ROWS ONLY"
        ][$this->conn->getGestor()] ?? "";
    }

    /**
     * @param String $oldString Database value
     * @param Mixed $newString New value to convert to
     * @param Array $data Column data being processed
     * @return String Returns the new value
     */
    private function applyFormatter(String $oldString, Mixed $newString, array $data = []): String
    {
        $type = strtoupper(gettype($newString));

        if ($type === "STRING") {
            return str_replace("@this", $oldString, $newString); # Simple string formatting
        } elseif ($type === "OBJECT") {
            return $newString(...$data); # Function formatting
        } else {
            return $oldString;
        }
    }
}
