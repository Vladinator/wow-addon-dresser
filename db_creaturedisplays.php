<?php

$csv_creature = WoWTools::GetLatestFile("creature");
$csv_creaturedisplayinfo = WoWTools::GetLatestFile("creaturedisplayinfo");

if ($csv_creature) {
    WoWTools::WriteLua(
        $csv_creature,
        "CreatureToDisplayID",
        array(
            "ID" => array(
                "DisplayID[0]",
                "DisplayID[1]",
                "DisplayID[2]",
                "DisplayID[3]",
            ),
        ),
        __DIR__ . "/db_creaturedisplays.lua"
    );
}

if ($csv_creaturedisplayinfo) {
    WoWTools::WriteLua(
        $csv_creaturedisplayinfo,
        "CreatureToModelID",
        array(
            "ID" => "ModelID",
        ),
        __DIR__ . "/db_creaturemodels.lua"
    );
}

class WoWTools {
    private static $WOW_TOOLS_DOC_URL = "https://wow.tools/dbc/?dbc=%s";
    private static $WOW_TOOLS_CSV_URL = "https://wow.tools/dbc/api/export/?name=%s&build=%s";
    private static $options;

    private static function GetOptions() {
        if (self::$options === null)
            self::$options = stream_context_create(array(
                "http" => array(
                    "header" => implode("\r\n", array(
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36",
                    )),
                ),
            ));
        return self::$options;
    }

    public static function GetLatestBuild($file) {
        $url = sprintf(self::$WOW_TOOLS_DOC_URL, $file);
        $raw = file_get_contents($url, false, self::GetOptions());
        if (preg_match("/\<\s*option\s+value\s*=\s*[\"'](\d+\.\d+\.\d+\.\d+)[\"']\s*\>/i", $raw, $match))
            return $match[1];
    }

    public static function GetBuildFile($file, $build) {
        $url = sprintf(self::$WOW_TOOLS_CSV_URL, $file, $build);
        $raw = file_get_contents($url, false, self::GetOptions());
        $lines = preg_split("/[\r\n|\r|\n]/", $raw);
        $csv = array_map("str_getcsv", $lines);
        $headers = $csv[0];
        foreach ($csv as $i => &$entry) {
            if ($i < 1)
                continue;
            foreach ($headers as $j => $h)
                $entry[$h] = $entry[$j];
        }
        return array_splice($csv, 1, -1);
    }

    public static function GetLatestFile($file) {
        $build = self::GetLatestBuild($file);
        if (!$build) return;
        return self::GetBuildFile($file, $build);
    }

    public static function WriteLua($csv, $var, $map, $file) {
        $lua = "local _, ns = ...\r\n\r\nns." . $var . " = {\r\n";
        foreach ($csv as $i => &$entry) {
            $elua = "";
            foreach ($map as $k => $v) {
                if (is_array($v)) {
                    $emlua = array();
                    foreach ($v as $x => $y)
                        if ($entry[$y])
                            $emlua[] = $entry[$y];
                    if (is_numeric($entry[$k]))
                        $elua .= "[" . $entry[$k] . "]=" . (count($emlua) === 1 ? $emlua[0] : ("{" . implode(",", $emlua) . "}")) . ",";
                } else if (is_numeric($entry[$k])) {
                    $elua .= "[" . $entry[$k] . "]=" . $entry[$v] . ",";
                }
            }
            $lua .= "\t" . $elua . "\r\n";
        }
        $lua .= "}\r\n";
        file_put_contents($file, $lua);
    }
}
