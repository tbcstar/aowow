<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class WorldEventList extends BaseType
{
    private static $holidayIcons = [
        141 => 'calendar_winterveilstart',
        327 => 'calendar_lunarfestivalstart',
        335 => 'calendar_loveintheairstart',
        423 => 'calendar_loveintheairstart',
        181 => 'calendar_noblegardenstart',
        201 => 'calendar_childrensweekstart',
        341 => 'calendar_midsummerstart',
        62  => 'inv_misc_missilelarge_red',
        321 => 'calendar_harvestfestivalstart',
        398 => 'calendar_piratesdaystart',
        372 => 'calendar_brewfeststart',
        324 => 'calendar_hallowsendstart',
        409 => 'calendar_dayofthedeadstart',
        404 => 'calendar_harvestfestivalstart',
        283 => 'inv_jewelry_necklace_21',
        284 => 'inv_misc_rune_07',
        400 => 'achievement_bg_winsoa',
        420 => 'achievement_bg_winwsg',
        285 => 'inv_jewelry_amulet_07',
        353 => 'spell_nature_eyeofthestorm',
        301 => 'calendar_fishingextravaganzastart',
        424 => 'calendar_fishingextravaganzastart',
        374 => 'calendar_darkmoonfaireelwynnstart',
        375 => 'calendar_darkmoonfairemulgorestart',
        376 => 'calendar_darkmoonfaireterokkarstart'
    ];

    protected $setupQuery = 'SELECT *, -e.id AS ARRAY_KEY, -e.id as id FROM ?_events e LEFT JOIN ?_holidays h ON e.holidayId = h.id WHERE [cond] ORDER BY -e.id ASC';
    protected $matchQuery = 'SELECT COUNT(1) FROM ?_events e LEFT JOIN ?_holidays h ON e.holidayId = h.id WHERE [cond]';

    public function __construct($data)
    {
        parent::__construct($data);

        // unseting elements while we iterate over the array will cause the pointer to reset
        $replace = [];

        // post processing
        while ($this->iterate())
        {
            // emulate category
            $sT = $this->curTpl['scheduleType'];
            if (!$this->curTpl['holidayId'])
                $this->curTpl['category'] = 0;
            else if ($sT == 2)
                $this->curTpl['category'] = 3;
            else if (in_array($sT, [0, 1]))
                $this->curTpl['category'] = 2;
            else if ($sT == -1)
                $this->curTpl['category'] = 1;

            // change Ids if holiday is set
            if ($this->curTpl['holidayId'] > 0)
            {
                // template
                $this->curTpl['id'] = $this->curTpl['holidayId'];
                unset($this->curTpl['description']);
                $replace[$this->id] = $this->curTpl;

                // names
                unset($this->names[$this->id]);
                $this->names[$this->curTpl['id']] = Util::localizedString($this->curTpl, 'name');
            }
            else                                            // set a name if holiday is missing
            {
                // template
                $this->curTpl['name_loc0']  = $this->curTpl['description'];
                $this->curTpl['iconString'] = 'INV_Misc_QuestionMark';
                $replace[$this->id] = $this->curTpl;

                // names
                $this->names[$this->id]     = '(SERVERSIDE) '.$this->curTpl['description'];
            }
        }

        foreach ($replace as $old => $data)
        {
            unset($this->templates[$old]);
            $this->templates[$data['id']] = $data;
        }
        $this->reset();
    }

    public static function getName($id)
    {
        if ($id > 0)
            $row = DB::Aowow()->SelectRow('SELECT * FROM ?_holidays WHERE Id = ?d', intVal($id));
        else
            $row = DB::Aowow()->SelectRow('SELECT description as name FROM ?_events WHERE Id = ?d', intVal(-$id));

        return Util::localizedString($row, 'name');
    }

    public static function updateDates($start, $end, $occurence, $final = 5000000000)    // in the far far FAR future..
    {
        // Convert everything to seconds
        $start     = intVal($start);
        $end       = intVal($end);
        $occurence = intVal($occurence);
        $final     = intVal($final);

        $now       = time();
        $year      = date("Y", $now);

        $curStart  = $start;
        $curEnd    = $end;
        $nextStart = $curStart + $occurence;
        $nextEnd   = $curEnd + $occurence;

        while ($nextEnd <= $final && date("Y", $nextEnd) <= $year && $curEnd <= $now)
        {
            $curStart  = $nextStart;
            $curEnd    = $nextEnd;
            $nextStart = $curStart + $occurence;
            $nextEnd   = $curEnd + $occurence;
        }

        return array(
            'start'     => $curStart,
            'end'       => $curEnd,
            'nextStart' => $nextStart,
            'nextEnd'   => $nextEnd
        );
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'category'  => $this->curTpl['category'],
                'id'        => $this->id,
                'name'      => $this->names[$this->id],
                'rec'       => $this->curTpl['occurence'],
                'startDate' => $this->curTpl['startTime'],
                'endDate'   => $this->curTpl['startTime'] + $this->curTpl['length']
            );
        }

        return $data;
    }

    public function setup()
    {
        foreach (self::$holidayIcons as $id => $s)
            DB::Aowow()->Query('UPDATE ?_holidays SET iconString = ?s WHERE id = ?', $s, $id);
    }

    public function addGlobalsToJScript(&$refs)
    {
        if (!isset($refs['gHolidays']))
            $refs['gHolidays'] = [];

        while ($this->iterate())
        {
            $refs['gHolidays'][$this->id] = array(
                'name' => $this->names[$this->id],
                'icon' => $this->curTpl['iconString']
            );
        }
    }

    public function addRewardsToJScript(&$ref) { }
    public function renderTooltip() { }
}

/*

function event_name($events)
{
    global $DB;
    if (!$events || !is_array($events) || count($events) == 0)
        return array();

    $entries = arraySelectKey($events, 'entry');

    $rows = $DB->select('
        SELECT eventEntry AS entry, description AS name
        FROM game_event
        WHERE eventEntry IN (?a)',
        $entries
    );

    // Merge original array with new information
    $result = array();
    foreach ($events as $event)
        if (isset($event['entry']))
            $result[$event['entry']] = $event;

    if ($rows)
    {
        foreach ($rows as $event)
            $result[$event['entry']] = array_merge($result[$event['entry']], $event);
    }

    return $result;
}

function event_description($entry)
{
    global $DB;

    $result = event_infoline(array(array('entry' => $entry)));
    if (is_array($result) && count($result) > 0)
        $result = reset($result);
    else
        return NULL;

    $result['period'] = sec_to_time(intval($result['occurence'])*60);

    $result['npcs_guid'] = $DB->selectCol('SELECT guid FROM game_event_creature WHERE eventEntry=?d OR eventEntry=?d', $entry, -$entry);
    $result['objects_guid'] = $DB->selectCol('SELECT guid FROM game_event_gameobject WHERE eventEntry=?d OR eventEntry=?d', $entry, -$entry);
    $result['creatures_quests_id'] = $DB->select('SELECT id AS creature, quest FROM game_event_creature_quest WHERE eventEntry=?d OR eventEntry=?d GROUP BY quest', $entry, -$entry);

    return $result;
}

*/

?>