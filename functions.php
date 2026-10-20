<?php
function formatSolveTime($solve) {
    if (!$solve) return "-";
    if ($solve['penalty'] === 'DNF') return "DNF";
    $val = number_format((float)$solve['final_time'], 2, '.', '');
    return $solve['penalty'] === '+2' ? $val . "+" : $val;
}

function formatStat($val) {
    if ($val === null) return "-";
    if ($val === 'DNF') return "DNF";
    return number_format((float)$val, 2, '.', '');
}

function hitungRata($solves, $endIndex, $n) {
    if ($endIndex + 1 < $n) return null;

    $slice = array_slice($solves, $endIndex - $n + 1, $n);
    $times = [];
    $dnf = 0;

    foreach ($slice as $s) {
        if ($s['penalty'] === 'DNF' || $s['final_time'] === null) {
            $dnf++;
        } else {
            $times[] = (float)$s['final_time'];
        }
    }

    if ($dnf >= 2) return 'DNF';

    sort($times, SORT_NUMERIC);
    array_shift($times);
    if ($dnf === 0) {
        array_pop($times);
    }

    return count($times) > 0 ? round(array_sum($times) / count($times), 2) : null;
}

function hitungStatistik($solves) {
    $total = count($solves);
    $list = [];
    $best = ['time' => null, 'mo3' => null, 'ao5' => null, 'ao12' => null, 'ao100' => null];
    $validCount = 0;
    $validSum = 0;

    for ($i = 0; $i < $total; $i++) {
        $s = $solves[$i];
        $isDnf = ($s['penalty'] === 'DNF' || $s['final_time'] === null);
        $time = $isDnf ? 'DNF' : (float)$s['final_time'];

        if (!$isDnf) {
            $validCount++;
            $validSum += $time;
            if ($best['time'] === null || $time < $best['time']) $best['time'] = $time;
        }

        $mo3   = hitungRata($solves, $i, 3);
        $ao5   = hitungRata($solves, $i, 5);
        $ao12  = hitungRata($solves, $i, 12);
        $ao100 = hitungRata($solves, $i, 100);

        foreach (['mo3' => $mo3, 'ao5' => $ao5, 'ao12' => $ao12, 'ao100' => $ao100] as $k => $v) {
            if (is_numeric($v) && ($best[$k] === null || $v < $best[$k])) {
                $best[$k] = $v;
            }
        }

        $diff = null;
        if ($i > 0 && !$isDnf && $solves[$i - 1]['penalty'] !== 'DNF') {
            $diff = round($time - (float)$solves[$i - 1]['final_time'], 2);
        }

        $list[$i] = [
            'no'           => $i + 1,
            'id'           => $s['id'],
            'raw_time'     => (float)$s['raw_time'],
            'penalty'      => $s['penalty'],
            'final_time'   => $isDnf ? null : $time,
            'display_time' => formatSolveTime($s),
            'scramble'     => $s['scramble'],
            'created_at'   => $s['created_at'],
            'mo3'          => $mo3,
            'ao5'          => $ao5,
            'ao12'         => $ao12,
            'ao100'        => $ao100,
            'diff'         => $diff
        ];
    }

    $last = $total > 0 ? $list[$total - 1] : null;

    return [
        'list' => $list,
        'summary' => [
            'total'   => $total,
            'valid'   => $validCount,
            'mean'    => $validCount > 0 ? round($validSum / $validCount, 2) : null,
            'current' => [
                'time'  => $last ? $last['display_time'] : '-',
                'mo3'   => $last ? $last['mo3'] : null,
                'ao5'   => $last ? $last['ao5'] : null,
                'ao12'  => $last ? $last['ao12'] : null,
                'ao100' => $last ? $last['ao100'] : null,
            ],
            'best' => $best
        ]
    ];
}
?>