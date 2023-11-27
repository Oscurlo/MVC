<?php

namespace Controller;

use Exception;
use Model\Html;
use Model\ProcessData;

class Calendar extends ProcessData
{
    private const TABLE_CALENDAR = "Calendar";
    private const TABLE_CALENDAR_DISPLAY = "EventDisplay";

    public function getEvents(String $start, String $end, bool $render = false, ?string $condition = null): array
    {
        if (!$this->tableManager->checkTableExists(self::TABLE_CALENDAR)) $this->createTableCalendar();

        $query = "SELECT * FROM " . self::TABLE_CALENDAR . " WHERE fechaRegistro BETWEEN :START AND :END" . ($condition ? " and {$condition}" : "");

        $data = $this->conn->executeQuery($query, [
            ":START" => $start,
            ":END" => $end
        ]);

        return $render === true ? self::renderEvents($data) : $data;
    }

    public function addEvent($data)
    {
        return $this->prepare(self::TABLE_CALENDAR, $data)->insert();
    }

    public function modifyEvent($data, $id)
    {
        if (!$id || empty($id)) throw new Exception("id is required");

        return $this->prepare(self::TABLE_CALENDAR, $data)->update("id = {$id}");
    }

    /**
     * remove empty values
     * 
     * @param array $data
     * 
     * @return array
     */
    private function renderEvents(array $data): array
    {
        $newData = [];

        foreach ($data as $i => $columns) foreach ($columns as $key => $value)
            if ($value && !empty($value)) $newData[$i][$key] = $value;

        return $newData;
    }

    private function getEvent($id): array
    {
        return $this->conn->executeQuery("SELECT * FROM " . self::TABLE_CALENDAR . " where id = :ID", [":ID" => $id]);
    }

    public function showEvent($id): String
    {
        $result = self::getEvent($id);
        $response = [];

        $newFormatDate = function ($date, $format) {
            return date($format, strtotime($date));
        };

        foreach ($result as $data) {
            $response[] = <<<HTML
            <dl class="row" data-title="{$data['title']}">
                <dt class="col-sm-4">Descripción</dt>
                <dd class="col-sm-8">{$data["description"]}</dd>
                <hr class="col-sm-12 my-1">
                <dt class="col-sm-4">Fecha de inicio</dt>
                <dd class="col-sm-8">{$newFormatDate($data["start"], "l, F d, Y, h:i A")}</dd>
                <hr class="col-sm-12 my-1">
                <dt class="col-sm-4">Fecha de fin</dt>
                <dd class="col-sm-8">{$newFormatDate($data["end"], "l, F d, Y, h:i A")}</dd>
            </dl>
            <button type="button" data-event-update="{$data['id']}" data-dismiss="modal" class="btn btn-sm btn-default float-right">
                <b><i class="fas fa-pencil-alt fs--2 mr-2"></i> Editar</b>
            </button>
            HTML;
        }

        return implode("\n", $response);
    }

    public function formEvent(?int $id = null): String
    {
        $mode = !is_null($id) ? "modifyEvent" : "addEvent";
        $result = self::getEvent($id);

        $valTitle          = ($mode == "modifyEvent" ? $result[0]["title"] : "");
        $valDescription    = ($mode == "modifyEvent" ? $result[0]["description"] : "");
        $valStart          = ($mode == "modifyEvent" ? $result[0]["start"] : "");
        $valEnd            = ($mode == "modifyEvent" ? $result[0]["end"] : "");
        $valAllDay         = ($mode == "modifyEvent" && !empty($result[0]["allDay"]) ? "checked" : "");

        return <<<HTML
        <form data-mode="{$mode}" data-id="{$id}">
            <div class="form-group">
                <label>Titulo</label>
                <input value="{$valTitle}" name="data[title]" type="text" placeholder="Titulo" class="form-control">
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <textarea name="data[description]" placeholder="Descripción" class="form-control">{$valDescription}</textarea>
            </div>
            <div class="form-group">
                <label>Fecha de inicio</label>
                <input value="{$valStart}" name="data[start]" type="datetime-local" class="form-control">
            </div>
            <div class="form-group">
                <label>Fecha de fin</label>
                <input value="{$valEnd}" name="data[end]" type="datetime-local" class="form-control">
            </div>
            <div class="form-check">
                <input value="1" {$valAllDay} name="data[allDay]" type="checkbox" class="form-check-input">
                <label class="form-check-label">¿El evento durara todo el día?</label>
            </div>
            <button class="btn btn-success float-right">Guardar</button>
        </form>
        HTML;
    }

