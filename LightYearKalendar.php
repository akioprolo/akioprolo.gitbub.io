<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galaktik 2277 Light Loop</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2c003e;
            color: #fff;
            text-align: center;
        }
        table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #fff;
        }
        th {
            background-color: #000;
        }
        td {
            background-color: #444;
        }
    </style>
</head>
<body>
    <header>
        <h1>Galaktik 2277 Light Loop</h1>
    </header>

    <?php

    function getLightDayName($index) {
        $_map = array(
            'Nebula', 'Quasar', 'Pulsar',
            'Supernova', 'Blackhole', 'Wormhole',
            'Andromeda', 'Orion', 'Cygnus',
            'Phoenix', 'Centauri', 'Aurora',
            'Zenith'
        );

        return $_map[$index - 1];
    }

    function getLightDayIndex($position) {
        return $position;    
    }

    function getLightYear($toneData) {
        $LY = $toneData['wave'];
        if ($LY < 0) $LY = 0;
        return $LY;
    }

    function getLightDayPosition($tone) {
        return $tone;
    }

    function getWhen($lightDate, $currentDate) {

        $is_today = $lightDate->format('m-d-Y') === $currentDate->format('m-d-Y');
        $is_past = !$is_today && $lightDate < $currentDate; 
        $is_future = !$is_past && !$is_today;

        if ($is_past) return '404';
        if ($is_today) return '1212';        
        if ($is_future) return '333';

    }

    function getToneData($gregorianDate) {
        $baseDate = DateTime::createFromFormat('m-d-Y', '12-12-2023'); 
        $inputDate = DateTime::createFromFormat('m-d-Y', $gregorianDate);

        $dayOutOfTime = "02-29-2024";
        if ($inputDate->format('m-d-Y') == $dayOutOfTime) {
            return null;
        }

        $toneNumber = NULL;
        $interval = $inputDate->diff($baseDate);
        $daysDifference = $interval->days;
        if ($inputDate > DateTime::createFromFormat('m-d-Y', $dayOutOfTime)) {
            $daysDifference -= 1;
        }
        $toneNumber = ($daysDifference + 1) % 13; 
        if ($toneNumber == 0) {
            $toneNumber = 13;
        }

        // Calculate the wave based on the first day of each tone cycle
        $firstDayOfTone = $daysDifference % 13 === 0;
        $wave = $firstDayOfTone ? ($daysDifference / 13) + 1 : ceil($daysDifference / 13);

        $wave = $wave - 1;  // start at 0

        return array(
            'tone' => $toneNumber, 
            'wave' => $wave
        ); 
    }

    function generateKalight($startDate, $numDays) {
        $_kalight = array();
        $currentDate = new DateTime();
        $startHere = DateTime::createFromFormat('m-d-Y', $startDate);

        for ($i = 0; $i < $numDays; $i++) {
            $lightDate = clone $startHere;
            $lightDate->modify("+$i day");
            $toneData = getToneData($lightDate->format('m-d-Y'));
        
            if ($toneData === null) {
                $_kalight[] = array(
                    'date' => $lightDate->format('m-d-Y'),
                    'when' => getWhen($lightDate, $currentDate),
                    'tone' => null,                
                    'position' => null,
                    'LY' => null,
                    'dayname' => null,
                );
                continue;
            }

            $tone = $toneData['tone'];
            $position = getLightDayPosition($tone);

            $LY = getLightYear($toneData);
            
            $lightDayIndex = getLightDayIndex($position);
            $lightDayName = getLightDayName($lightDayIndex);

            $_kalight[] = array(
                'date' => $lightDate->format('m-d-Y'),
                'when' => getWhen($lightDate, $currentDate),
                'tone' => $tone,            
                'position' => $position,
                'LY' => $LY,
                'dayname' => $lightDayName,
                'wave' => $toneData['wave'],
            );
        }

        return $_kalight;
    }

    function formatWhenKaji($zsynk) {
        if ($zsynk == '404') return '🕳️';
        if ($zsynk == '1212') return '<a href="1212">🚀</a>';
        if ($zsynk == '333') return '⭐';
    }        

    function formatDate($date) {
        $inputDate = DateTime::createFromFormat('m-d-Y', $date);
        $date = $inputDate->format('m-d-202X');
        return $date;
    }

    function printKalight($_kalight) {

        $DROP = "";
        $DROP .= '<table border="1">';
        $DROP .= '<tr><th>Date</th><th>When</th><th>Light Code</th><th>Position</th></tr>';
        
        $_kalight = array_reverse($_kalight);

        foreach ($_kalight as $_da) {

            if (!isset($_da['tone'])) {
                $lightCode = "";
                $position = "";
            }
            else {
                $year = sprintf('%02d', $_da['LY']);
                $lightCode = "LY" . $year . ':' . $_da['dayname'];
                $position = sprintf('%02d', $_da['position']);
            }

            $date = formatDate($_da['date']);
            $whenKaji = formatWhenKaji($_da['when']);

            $style = "";
            if ($_da['position'] == 13) {            
                // $style = 'background-color: #ff1987; color: black';
            }         
            if ($_da['position'] == 1) {            
                $style = 'background-color: #ff1987; color: white';
            } 
            if ($_da['position'] == null) {
                $style = 'background-color: black; color: white';
            }
            if ($_da['when'] == 1212) {
                $style = 'background-color: yellow';
            }

            $DROP .= '<tr style="' . $style . '">';
            $DROP .= '<td style="font-size: 0.9em">' . $date . '</td>';
            $DROP .= '<td>' . $whenKaji. '</td>';        
            $DROP .= '<td>' . $lightCode . '</td>';
            $DROP .= '<td>' . $position. '</td>';
            $DROP .= '</tr>';
        }

        $DROP .= '</table>';
        return $DROP;
    }


    $startDate = "12-12-2023";
    $TEXAS_CENTRAL = 'America/Chicago';
    $DAYS_OUTTA_TIME = 1;
    $DA_KOUNT = 15 * 12 + $DAYS_OUTTA_TIME + 1;

    date_default_timezone_set($TEXAS_CENTRAL);
    $_kalight = generateKalight($startDate, $DA_KOUNT);
    $DROP = printKalight($_kalight);
    echo '<p>202X Gregorian Light Loop</p>';
    echo $DROP;

    ?>
</body>
</html>
