<?php
/**
 * Sort multidimensional arrays
 *
 * http://php.net/manual/en/function.usort.php#89977
 *
 * @package EDTB\Backend
 * @author nmcquay
 */

class utility
{
    /**
     * @param array $ary the array we want to sort
     * @param string $clause a string specifying how to sort the array similar to SQL ORDER BY clause
     * @param bool $ascending that default sorts fall back to when no direction is specified
     * @return null
     */
    public static function orderBy(&$ary, $clause, $ascending = true)
    {
        $clause = str_ireplace('order by', '', $clause);
        $clause = preg_replace('/\s+/', ' ', $clause);
        $keys = explode(',', $clause);
        $dirMap = array('desc' => 1, 'asc' => -1);
        $def = $ascending ? -1 : 1;

        $keyAry = [];
        $dirAry = [];
        foreach ($keys as $key) {
            $key2 = explode(' ', trim($key));
            $keyAry[] = trim($key2[0]);
            if (isset($key2[1])) {
                $dir = strtolower(trim($key2[1]));
                $dirAry[] = $dirMap[$dir] ? $dirMap[$dir] : $def;
            } else {
                $dirAry[] = $def;
            }
        }

        $fnBody = '';
        for ($i = count($keyAry) - 1; $i >= 0; $i--) {
            $k = $keyAry[$i];
            $t = $dirAry[$i];
            $f = -1 * $t;
            $aStr = '$a[\'' . $k . '\']';
            $bStr = '$b[\'' . $k . '\']';
            if (strpos($k, '(') !== false) {
                $aStr = '$a->' . $k;
                $bStr = '$b->' . $k;
            }

            if ($fnBody == '') {
                $fnBody .= "if({$aStr} == {$bStr}) { return 0; }\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            } else {
                $fnBody = "if({$aStr} == {$bStr}) {\n" . $fnBody;
                $fnBody .= "}\n";
                $fnBody .= "return ({$aStr} < {$bStr}) ? {$t} : {$f};\n";
            }
        }

        if ($fnBody) {
            $sortFn = create_function('$a,$b', $fnBody);
            usort($ary, $sortFn);
        }
    }
}