    /**
     * https://fullcalendar.io/docs/v5/event-object
     */
    private function createTableCalendar(): void
    {
        # Create table with default columns (id, fechaRegistro).
        $this->tableManager->createTable(self::TABLE_CALENDAR);

        # String. Events that share a groupId will be dragged and resized together automatically.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "groupId");

        # Boolean (true or false). Determines if the event is shown in the “all-day” section of relevant views. In addition, if true the time text is not displayed with the event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "allDay", "BOOLEAN DEFAULT FALSE");

        # Date object that obeys the current timeZone. When an event begins.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "start", "DATETIME DEFAULT NULL");

        # Date object that obeys the current timeZone. When an event ends. It could be null if an end wasn’t specified.
        # Note: This value is exclusive. For example, an event with the end of 2018-09-03 will appear to span through 2018-09-02 but end before the start of 2018-09-03. See how events are are parsed from a plain object for further details.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "end", "DATETIME DEFAULT NULL");

        # An ISO8601 string representation of the start date. If the event is all-day, there will not be a time part.
        $this->tableManager->createColumn(
            self::TABLE_CALENDAR,
            "startStr",
            "DATE DEFAULT NULL"
        );

        # An ISO8601 string representation of the end date. If the event is all-day, there will not be a time part.
        $this->tableManager->createColumn(
            self::TABLE_CALENDAR,
            "endStr",
            "DATE DEFAULT NULL"
        );

        # String. The text that will appear on an event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "title");
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "description");

        # String. A URL that will be visited when this event is clicked by the user. For more information on controlling this behavior, see the eventClick callback.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "url");

        # An array of strings like [ 'myclass1', myclass2' ]. Determines which HTML classNames will be attached to the rendered event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "classNames");

        # Boolean (true or false) or null. The value overriding the editable setting for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "editable", "BOOLEAN DEFAULT FALSE");

        # Boolean (true or false) or null. The value overriding the eventStartEditable setting for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "startEditable", "BOOLEAN DEFAULT FALSE");

        # Boolean (true or false) or null. The value overriding the eventDurationEditable setting for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "durationEditable", "BOOLEAN DEFAULT FALSE");

        # Boolean (true or false) or null. The value overriding the eventResourceEditable setting for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "resourceEditable", "BOOLEAN DEFAULT FALSE");

        # No sé cómo funciona, me perdí en la documentación, así que lo omitiré por ahora.

        # The rendering type of this event. Can be 'auto', 'block', 'list-item', 'background', 'inverse-background', or 'none'. See eventDisplay.
        // $this->tableManager->createColumn( self::TABLE_CALENDAR, "id_display","int default 1");

        // if ($this->tableManager->checkTableExists( self::TABLE_CALENDAR_DISPLAY)) {
        //     $this->tableManager->createTable( self::TABLE_CALENDAR_DISPLAY);

        //     $displayEvents = [
        //         ["display" => "auto"],
        //         ["display" => "block"],
        //         ["display" => "list-item"],
        //         ["display" => "background"],
        //         ["display" => "inverse-background"],
        //         ["display" => "none"]
        //     ];

        //     foreach ($displayEvents as $event) self::prepare(self::TABLE_CALENDAR_DISPLAY, ["data" => $event])->insert();
        //     $this->tableManager->addForeignKey( [self::TABLE_CALENDAR => self::TABLE_CALENDAR_DISPLAY], ["id_display" => "id"]);
        // }

        # The value overriding the eventOverlap setting for this specific event. If false, prevents this event from being dragged/resized over other events. Also prevents other events from being dragged/resized over this event. Does not accept a function.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "overlap");

        # The eventConstraint override for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "constraint");

        # The eventBackgroundColor override for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "backgroundColor");

        # The eventBorderColor override for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "borderColor");

        # The eventTextColor override for this specific event.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "textColor");

        # A plain object holding miscellaneous other properties specified during parsing. Receives properties in the explicitly given extendedProps hash as well as other non-standard properties.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "extendedProps", "JSON DEFAULT NULL");

        # A reference to the Event Source this event came from. If the event was added dynamically via addEvent, and the source parameter was not specified, this value will be null.
        $this->tableManager->createColumn(self::TABLE_CALENDAR, "source");
    }
}
